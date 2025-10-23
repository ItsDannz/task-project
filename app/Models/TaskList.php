<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    protected $table = 'lists';

    protected $fillable = [
        'title',
        'description',
        'user_id',
    ];

    public function tasks(): HasMany
    {
        // Tambahkan foreign key eksplisit biar gak salah tebak
        return $this->hasMany(Task::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
