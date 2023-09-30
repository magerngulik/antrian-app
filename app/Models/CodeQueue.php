<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeQueue extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'queue_code',
    ];
}
