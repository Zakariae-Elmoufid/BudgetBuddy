<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'group_id' => $this->id,
            'group_name' => $this->name,
            'total_expenses' => $this->expenses->sum('price'), 
             'expenses' => $this->expenses->map(function ($expense) {
                return [
                    'title' => $expense->title,
                    'users' => $expense->users->pluck('email') // Récupérer uniquement les emails des utilisateurs
                ];
             }),
             'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
