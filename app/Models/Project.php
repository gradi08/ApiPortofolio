<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'technologies',
        'project_url',
        'github_url',
    ];

    protected $casts = [
        'technologies' => 'array',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('is_approved', true);
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class); // pour l'admin (tous les commentaires)
    }
}