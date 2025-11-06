<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalContact extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'medical_contact_id';

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
        'cellphone',
        'telephone',
        'mail',
        'address',
        'description',
    ];

    /**
     * Get the patient this medical contact belongs to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }
}