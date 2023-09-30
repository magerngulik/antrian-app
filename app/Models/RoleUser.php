<?php

namespace App\Models;

use App\Models\CodeQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleUser extends Model
{
    use HasFactory;
  
    protected $fillable = [
        'nama_role',
    ];

    /**
     * Get the user that owns the RoleUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(CodeQueue::class, 'code_id', 'id');
    }

}
