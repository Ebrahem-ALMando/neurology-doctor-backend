<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'from_status',
        'to_status',
        'changed_by_id',
        'changed_by_type',
        'note',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
