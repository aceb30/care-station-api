<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\CareGroup;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'user_id'; // This is correct!

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'names',        // <-- Fixed from 'name'
        'surnames',     // <-- Added
        'email',
        'password',
        'cellphone',    // <-- Added
        'photo_url',    // <-- Added
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // 'remember_token', // <-- Removed (column doesn't exist)
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', // <-- Removed (column doesn't exist)
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The care groups this user is a member of.
     */
    public function careGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            CareGroup::class,      // The model we are joining to
            'group_members',       // The name of our pivot table
            'user_id',             // The foreign key on the pivot table for *this* model
            'care_group_id'        // The foreign key on the pivot table for the *other* model
        )->using(GroupMember::class); // Tell Laravel to use our custom Pivot model
    }

    /**
     * The tasks that are assigned to this user.
     * This defines the many-to-many relationship via 'task_assignments'.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_assignments', // The pivot table
            'user_id',          // The foreign key for this model
            'task_id'           // The foreign key for the Task model
        );
    }

    /**
     * Get the notes authored by this user.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'author_id', 'user_id');
    }
}