<?php

namespace App\Models;

use App\Models\Queue;
use App\Models\RoleUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_users_id',
    ];

    public function role()
    {
        return $this->belongsTo(RoleUser::class, 'role_users_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all of the comments for the Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function queue(): HasMany
    {
        return $this->hasMany(Queue::class, 'assignments_id', 'id');
    }

}
