<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
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
            'nome' => 'required|max:100',
            'email' => 'required|max:255|unique:empresas',
            'cor' => 'required',
            'logo' => 'nullable',
            'whatsapp' => 'max:20',
            'instagram' => 'max:255',
            'facebook' => 'max:255',
            'telefone' => 'max:20',
            'descricao' => 'max:255',
            'palavras_chaves' => 'max:255',
            'titulo' => 'max:255',
            'cnpj' => 'required|unique:empresas',

            'endereco' => 'required|array',
            'endereco.rua' => 'required|string',
            'endereco.cidade' => 'required|string',
            'endereco.estado' => 'required|string',
            'endereco.cep' => 'required|string',
            'endereco.pais' => 'required|string',
            'endereco.numero' => 'required|string',
            'endereco.bairro' => 'required|string',
            'endereco.complemento' => 'max:255|string'
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
            'nome.required' => 'O campo nome é obrigatório',
            'email.required' => 'O campo email é obrigatório',
            'email.unique' => 'O email informado já está em uso',
            'cnpj.required' => 'O campo cnpj é obrigatório',
            'cnpj.unique' => 'O cnpj informado já está em uso',
            'endereco.rua.required' => 'O campo rua é obrigatório',
            'endereco.cidade.required' => 'O campo cidade é obrigatório',
            'endereco.estado.required' => 'O campo estado é obrigatório',
            'endereco.cep.required' => 'O campo cep é obrigatório',
            'endereco.pais.required' => 'O campo pais é obrigatório',
            'endereco.numero.required' => 'O campo numero é obrigatório',
            'endereco.bairro.required' => 'O campo bairro é obrigatório',
        ];
    }
}