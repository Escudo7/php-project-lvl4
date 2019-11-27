<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id',
    ];
    
    public function tags()
    {
        return $this->belongsToMany('App\Tag');
    }

    public function creator()
    {
        return $this->belongsTo('App\User');
    }

    public function assignedTo()
    {
        return $this->belongsTo('App\User', 'assignedTo_id');
    }

    public function status()
    {
        return $this->belongsTo('App\TaskStatus');
    }
}