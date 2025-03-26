<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'amount_total',
        'user_id',
        'group_id',
        'date',
        'split_type', 
        'category',
    ];
    
    public function tags(){
        return $this->belongsToMany(Tag::class,'expense_tag');
    }
     
     

     public function user()
    {
    return $this->belongsTo(User::class);
    }   

    public function contributions()
    {
        return $this->hasMany(Contribution::class);
    }


 

    public function group(){
        return $this->belongsTo(Group::class);
    }    

    public function shares()
    {
        return $this->hasMany(ExpenseShare::class);
    }

    



}
