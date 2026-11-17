<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'order_num',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_answer',
        'points',
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
