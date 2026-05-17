<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Specialty;
use App\Models\Doctor;

class Service extends Model
{
    use HasFactory;

    protected $primaryKey = 'service_id';

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'status',
    ];

    public $timestamps = true;

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'service_specialty', 'service_id', 'specialty_id', 'service_id', 'specialty_id');
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_service', 'service_id', 'doctor_id', 'service_id', 'doctor_id');
    }
}
