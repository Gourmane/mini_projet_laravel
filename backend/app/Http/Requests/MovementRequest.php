<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', Rule::in(['IN', 'OUT'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000000'],
            'note' => ['nullable', 'string', 'max:500'],
            // These values are calculated by the server.
            'user_id' => ['prohibited'],
            'quantity_before' => ['prohibited'],
            'quantity_after' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.integer' => 'La quantité doit être un nombre entier.',
            'quantity.min' => 'La quantité doit être supérieure à zéro.',
        ];
    }
}

