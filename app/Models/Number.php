<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'number',
        'label',
        'type',
        'description',
        'workspace',
        'company_name',
        'status',
    ];
}
