<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_message_id',
        'consultation_id',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function message()
    {
        return $this->belongsTo(ConsultationMessage::class, 'consultation_message_id');
    }
}
