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
        'options_data',
        'correct_answer',
        'points',
    ];

    protected $casts = [
        'options_data' => 'array',
        'points' => 'integer',
        'order_num' => 'integer',
    ];

    /**
     * Dapatkan semua opsi jawaban (gabungan option_a-e dan options_data)
     * Mengembalikan array associative: ['A' => '...', 'B' => '...', ...]
     */
    public function getAllOptions(): array
    {
        $options = [];
        $letters = range('A', 'Z');

        // Jika ada options_data (JSON)
        if (!empty($this->options_data) && is_array($this->options_data)) {
            foreach ($this->options_data as $key => $val) {
                if ($val !== null && trim((string)$val) !== '') {
                    $options[strtoupper($key)] = (string)$val;
                }
            }
        } else {
            // Fallback ke kolom option_a s/d option_e
            foreach (['A', 'B', 'C', 'D', 'E'] as $k) {
                $prop = 'option_' . strtolower($k);
                if (!empty($this->$prop)) {
                    $options[$k] = (string)$this->$prop;
                }
            }
        }

        return $options;
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(ParticipantAnswer::class);
    }
}
