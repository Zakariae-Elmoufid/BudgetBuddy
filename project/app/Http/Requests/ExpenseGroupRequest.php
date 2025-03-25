<?php

namespace App\Http\Requests;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $group = $this->route('group');
        return $group && $group->users->contains(Auth::id());

    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $group = $this->route('group');
        $groupUserIds = $group ? $group->users->pluck('id')->toArray() : [];
        $isCustomSplit = $this->input('split_type') === 'custom';

        return [
            'description' => ['required', 'string', 'max:255'],
            // 'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'split_type' => ['required', Rule::in(['equal', 'custom'])],
            'category' => ['nullable', 'string', 'max:100'],
            
            
            // Ajouter la validation `required` uniquement si `split_type` est `custom`
        'shares.*.percentage' => array_merge(
            ['numeric', 'min:0.01', 'max:100'],
            $isCustomSplit ? ['required'] : []
        ),

        // Ajouter la validation `required` uniquement si `split_type` est `equal`
        'shares.*.amount' => array_merge(
            ['numeric', 'min:1'],
            !$isCustomSplit ? ['required'] : []
        ),
        ];
    }


      /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         // Vérifier que la somme des pourcentages est 100% pour le partage personnalisé
    //         if ($this->split_type === 'custom' && $this->has('shares')) {
    //             $totalPercentage = collect($this->shares)->sum('percentage');
    //             if (abs($totalPercentage - 100) > 0.01) {
    //                 $validator->errors()->add('shares', 'La somme des pourcentages doit être égale à 100%.');
    //             }
    //         }

    //         // Vérifier que la somme des contributions est égale au montant total
    //         if ($this->has('contributions')) {
    //             $totalContributions = collect($this->contributions)->sum('amount');
    //             if (abs($totalContributions - $this->amount) > 0.01) {
    //                 $validator->errors()->add(
    //                     'contributions', 
    //                     'La somme des contributions doit être égale au montant total de la dépense.'
    //                 );
    //             }
    //         }
    //     });
    // }

   
}
