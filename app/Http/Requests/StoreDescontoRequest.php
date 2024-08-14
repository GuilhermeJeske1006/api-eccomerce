<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDescontoRequest extends FormRequest
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
            'desconto'    => ['numeric'],
            'porcentagem' => ['numeric'],
            'produto_id'  => ['required'],

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
            'desconto.numeric'    => 'O campo desconto deve ser um número',
            'porcentagem.numeric' => 'O campo porcentagem deve ser um número',
            'produto_id.required' => 'O campo produto_id é obrigatório',
        ];
    }
}
