<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusWorkspace extends Model
{
    use HasFactory;

    protected $table = 'status_workspaces';
    protected $hidden = ['id'];
}
