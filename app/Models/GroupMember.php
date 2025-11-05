<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupMember extends Pivot
{
    // We extend Pivot because this is a many-to-many join table.

    /**
     * The table associated with the model.
     * We set this explicitly to match your migration.
     */
    protected $table = 'group_members';

    /**
     * Indicates if the model should be timestamped.
     * (Your migration doesn't have timestamps).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'care_group_id',
    ];

    /**
     * Get the user in this relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the care group in this relationship.
     */
    public function careGroup(): BelongsTo
    {
        return $this->belongsTo(CareGroup::class, 'care_group_id', 'care_group_id');
    }
}