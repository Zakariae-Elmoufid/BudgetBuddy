<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'required|string|min:10',
            'users' => 'array',
            'users.*.email' => 'email', // Chaque utilisateur dans le tableau "users" doit avoir une adresse email valide
            'users.*.amount' => 'required|numeric', // Chaque utilisateur doit avoir un montant numérique
        ];
    }
}
