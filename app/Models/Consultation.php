<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'status',
        'closed_at',
        'last_message_at',
        'last_sender_id',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function lastSender()
    {
        return $this->belongsTo(User::class, 'last_sender_id');
    }

    public function messages()
    {
        return $this->hasMany(ConsultationMessage::class);
    }

    public function attachments()
    {
        return $this->hasMany(ConsultationAttachment::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ConsultationStatusLog::class);
    }
}
