<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdutoRequest extends FormRequest
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
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'nome'            => 'required|max:255',
            'valor'           => 'required',
            'empresa_id'      => 'required|numeric',
            'categoria_id'    => 'required|numeric',
            'foto'            => 'nullable|string',
            'largura'         => 'required|numeric',
            'altura'          => 'required|numeric',
            'comprimento'     => 'required|numeric',
            'descricao'       => 'max:255',
            'descricao_longa' => '',
            'peso'            => 'numeric|max:255',
            'material'        => 'max:50',
            'fotos.*'         => 'nullable',
            'irParaSite'      => 'boolean',
            'destaque'        => 'boolean',
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
            'nome.required'         => 'O campo nome é obrigatório',
            'valor.required'        => 'O campo valor é obrigatório',
            'empresa_id.required'   => 'O campo empresa_id é obrigatório',
            'categoria_id.required' => 'O campo categoria_id é obrigatório',
            'largura.required'      => 'O campo largura é obrigatório',
            'altura.required'       => 'O campo altura é obrigatório',
            'comprimento.required'  => 'O campo comprimento é obrigatório',
            'peso.required'         => 'O campo peso é obrigatório',
            'material.required'     => 'O campo material é obrigatório',

        ];
    }
}
