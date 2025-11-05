<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prescription extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'prescription_id';

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
        'emission_date',
        'file_url',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'emission_date' => 'date',
    ];

    /**
     * Get the patient this prescription belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * The medications that are part of this prescription.
     * This defines the many-to-many relationship.
     */
    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'medication_prescriptions', // The pivot table
            'prescription_id',          // The foreign key for this model
            'medication_id'             // The foreign key for the Medication model
        );
    }
}