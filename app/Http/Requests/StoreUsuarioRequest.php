<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|max:255',
            'password' => 'required|max:255',
            'endereco_id' => '',
            'empresa_id' => '',
            'is_master' => 'required',
            'foto' => 'string|nullable',
            'cpf' => 'required|unique:users',
            'telefone' => 'required',
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
            'name.required' => 'O campo nome é obrigatório',
            'email.required' => 'O campo email é obrigatório',
            'email.unique' => 'O email informado já está em uso',
            'password.required' => 'O campo senha é obrigatório',
            'cpf.required' => 'O campo cpf é obrigatório',
            'cpf.unique' => 'O cpf informado já está em uso',
            'telefone.required' => 'O campo telefone é obrigatório',
        ];
    }
}
