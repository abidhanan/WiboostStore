<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingForm;
use App\Models\MarketingFormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{
    protected const FIELD_TYPES = [
        'text',
        'textarea',
        'email',
        'number',
        'date',
        'select',
        'checkbox',
        'file',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $formFilter = (string) $request->query('form_filter', 'all');
        $allowedFormFilters = ['all', 'active', 'inactive'];

        if (! in_array($formFilter, $allowedFormFilters, true)) {
            $formFilter = 'all';
        }

        $forms = MarketingForm::query()
            ->with('creator:id,name')
            ->withCount('submissions')
            ->when($formFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($formFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('benefit_title', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.forms.index', compact('forms', 'search', 'formFilter'));
    }

    public function create()
    {
        return view('admin.forms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $fields = $this->normalizeFields($request);

        $form = MarketingForm::create([
            ...$this->normalizePayload($validated, $request),
            'slug' => $this->uniqueSlug($validated['slug'] ?? $validated['title']),
            'fields' => $fields,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.forms.show', $form)
            ->with('success', 'Formulir berhasil dibuat dan siap dibagikan.');
    }

    public function show(Request $request, MarketingForm $form)
    {
        $search = trim((string) $request->query('search', ''));

        $submissionQuery = MarketingFormSubmission::query()
            ->where('marketing_form_id', $form->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('answers', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            });

        $answerCharts = $this->buildAnswerCharts(
            $form,
            (clone $submissionQuery)->latest('created_at')->get(['id', 'answers', 'created_at'])
        );

        $submissions = $submissionQuery
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.forms.show', compact('form', 'submissions', 'search', 'answerCharts'));
    }

    public function edit(MarketingForm $form)
    {
        return view('admin.forms.edit', compact('form'));
    }

    public function update(Request $request, MarketingForm $form)
    {
        $validated = $request->validate($this->rules($form));
        $oldOptionImages = $this->optionImagePaths($form->fields ?? []);
        $fields = $this->normalizeFields($request, $form);
        $slugSource = trim((string) ($validated['slug'] ?? '')) !== '' ? $validated['slug'] : $validated['title'];

        $form->update([
            ...$this->normalizePayload($validated, $request),
            'slug' => $this->uniqueSlug($slugSource, $form),
            'fields' => $fields,
        ]);

        $newOptionImages = $this->optionImagePaths($fields);
        foreach (array_diff($oldOptionImages, $newOptionImages) as $obsoleteImage) {
            Storage::disk('public')->delete($obsoleteImage);
        }

        return $this->redirectToReturnTo($request, 'admin.forms.show', [$form])
            ->with('success', 'Formulir berhasil diperbarui.');
    }

    public function destroy(MarketingForm $form)
    {
        foreach ($this->optionImagePaths($form->fields ?? []) as $optionImage) {
            Storage::disk('public')->delete($optionImage);
        }

        $form->submissions()
            ->select(['id', 'answers'])
            ->chunkById(100, function ($submissions) {
                $submissions->each(fn (MarketingFormSubmission $submission) => $this->deleteSubmissionFiles($submission));
            });

        $form->delete();

        return redirect()->route('admin.forms.index')->with('success', 'Formulir berhasil dihapus.');
    }

    protected function rules(?MarketingForm $form = null): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:220',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('marketing_forms', 'slug')->ignore($form?->id),
            ],
            'description' => ['nullable', 'string', 'max:3000'],
            'fields' => ['required', 'array', 'min:1', 'max:30'],
            'fields.*.key' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_]+$/'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.type' => ['required', Rule::in(self::FIELD_TYPES)],
            'fields.*.help' => ['nullable', 'string', 'max:300'],
            'fields.*.option_items' => ['nullable', 'array', 'max:50'],
            'fields.*.option_items.*.label' => ['nullable', 'string', 'max:180'],
            'fields.*.option_items.*.image' => ['nullable', 'image', 'max:5120'],
            'fields.*.option_items.*.existing_image' => ['nullable', 'string', 'max:500'],
            'fields.*.option_items.*.remove_image' => ['nullable', 'boolean'],
            'fields.*.required' => ['nullable', 'boolean'],
            'benefit_enabled' => ['nullable', 'boolean'],
            'benefit_title' => ['nullable', 'string', 'max:180'],
            'benefit_description' => ['nullable', 'string', 'max:2000'],
            'benefit_credentials' => ['nullable', 'array'],
            'benefit_credentials.email' => ['nullable', 'string', 'max:500'],
            'benefit_credentials.password' => ['nullable', 'string', 'max:500'],
            'benefit_credentials.profile' => ['nullable', 'string', 'max:120'],
            'benefit_credentials.pin' => ['nullable', 'string', 'max:80'],
            'benefit_credentials.link' => ['nullable', 'url', 'max:500'],
            'benefit_credentials.tutorial_link' => ['nullable', 'url', 'max:500'],
            'benefit_credentials.notes' => ['nullable', 'string', 'max:2000'],
            'benefit_url' => ['nullable', 'url', 'max:500'],
            'benefit_button_label' => ['nullable', 'string', 'max:80'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function normalizePayload(array $data, Request $request): array
    {
        $benefitEnabled = $request->boolean('benefit_enabled');

        return [
            'title' => trim((string) $data['title']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'benefit_enabled' => $benefitEnabled,
            'benefit_title' => $benefitEnabled ? (trim((string) ($data['benefit_title'] ?? '')) ?: 'Benefit Aplikasi Premium') : null,
            'benefit_description' => $benefitEnabled ? (trim((string) ($data['benefit_description'] ?? '')) ?: null) : null,
            'benefit_credentials' => $benefitEnabled ? $this->normalizeBenefitCredentials((array) $request->input('benefit_credentials', [])) : null,
            'benefit_url' => null,
            'benefit_button_label' => null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    protected function normalizeBenefitCredentials(array $rawCredentials): ?array
    {
        $credentials = [];

        foreach ([
            'email',
            'password',
            'profile',
            'pin',
            'link',
            'tutorial_link',
            'notes',
        ] as $key) {
            $value = trim((string) ($rawCredentials[$key] ?? ''));

            if ($value !== '') {
                $credentials[$key] = $value;
            }
        }

        return $credentials === [] ? null : $credentials;
    }

    protected function buildAnswerCharts(MarketingForm $form, $submissions): array
    {
        $charts = [];

        foreach (($form->fields ?? []) as $field) {
            $type = (string) ($field['type'] ?? 'text');
            $key = (string) ($field['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $definedOptions = collect($field['options'] ?? [])
                ->map(fn ($option) => trim((string) (is_array($option) ? ($option['label'] ?? '') : $option)))
                ->filter()
                ->values()
                ->all();

            $filledAnswers = [];
            $emptyCount = 0;

            foreach ($submissions as $submission) {
                $answers = is_array($submission->answers ?? null) ? $submission->answers : [];
                $answer = $answers[$key] ?? collect($answers)->firstWhere('key', $key);

                if (! is_array($answer)) {
                    $emptyCount++;
                    continue;
                }

                $value = $answer['value'] ?? null;
                $values = $this->answerValuesForChart($value, $type);

                if ($values === []) {
                    $emptyCount++;
                    continue;
                }

                foreach ($values as $selected) {
                    $filledAnswers[] = [
                        'value' => $selected,
                        'title' => $selected,
                        'url' => $type === 'file' && is_array($value) ? ($value['url'] ?? null) : null,
                        'submitted_at' => optional($submission->created_at)
                            ->timezone(config('app.timezone'))
                            ->format('d/m/Y H:i'),
                    ];
                }
            }

            $charts[] = match ($type) {
                'select', 'checkbox' => $this->buildDistributionChart($field, $type, $definedOptions, $filledAnswers, $emptyCount),
                'number' => $this->buildNumberChart($field, $filledAnswers, $emptyCount),
                'date' => $this->buildDateChart($field, $filledAnswers, $emptyCount),
                default => $this->buildListChart($field, $type, $filledAnswers, $emptyCount),
            };
        }

        return $charts;
    }

    protected function buildDistributionChart(array $field, string $type, array $definedOptions, array $filledAnswers, int $emptyCount): array
    {
        $counts = array_fill_keys($definedOptions, 0);

        foreach ($filledAnswers as $answer) {
            $value = (string) ($answer['value'] ?? '');
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        if ($emptyCount > 0) {
            $emptyLabel = $this->emptyAnswerLabelForChart($type);
            $counts[$emptyLabel] = ($counts[$emptyLabel] ?? 0) + $emptyCount;
        }

        if ($counts === []) {
            $counts[$this->emptyAnswerLabelForChart($type)] = 0;
        }

        $total = array_sum($counts);

        return [
            'label' => (string) ($field['label'] ?? 'Pertanyaan'),
            'type' => $type,
            'visual' => 'distribution',
            'total' => $total,
            'total_label' => $type === 'checkbox' ? 'pilihan' : 'jawaban',
            'options' => collect($counts)->map(fn ($count, $label) => [
                'label' => Str::limit((string) $label, 90, '...'),
                'title' => (string) $label,
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ])->values()->all(),
        ];
    }

    protected function buildNumberChart(array $field, array $filledAnswers, int $emptyCount): array
    {
        $numbers = collect($filledAnswers)
            ->map(fn ($answer) => $this->parseChartNumber((string) ($answer['value'] ?? '')))
            ->filter(fn ($number) => $number !== null)
            ->values();

        $count = $numbers->count();
        $sum = $numbers->sum();
        $average = $count > 0 ? $sum / $count : 0;

        return [
            'label' => (string) ($field['label'] ?? 'Pertanyaan'),
            'type' => 'number',
            'visual' => 'number',
            'total' => $count + $emptyCount,
            'total_label' => 'jawaban',
            'empty_count' => $emptyCount,
            'stats' => [
                ['label' => 'Rata-rata', 'value' => $this->formatChartNumber($average)],
                ['label' => 'Terkecil', 'value' => $count > 0 ? $this->formatChartNumber($numbers->min()) : '-'],
                ['label' => 'Terbesar', 'value' => $count > 0 ? $this->formatChartNumber($numbers->max()) : '-'],
                ['label' => 'Total', 'value' => $count > 0 ? $this->formatChartNumber($sum) : '-'],
            ],
            'entries' => collect($filledAnswers)
                ->take(8)
                ->map(fn ($answer) => [
                    'value' => Str::limit((string) ($answer['value'] ?? '-'), 90, '...'),
                    'title' => (string) ($answer['title'] ?? $answer['value'] ?? '-'),
                    'url' => $answer['url'] ?? null,
                    'submitted_at' => $answer['submitted_at'] ?? null,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function buildDateChart(array $field, array $filledAnswers, int $emptyCount): array
    {
        $counts = [];

        foreach ($filledAnswers as $answer) {
            $value = (string) ($answer['value'] ?? '');
            $timestamp = strtotime($value);
            $label = $timestamp ? date('d/m/Y', $timestamp) : $value;

            if ($label === '') {
                continue;
            }

            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        if ($emptyCount > 0) {
            $counts[$this->emptyAnswerLabelForChart('date')] = ($counts[$this->emptyAnswerLabelForChart('date')] ?? 0) + $emptyCount;
        }

        if ($counts === []) {
            $counts[$this->emptyAnswerLabelForChart('date')] = 0;
        }

        return [
            'label' => (string) ($field['label'] ?? 'Pertanyaan'),
            'type' => 'date',
            'visual' => 'date',
            'total' => array_sum($counts),
            'total_label' => 'jawaban',
            'dates' => collect($counts)->map(fn ($count, $label) => [
                'label' => (string) $label,
                'count' => $count,
            ])->values()->all(),
        ];
    }

    protected function buildListChart(array $field, string $type, array $filledAnswers, int $emptyCount): array
    {
        return [
            'label' => (string) ($field['label'] ?? 'Pertanyaan'),
            'type' => $type,
            'visual' => $type === 'file' ? 'file' : 'list',
            'total' => count($filledAnswers) + $emptyCount,
            'total_label' => 'jawaban',
            'empty_count' => $emptyCount,
            'entries' => collect($filledAnswers)
                ->take(8)
                ->map(fn ($answer) => [
                    'value' => Str::limit((string) ($answer['value'] ?? '-'), 110, '...'),
                    'title' => (string) ($answer['title'] ?? $answer['value'] ?? '-'),
                    'url' => $answer['url'] ?? null,
                    'submitted_at' => $answer['submitted_at'] ?? null,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function answerValuesForChart($value, string $type): array
    {
        if ($type === 'file') {
            if (! is_array($value)) {
                return [];
            }

            $fileLabel = trim((string) ($value['name'] ?? $value['url'] ?? $value['path'] ?? ''));

            return $fileLabel === '' ? [] : [$fileLabel];
        }

        $values = is_array($value) ? $value : [$value];

        return collect($values)
            ->map(function ($item) {
                if (is_array($item)) {
                    $item = $item['value'] ?? $item['label'] ?? $item['name'] ?? '';
                }

                return trim((string) $item);
            })
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();
    }

    protected function emptyAnswerLabelForChart(string $type): string
    {
        return $type === 'file' ? 'Tidak mengunggah file' : 'Tidak diisi';
    }

    protected function parseChartNumber(string $value): ?float
    {
        $value = trim(preg_replace('/[^0-9,\.\-]/', '', $value) ?? '');

        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',') && ! str_contains($value, '.')) {
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1 || (str_contains($value, '.') && str_contains($value, ','))) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    protected function formatChartNumber(float|int $number): string
    {
        $precision = abs($number - round($number)) < 0.00001 ? 0 : 2;

        return number_format($number, $precision, ',', '.');
    }

    protected function normalizeFields(Request $request, ?MarketingForm $form = null): array
    {
        $rawFields = (array) $request->input('fields', []);
        $allowedExistingImages = $this->optionImagePaths($form?->fields ?? []);
        $fields = [];
        $usedKeys = [];
        $errors = [];
        $uploadedOptionImages = [];

        foreach (array_values($rawFields) as $index => $rawField) {
            $label = trim((string) ($rawField['label'] ?? ''));
            $type = in_array(($rawField['type'] ?? 'text'), self::FIELD_TYPES, true) ? $rawField['type'] : 'text';

            if ($label === '') {
                $errors["fields.{$index}.label"] = 'Label pertanyaan wajib diisi.';
                continue;
            }

            $key = trim((string) ($rawField['key'] ?? ''));
            $key = preg_match('/^[A-Za-z0-9_]+$/', $key) ? Str::lower($key) : '';
            $key = $key ?: (Str::slug(Str::ascii($label), '_') ?: 'field');
            $key = $this->uniqueFieldKey($key, $usedKeys);
            $usedKeys[] = $key;

            $options = [];
            $optionImages = [];
            if (in_array($type, ['select', 'checkbox'], true)) {
                $seenOptions = [];

                foreach ((array) ($rawField['option_items'] ?? []) as $optionIndex => $rawOption) {
                    $optionLabel = trim((string) ($rawOption['label'] ?? ''));

                    if ($optionLabel === '' || in_array(Str::lower($optionLabel), $seenOptions, true)) {
                        continue;
                    }

                    $seenOptions[] = Str::lower($optionLabel);
                    $options[] = $optionLabel;
                    $existingImage = trim((string) ($rawOption['existing_image'] ?? ''));
                    if (! in_array($existingImage, $allowedExistingImages, true)) {
                        $existingImage = '';
                    }
                    $image = $request->file("fields.{$index}.option_items.{$optionIndex}.image");
                    $imagePath = ! empty($rawOption['remove_image']) ? null : ($existingImage ?: null);

                    if ($image) {
                        $imagePath = $image->store('form-options', 'public');
                        $uploadedOptionImages[] = $imagePath;
                    }

                    if ($imagePath) {
                        $optionImages[$optionLabel] = $imagePath;
                    }
                }

                if ($options === []) {
                    $errors["fields.{$index}.options"] = 'Opsi wajib diisi untuk pertanyaan pilihan.';
                }
            }

            $fields[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'help' => trim((string) ($rawField['help'] ?? '')) ?: null,
                'options' => $options,
                'option_images' => $optionImages,
                'required' => ! empty($rawField['required']),
            ];
        }

        if ($fields === []) {
            $errors['fields'] = 'Minimal harus ada satu pertanyaan formulir.';
        }

        if ($errors !== []) {
            foreach ($uploadedOptionImages as $uploadedOptionImage) {
                Storage::disk('public')->delete($uploadedOptionImage);
            }

            throw ValidationException::withMessages($errors);
        }

        return $fields;
    }

    protected function optionImagePaths(array $fields): array
    {
        return collect($fields)
            ->flatMap(function ($field) {
                $images = is_array($field['option_images'] ?? null) ? $field['option_images'] : [];

                return collect($images)
                    ->map(fn ($path) => trim((string) $path))
                    ->filter();
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function uniqueFieldKey(string $key, array $usedKeys): string
    {
        $base = preg_replace('/[^a-z0-9_]/', '_', Str::lower($key)) ?: 'field';
        $candidate = $base;
        $counter = 2;

        while (in_array($candidate, $usedKeys, true)) {
            $candidate = $base.'_'.$counter;
            $counter++;
        }

        return $candidate;
    }

    protected function uniqueSlug(string $source, ?MarketingForm $ignore = null): string
    {
        $base = Str::slug($source) ?: 'formulir';
        $candidate = $base;
        $counter = 2;

        while (
            MarketingForm::query()
                ->where('slug', $candidate)
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
                ->exists()
        ) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    protected function deleteSubmissionFiles(MarketingFormSubmission $submission): void
    {
        foreach (($submission->answers ?? []) as $answer) {
            $value = $answer['value'] ?? null;

            if (($answer['type'] ?? null) === 'file' && is_array($value) && ! empty($value['path'])) {
                Storage::disk('public')->delete((string) $value['path']);
            }
        }
    }
}
