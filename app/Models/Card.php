<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'holder_name',
        'number',
        'expiration_date',
        'cvv',
        'zip_code',
        'user_id'
    ];
}
