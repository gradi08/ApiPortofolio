<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GraphicDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(GraphicDesignImage::class)->orderBy('order');
    }
}