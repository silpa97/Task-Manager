<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'assigned_to', 'due_time', 'project_id','created_by'];
    public function project()
{
    return $this->belongsTo(Project::class);
}


}
