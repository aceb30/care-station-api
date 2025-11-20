<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class CareGroup extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'care_group_id';

    /**
     * Indicates if the model should be timestamped.
     * We set this to false because migration doesn't have timestamps().
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'photo_url',
        'admin_id',
    ];

    /**
     * Get the admin (a User) that this CareGroup belongs to.
     */
    public function admin(): BelongsTo
    {
        // This defines the "belongs to" relationship:
        // 1. Related Model: User::class
        // 2. Foreign Key: 'admin_id' (on this table)
        // 3. Owner Key: 'user_id' (on the 'users' table)
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    /**
     * The users that are members of this care group.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,           // The model we are joining to
            'group_members',       // The name of our pivot table
            'care_group_id',       // The foreign key on the pivot table for *this* model
            'user_id'              // The foreign key on the pivot table for the *other* model
        )->using(GroupMember::class); // Tell Laravel to use our custom Pivot model
    }

    /**
     * Get the patients associated with this care group.
     */
    public function patients(): HasMany
    {
        // This defines the "has many" relationship:
        // 1. Related Model: Patient::class
        // 2. Foreign Key: 'care_group_id' (on the 'patients' table)
        // 3. Local Key: 'care_group_id' (on this 'care_groups' table)
        return $this->hasMany(Patient::class, 'care_group_id', 'care_group_id');
    }

    /**
     * Get the tasks associated with this care group.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'care_group_id', 'care_group_id');
    }
    // We will add the 'members()' and 'patients()' relationships
    // here later, after we create those models.
}
