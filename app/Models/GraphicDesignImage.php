<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraphicDesignImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'graphic_design_id',
        'image_path',
        'order',
    ];

    public function graphicDesign(): BelongsTo
    {
        return $this->belongsTo(GraphicDesign::class);
    }
}