<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultationMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultation_id',
        'sender_id',
        'sender_type',
        'subject',
        'message',
        'read_by_patient',
        'read_by_doctor',
        'deleted_at',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments()
    {
        return $this->hasMany(ConsultationAttachment::class, 'consultation_message_id');
    }
}
