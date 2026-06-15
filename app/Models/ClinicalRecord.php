<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalRecord extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'type',
        'content',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
}
