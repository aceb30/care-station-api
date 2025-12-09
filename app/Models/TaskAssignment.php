<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskAssignment extends Pivot
{
    protected $table = 'task_assignments';

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = [
        'task_id',
        'user_id',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
