<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GlobalFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search'   => ['nullable', 'string'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'from'     => ['nullable', 'date'],
            'to'       => ['nullable', 'date'],
        ];
    }


    public function filters(): array
    {
        return [
            'search'   => $this->input('search'),
            'page'     => (int) $this->input('page', 1),
            'per_page' => (int) $this->input('per_page', 10),
            'from'     => $this->input('from'),
            'to'       => $this->input('to'),
        ];
    }
}
