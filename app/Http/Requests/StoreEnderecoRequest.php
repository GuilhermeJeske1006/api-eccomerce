<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnderecoRequest extends FormRequest
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
            'cep'    => 'required',
            'rua'    => 'required',
            'numero' => 'required | integer',
            'bairro' => 'required',
            'cidade' => 'required',
            'estado' => 'required',
            'pais'   => 'required',
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
            'cep.required'    => 'O campo CEP é obrigatório',
            'rua.required'    => 'O campo Rua é obrigatório',
            'numero.required' => 'O campo Número é obrigatório',
            'numero.integer'  => 'O campo Número deve ser um número inteiro',
            'bairro.required' => 'O campo Bairro é obrigatório',
            'cidade.required' => 'O campo Cidade é obrigatório',
            'estado.required' => 'O campo Estado é obrigatório',
            'pais.required'   => 'O campo País é obrigatório',
        ];
    }
}
