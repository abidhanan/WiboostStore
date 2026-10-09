<?php

namespace Tests\Feature;

use App\Models\MarketingForm;
use App\Models\MarketingFormSubmission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketingFormExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_form_has_a_friendly_public_page_instead_of_a_404(): void
    {
        $form = $this->makeForm(false);

        $this->get(route('public.forms.show', $form))
            ->assertOk()
            ->assertDontSee('Formulir Wiboost Store')
            ->assertSee('Formulir sedang tidak aktif')
            ->assertSee('Silakan hubungi pengelola formulir untuk informasi selengkapnya.')
            ->assertDontSee('Formulir ini belum dapat menerima jawaban.')
            ->assertDontSee('Kirim Formulir');
    }

    public function test_public_choice_fields_render_vertically_with_option_images_and_no_placeholders(): void
    {
        $form = $this->makeForm(true, [[
            'key' => 'paket',
            'label' => 'Pilih Paket',
            'type' => 'select',
            'help' => null,
            'options' => ['Paket A', 'Paket B'],
            'option_images' => ['Paket A' => 'form-options/paket-a.jpg'],
            'required' => true,
        ], [
            'key' => 'fitur',
            'label' => 'Pilih Fitur',
            'type' => 'checkbox',
            'help' => null,
            'options' => ['Fitur Satu', 'Fitur Dua'],
            'option_images' => [],
            'required' => false,
        ]]);

        $this->get(route('public.forms.show', $form))
            ->assertOk()
            ->assertSee('type="radio"', false)
            ->assertSee('type="checkbox"', false)
            ->assertSee('form-options/paket-a.jpg', false)
            ->assertSee('wiboost-public-checkbox-grid', false)
            ->assertDontSee('placeholder=', false);
    }

    public function test_admin_can_save_an_image_for_each_choice_option(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.forms.store'), [
            'title' => 'Formulir Bergambar',
            'slug' => 'formulir-bergambar',
            'is_active' => '1',
            'fields' => [[
                'label' => 'Pilih Produk',
                'type' => 'select',
                'required' => '1',
                'option_items' => [[
                    'label' => 'Produk A',
                    'image' => UploadedFile::fake()->image('produk-a.jpg', 300, 300),
                ], [
                    'label' => 'Produk B',
                ]],
            ]],
        ]);

        $form = MarketingForm::query()->where('slug', 'formulir-bergambar')->firstOrFail();
        $field = $form->fields[0];

        $response->assertRedirect(route('admin.forms.show', $form));
        $this->assertSame(['Produk A', 'Produk B'], $field['options']);
        $this->assertArrayHasKey('Produk A', $field['option_images']);
        $this->assertSame('produk-a.jpg', $field['option_image_names']['Produk A']);
        Storage::disk('public')->assertExists($field['option_images']['Produk A']);
    }

    public function test_admin_form_index_uses_the_standard_table_layout(): void
    {
        $admin = $this->makeAdmin();
        $form = $this->makeForm(true);
        $form->update(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('admin.forms.index'))
            ->assertOk()
            ->assertSee('<table', false)
            ->assertSee('Judul')
            ->assertSee('Slug')
            ->assertSee('Deskripsi')
            ->assertSee('Pertanyaan')
            ->assertSee('Jawaban')
            ->assertSee('Dibuat Oleh')
            ->assertSee('Waktu Dibuat')
            ->assertSee('Tautan')
            ->assertSee('Salin Tautan')
            ->assertDontSee('data-input-name="month"', false)
            ->assertDontSee('data-input-name="year"', false)
            ->assertSee('forms-table-scroll', false);

        $view = file_get_contents(resource_path('views/admin/forms/index.blade.php'));
        $this->assertStringContainsString("['name' => 'eye'", $view);
        $tableSource = strstr($view, '<table');
        $tableSource = strstr($tableSource, '</table>', true);
        $expectedColumnOrder = [
            '>Waktu Dibuat<',
            '>Judul<',
            '>Slug<',
            '>Deskripsi<',
            '>Dibuat Oleh<',
            '>Pertanyaan<',
            '>Jawaban<',
            '>Status<',
            '>Benefit<',
            '>Tautan<',
            '>Aksi<',
        ];
        $lastPosition = -1;
        foreach ($expectedColumnOrder as $column) {
            $position = strpos($tableSource, $column);
            $this->assertNotFalse($position, "Kolom {$column} tidak ditemukan.");
            $this->assertGreaterThan($lastPosition, $position, "Urutan kolom {$column} tidak sesuai.");
            $lastPosition = $position;
        }
        $this->assertStringContainsString("admin.forms.partials.copy-link-button", $tableSource);
        $this->assertStringContainsString('is-copied', $view);
        $this->assertStringContainsString('body.wiboost-admin-shell .wiboost-form-action-button--copy.is-copied', $view);
        $this->assertStringContainsString('document.addEventListener', $view);
        $this->assertStringContainsString('}, true);', $view);
        $this->assertStringContainsString('Lihat Formulir', $view);
        $this->assertStringContainsString('Hapus Formulir</button>', $view);
        $this->assertStringNotContainsString('data-copy-label', $view);

        $showView = file_get_contents(resource_path('views/admin/forms/show.blade.php'));
        $copyButtonView = file_get_contents(resource_path('views/admin/forms/partials/copy-link-button.blade.php'));
        $this->assertStringContainsString("admin.forms.partials.copy-link-button", $showView);
        $this->assertStringContainsString('data-copy-success', $copyButtonView);
        $this->assertStringContainsString('h-10 w-10', $copyButtonView);
        $this->assertStringContainsString('is-copied', $showView);
        $this->assertStringContainsString('}, true);', $showView);
        $this->assertStringNotContainsString('data-copy-label', $showView);
        $this->assertStringNotContainsString('data-copy-label', $copyButtonView);
    }

    public function test_form_navigation_preserves_the_selected_index_filters(): void
    {
        $admin = $this->makeAdmin();
        $form = $this->makeForm(true);
        $form->update(['created_by' => $admin->id]);
        $returnTo = route('admin.forms.index', [
            'search' => 'Formulir Uji',
            'form_filter' => 'active',
        ]);

        $this->actingAs($admin)
            ->get($returnTo)
            ->assertOk()
            ->assertSee(route('admin.forms.show', $form), false)
            ->assertSee('return_to=', false);

        $this->actingAs($admin)
            ->get(route('admin.forms.show', ['form' => $form, 'return_to' => $returnTo]))
            ->assertOk()
            ->assertSee('href="'.e($returnTo).'"', false);

        $this->actingAs($admin)
            ->get(route('admin.forms.edit', ['form' => $form, 'return_to' => $returnTo]))
            ->assertOk()
            ->assertSee('name="return_to"', false)
            ->assertSee('value="'.e($returnTo).'"', false);

        $this->actingAs($admin)
            ->get(route('admin.forms.create', ['return_to' => $returnTo]))
            ->assertOk()
            ->assertSee('name="return_to"', false)
            ->assertSee('value="'.e($returnTo).'"', false);
    }

    public function test_form_answer_page_preserves_description_lines_and_exports_excel(): void
    {
        $admin = $this->makeAdmin();
        $form = $this->makeForm(true);
        $form->update([
            'created_by' => $admin->id,
            'description' => "Baris deskripsi pertama.\nBaris deskripsi kedua.",
        ]);

        MarketingFormSubmission::query()->create([
            'marketing_form_id' => $form->id,
            'public_token' => str()->random(64),
            'answers' => [
                'nama' => [
                    'key' => 'nama',
                    'label' => 'Nama',
                    'type' => 'text',
                    'value' => 'Budi',
                ],
            ],
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.forms.show', $form))
            ->assertOk()
            ->assertSee("Baris deskripsi pertama.\nBaris deskripsi kedua.")
            ->assertSee('whitespace-pre-line', false)
            ->assertSee('Cetak Laporan');

        $export = $this->actingAs($admin)->get(route('admin.forms.export_excel', $form));
        $export->assertOk();
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $export->headers->get('Content-Type')
        );
        $this->assertStringContainsString(
            'laporan-formulir-'.$form->slug,
            (string) $export->headers->get('Content-Disposition')
        );
    }

    public function test_admin_form_index_has_no_period_filter_and_uses_transaction_style_pagination(): void
    {
        $admin = $this->makeAdmin();

        foreach (range(1, 21) as $index) {
            $form = $this->makeForm(true);
            $form->update([
                'title' => 'Formulir Periode '.$index,
                'created_by' => $admin->id,
            ]);
        }

        $olderForm = $this->makeForm(true);
        $olderForm->forceFill([
            'title' => 'Formulir Tahun Lama',
            'created_by' => $admin->id,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.forms.index'))
            ->assertOk()
            ->assertSee('wiboost-pagination-wrap', false)
            ->assertSee('page=2', false)
            ->assertDontSee('Formulir Tahun Lama');

        $this->actingAs($admin)
            ->get(route('admin.forms.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Formulir Tahun Lama')
            ->assertDontSee('data-input-name="month"', false)
            ->assertDontSee('data-input-name="year"', false);
    }

    public function test_dashboard_does_not_show_form_or_social_media_summaries(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', [
                'month' => now()->format('m'),
                'year' => now()->format('Y'),
            ]))
            ->assertOk()
            ->assertDontSee('Ringkasan Formulir')
            ->assertDontSee('Ringkasan Media Sosial');
    }

    public function test_public_file_field_uses_an_indonesian_file_picker(): void
    {
        $form = $this->makeForm(true, [[
            'key' => 'lampiran',
            'label' => 'Lampiran',
            'type' => 'file',
            'help' => null,
            'options' => [],
            'option_images' => [],
            'required' => false,
        ]]);

        $this->get(route('public.forms.show', $form))
            ->assertOk()
            ->assertSee('Pilih File')
            ->assertSee('Belum ada file')
            ->assertSee('data-public-file-input', false)
            ->assertDontSee('No file chosen');
    }

    public function test_public_form_accepts_the_saved_choice_labels(): void
    {
        $form = $this->makeForm(true, [[
            'key' => 'paket',
            'label' => 'Pilih Paket',
            'type' => 'select',
            'help' => null,
            'options' => ['Paket A', 'Paket B'],
            'option_images' => [],
            'required' => true,
        ]]);

        $this->post(route('public.forms.store', $form), [
            'answers' => ['paket' => 'Paket B'],
        ])->assertRedirect();

        $this->assertSame('Paket B', $form->submissions()->firstOrFail()->answers['paket']['value']);
    }

    public function test_form_descriptions_and_account_notes_preserve_manual_line_breaks(): void
    {
        $form = $this->makeForm(true, [[
            'key' => 'nama',
            'label' => 'Nama',
            'type' => 'text',
            'help' => "Petunjuk baris pertama.\nPetunjuk baris kedua.",
            'options' => [],
            'option_images' => [],
            'required' => true,
        ]]);
        $form->update(['description' => "Deskripsi baris pertama.\nDeskripsi baris kedua."]);

        $this->get(route('public.forms.show', $form))
            ->assertOk()
            ->assertSee("Deskripsi baris pertama.\nDeskripsi baris kedua.")
            ->assertSee("Petunjuk baris pertama.\nPetunjuk baris kedua.")
            ->assertSee('text-align: left', false)
            ->assertSee('white-space: pre-line', false);

        $thanksView = file_get_contents(resource_path('views/public/forms/thanks.blade.php'));
        $historyView = file_get_contents(resource_path('views/user/history.blade.php'));

        $this->assertStringContainsString("\$item['key'] === 'notes'", $thanksView);
        $this->assertStringContainsString('style="text-align: left; white-space: pre-line;">{{ $credentials[\'notes\'] }}', $historyView);
    }

    public function test_public_form_has_no_outer_card_but_keeps_the_heading_card(): void
    {
        $form = $this->makeForm(true);

        $this->get(route('public.forms.show', $form))
            ->assertOk()
            ->assertSee('wiboost-public-guest-card--unframed', false)
            ->assertSee('wiboost-public-form-heading', false)
            ->assertSee('border-radius: 2rem', false)
            ->assertSee('background-image: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%)', false)
            ->assertSee('0 20px 42px -16px rgba(90, 118, 200, .42)', false)
            ->assertSee('background: transparent !important', false)
            ->assertSee('background-image: none !important', false)
            ->assertSee('gap: 1.75rem', false)
            ->assertDontSee('class="wiboost-public-form space-y-5"', false)
            ->assertDontSee('wiboost-public-form-badge', false)
            ->assertDontSee('Formulir Wiboost Store');
    }

    private function makeForm(bool $active, ?array $fields = null): MarketingForm
    {
        return MarketingForm::query()->create([
            'title' => 'Formulir Uji',
            'slug' => 'formulir-uji-'.str()->random(8),
            'description' => 'Formulir untuk pengujian.',
            'fields' => $fields ?? [[
                'key' => 'nama',
                'label' => 'Nama',
                'type' => 'text',
                'help' => null,
                'options' => [],
                'option_images' => [],
                'required' => true,
            ]],
            'is_active' => $active,
            'benefit_enabled' => false,
        ]);
    }

    private function makeAdmin(): User
    {
        Role::query()->firstOrCreate(['id' => User::ROLE_SUPER_ADMIN], ['name' => 'Admin']);

        return User::factory()->create(['role_id' => User::ROLE_SUPER_ADMIN]);
    }
}
