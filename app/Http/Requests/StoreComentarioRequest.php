<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComentarioRequest extends FormRequest
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
            'descricao'  => 'required|string|max:255',
            'estrela'    => 'required|integer',
            'produto_id' => 'required|integer',
            'usuario_id' => 'required|integer',
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
            'descricao.required'  => 'A descrição é obrigatória.',
            'estrela.required'    => 'A estrela é obrigatória.',
            'produto_id.required' => 'O produto_id é obrigatório.',
            'usuario_id.required' => 'O usuario_id é obrigatório.',
        ];
    }
}
