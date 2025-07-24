<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'assigned_to', 'end_date', 'created_by'];

    //
    public function teamLead()
{
    return $this->belongsTo(User::class, 'assigned_to');
}

public function projectManager()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function tasks()
{
    return $this->hasMany(Task::class);
}

}
