<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'question_id',
        'selected_option',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
