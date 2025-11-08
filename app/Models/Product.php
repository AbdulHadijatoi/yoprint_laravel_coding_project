<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_upload_id',
        'unique_key',
        'product_title',
        'product_description',
        'style_number',
        'available_sizes',
        'brand_logo_image',
        'thumbnail_image',
        'color_swatch_image',
        'product_image',
        'spec_sheet',
        'price_text',
        'size',
        'color_name',
        'sanmar_mainframe_color',
        'piece_price',
        'dozens_price',
        'case_price',
        'raw_payload',
    ];

    protected $casts = [
        'piece_price' => 'decimal:2',
        'dozens_price' => 'decimal:2',
        'case_price' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function upload()
    {
        return $this->belongsTo(ProductUpload::class, 'product_upload_id');
    }
}
