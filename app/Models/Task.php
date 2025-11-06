<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'task_id';

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
        'title',
        'description',
        'frequency',
        'category',
        'begin_time',
        'end_time',
        'done',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'begin_time' => 'datetime',
        'end_time' => 'datetime',
        'done' => 'boolean',
    ];

    /**
     * Get the care group this task belongs to.
     */
    public function careGroup(): BelongsTo
    {
        return $this->belongsTo(CareGroup::class, 'care_group_id', 'care_group_id');
    }

    /**
     * The users that are assigned to this task.
     * This defines the many-to-many relationship via 'task_assignments'.
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'task_assignments', // The pivot table
            'task_id',          // The foreign key for this model
            'user_id'           // The foreign key for the User model
        );
    }
}