<?php

namespace Tests\Feature;

use App\Models\MarketingForm;
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
            ->assertSee('background: rgba(255, 255, 255, .95)', false)
            ->assertSee('box-shadow: 0 22px 52px', false)
            ->assertSee('background: transparent !important', false)
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
