<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workspace extends Model
{
    use HasFactory;

    // The table associated with the model (optional, if different from plural of the model name)
    protected $table = 'workspaces';

    // Fields that can be mass-assigned
    protected $fillable = [
        'nama_workspace',
        'tim',
        'urutan_status_workspace',
        'titk_koordinat',
        'pohon',
        'shannon_wanner',
    ];

    // Fields that should be cast to specific types
    protected $casts = [
        // 'status' => 'array', // Automatically cast 'status' to an array (useful for working with JSON objects)
        'pohon' => 'array',
        'shannon_wanner' => 'array',
    ];

    public function statusWorkspace()
    {
        return $this->belongsTo(StatusWorkspace::class, 'urutan_status_workspace', 'urutan');
    }

    protected $with = ['statusWorkspace'];
}
