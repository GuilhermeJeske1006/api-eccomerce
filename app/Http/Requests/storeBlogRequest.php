<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeBlogRequest extends FormRequest
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
            'titulo'     => 'required|max:255',
            'texto'      => 'required',
            'empresa_id' => 'required|integer',

        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required'     => 'O título é obrigatório.',
            'texto.required'      => 'O texto é obrigatório.',
            'empresa_id.required' => 'O empresa_id é obrigatório.',
        ];
    }
}
