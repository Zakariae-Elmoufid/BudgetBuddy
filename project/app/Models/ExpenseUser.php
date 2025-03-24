<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExpenseUser extends Pivot
{
    protected $fillable = ['user_amount'];
}