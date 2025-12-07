<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvitation extends Model
{
    use HasFactory;

    protected $primaryKey = 'invitation_id';

    protected $fillable = [
        'care_group_id',
        'code',
        'expires_at',
    ];

    public function careGroup()
    {
        return $this->belongsTo(CareGroup::class, 'care_group_id');
    }
}