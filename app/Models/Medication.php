<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'medication_id';

    /**
     * Indicates if the model should be timestamped.
     * (Your migration doesn't have timestamps).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patient_id',
        'name',
        'description',
    ];

    /**
     * Get the patient this medication belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * The prescriptions that this medication is part of.
     * This defines the many-to-many relationship.
     */
    public function prescriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            Prescription::class,
            'medication_prescriptions', // The pivot table
            'medication_id',            // The foreign key for this model
            'prescription_id'           // The foreign key for the Prescription model
        );
    }

    // We will add the 'prescriptions()' many-to-many relationship
    // after we create the 'Prescription' model.
}