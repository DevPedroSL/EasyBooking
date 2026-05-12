<?php

namespace App\Http\Requests\Barbershops;

class UpdateServiceRequest extends StoreServiceRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ]);
    }
}
