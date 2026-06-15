<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $primaryKey = 'patient_id';

    protected $fillable = [
        'document_type',
        'document_number',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'blood_type',
        'allergies',
        'previous_conditions',
        'insurance_type',
        'status',
    ];

    public $timestamps = true;

    protected $casts = [
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    public function clinicalRecords()
    {
        return $this->hasMany(ClinicalRecord::class, 'patient_id', 'patient_id');
    }
}
