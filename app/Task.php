<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'task_title', 'category', 'description', 'user_id'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }
}
