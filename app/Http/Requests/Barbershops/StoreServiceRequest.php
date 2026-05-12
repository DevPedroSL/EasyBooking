<?php

namespace App\Http\Requests\Barbershops;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50'],
            'duration' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'visibility' => ['required', 'in:public,private'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'max:3072'],
        ];
    }
}
