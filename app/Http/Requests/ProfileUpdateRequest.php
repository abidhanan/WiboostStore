<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\WiboostInputRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => WiboostInputRules::nameRules(),
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'whatsapp' => WiboostInputRules::numberRules(20),
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama panggilan',
            'email' => 'alamat email',
            'whatsapp' => 'nomor kontak',
            'profile_photo' => 'foto profil',
        ];
    }

    public function messages(): array
    {
        return [
            ...WiboostInputRules::validationMessages(),
            'profile_photo.uploaded' => 'Foto profil gagal diunggah. Pastikan ukuran file tidak terlalu besar lalu coba lagi.',
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 5 MB.',
        ];
    }
}
