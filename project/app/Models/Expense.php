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
        'price',
        'user_id',
        'group_id',
    ];
    
    public function tags(){
        return $this->belongsToMany(Tag::class,'expense_tag');
    }
     
     

     public function user()
    {
    return $this->belongsTo(User::class);
    }   

    public function users(){
        return $this->belongsToMany(user::class,'expense_user');
     } 


    public function group(){
        return $this->belongsTo(Group::class);
    }    

    



}
