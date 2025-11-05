<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'patient_id';

    /**
     * Indicates if the model should be timestamped.
     * (Your migration doesn't have timestamps).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'care_group_id',
        'names',
        'surnames',
        'cellphone',
        'telephone',
        'address',
    ];

    /**
     * Get the care group this patient belongs to.
     */
    public function careGroup(): BelongsTo
    {
        return $this->belongsTo(CareGroup::class, 'care_group_id', 'care_group_id');
    }

    /**
     * Get the health problems associated with this patient.
     */
    public function healthProblems(): HasMany
    {
        return $this->hasMany(HealthProblem::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the medical contacts associated with this patient.
     */
    public function medicalContacts(): HasMany
    {
        return $this->hasMany(MedicalContact::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the medications associated with this patient.
     */
    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the exams associated with this patient.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the prescriptions associated with this patient.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the notes associated with this patient.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'patient_id', 'patient_id');
    }
    // We will add relationships for health problems,
    // medications, etc., after we create those models.
}