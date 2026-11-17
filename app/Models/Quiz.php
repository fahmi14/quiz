<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'description',
        'starts_at',
        'expires_at',
        'duration_minutes',
        'shuffle_questions',
        'shuffle_options',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order_num');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function isExpired(): bool
    {
        if (!$this->is_active) {
            return true;
        }
        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false; // not yet started
        }
        if ($this->expires_at && now()->gt($this->expires_at)) {
            return true;
        }
        return false;
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at && now()->lt($this->starts_at);
    }
}
