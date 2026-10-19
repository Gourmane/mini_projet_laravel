<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        // PATCH may update just one field; POST must provide all required fields.
        $presence = 'sometimes';
        if ($this->isMethod('POST')) {
            $presence = 'required';
        }

        return [
            'sku' => [$presence, 'string', 'max:80', Rule::unique('products')->ignore($this->route('product'))],
            'name' => [$presence, 'string', 'max:200'],
            'category_id' => [$presence, 'integer', 'exists:categories,id'],
            'brand' => ['nullable', 'string', 'max:100'],
            'image_url' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'min_stock' => [$presence, 'integer', 'min:0', 'max:100000000'],
            'is_active' => ['sometimes', 'boolean'],
            'quantity' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'Ce SKU est déjà utilisé.',
            'image_url.url' => 'Saisissez une URL d’image HTTP ou HTTPS valide.',
            'image_url.max' => 'L’URL de l’image ne doit pas dépasser 2048 caractères.',
            'quantity.prohibited' => 'Le stock se modifie uniquement par un mouvement.',
        ];
    }
}

