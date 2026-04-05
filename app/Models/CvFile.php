<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvFile extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'file_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope pour récupérer le CV actif
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->latest()->first();
    }
}