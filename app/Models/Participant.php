<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'session_token',
        'name',
        'identifier',
        'started_at',
        'finished_at',
        'status',
        'score',
        'total_correct',
        'total_answered',
        'total_questions',
        'question_order',
        'option_orders',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'question_order' => 'array',
        'option_orders' => 'array',
        'score' => 'float',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(ParticipantAnswer::class);
    }
}
