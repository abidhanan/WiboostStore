<?php

namespace App\Http\Controllers;

use App\Models\MarketingForm;
use App\Models\MarketingFormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicFormController extends Controller
{
    public function show(MarketingForm $form)
    {
        return view('public.forms.show', [
            'form' => $form,
            'formInactive' => ! $form->is_active,
        ]);
    }

    public function store(Request $request, MarketingForm $form)
    {
        abort_unless($form->is_active, 404);

        $fields = $form->fields ?? [];
        $validated = $request->validate(
            $this->answerRules($fields),
            [],
            $this->answerAttributes($fields)
        );

        $submission = $form->submissions()->create([
            'public_token' => Str::random(48),
            'answers' => $this->normalizeAnswers($request, $fields),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return redirect()->route('public.forms.thanks', [
            'form' => $form->slug,
            'submission' => $submission->public_token,
        ]);
    }

    public function thanks(MarketingForm $form, MarketingFormSubmission $submission)
    {
        abort_unless((int) $submission->marketing_form_id === (int) $form->id, 404);

        return view('public.forms.thanks', compact('form', 'submission'));
    }

    protected function answerRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = (string) ($field['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $baseRule = ! empty($field['required']) ? 'required' : 'nullable';
            $type = (string) ($field['type'] ?? 'text');
            $path = "answers.{$key}";

            $rules[$path] = match ($type) {
                'email' => [$baseRule, 'string', 'email:rfc', 'max:255'],
                'number' => [$baseRule, 'numeric'],
                'date' => [$baseRule, 'date'],
                'textarea' => [$baseRule, 'string', 'max:5000'],
                'select' => [$baseRule, 'string', Rule::in($this->optionLabels($field))],
                'checkbox' => [$baseRule, 'array', ! empty($field['required']) ? 'min:1' : 'nullable'],
                'file' => [$baseRule, 'file'],
                default => [$baseRule, 'string', 'max:1000'],
            };

            if ($type === 'checkbox') {
                $rules[$path.'.*'] = ['string', Rule::in($this->optionLabels($field))];
            }
        }

        return $rules;
    }

    protected function answerAttributes(array $fields): array
    {
        $attributes = [];

        foreach ($fields as $field) {
            if (! empty($field['key']) && ! empty($field['label'])) {
                $attributes['answers.'.$field['key']] = $field['label'];
            }
        }

        return $attributes;
    }

    protected function normalizeAnswers(Request $request, array $fields): array
    {
        $answers = [];

        foreach ($fields as $field) {
            $key = (string) ($field['key'] ?? '');
            $type = (string) ($field['type'] ?? 'text');

            if ($key === '') {
                continue;
            }

            if ($type === 'file') {
                $file = $request->file("answers.{$key}");
                $value = null;

                if ($file) {
                    $path = $file->store('form-submissions', 'public');
                    $value = [
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'name' => $file->getClientOriginalName(),
                    ];
                }
            } elseif ($type === 'checkbox') {
                $value = array_values((array) $request->input("answers.{$key}", []));
            } else {
                $value = trim((string) $request->input("answers.{$key}", ''));
            }

            $answers[$key] = [
                'label' => (string) ($field['label'] ?? $key),
                'type' => $type,
                'value' => $value,
            ];
        }

        return $answers;
    }

    protected function optionLabels(array $field): array
    {
        return collect($field['options'] ?? [])
            ->map(fn ($option) => trim((string) (is_array($option) ? ($option['label'] ?? '') : $option)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
