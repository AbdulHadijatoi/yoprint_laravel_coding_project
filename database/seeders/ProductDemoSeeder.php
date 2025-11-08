<?php

namespace Database\Seeders;

use App\Models\File;
use App\Models\Product;
use App\Models\ProductUpload;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductDemoSeeder extends Seeder
{
    public function run(): void
    {
        $file = File::create([
            'disk' => 'public',
            'path' => 'seed/uploads/' . Str::uuid() . '.csv',
            'original_name' => 'demo-products.csv',
            'mime_type' => 'text/csv',
            'size' => 12_345,
            'checksum' => Str::random(64),
            'meta' => [
                'seeded' => true,
                'description' => 'Sample product catalog seeded for demos.',
            ],
        ]);

        $upload = ProductUpload::create([
            'file_id' => $file->id,
            'status' => ProductUpload::STATUS_COMPLETED,
            'total_rows' => 3,
            'processed_rows' => 3,
            'failed_rows' => 0,
        ]);

        Product::insert([
            [
                'product_upload_id' => $upload->id,
                'unique_key' => 'SKU-001',
                'product_title' => 'Performance Tee',
                'product_description' => 'Lightweight moisture-wicking tee ideal for training.',
                'style_number' => 'PT-100',
                'available_sizes' => 'S,M,L,XL',
                'price_text' => '$9.99',
                'piece_price' => 9.99,
                'color_name' => 'Charcoal',
                'sanmar_mainframe_color' => 'Grey',
                'raw_payload' => [
                    'category' => 'Apparel',
                    'inventory' => 120,
                ],
            ],
            [
                'product_upload_id' => $upload->id,
                'unique_key' => 'SKU-002',
                'product_title' => 'Heritage Hoodie',
                'product_description' => 'Soft fleece hoodie with vintage YoPrint branding.',
                'style_number' => 'HH-220',
                'available_sizes' => 'XS,S,M,L,XL,2XL',
                'price_text' => '$29.00',
                'piece_price' => 29.00,
                'color_name' => 'Navy',
                'sanmar_mainframe_color' => 'Blue',
                'raw_payload' => [
                    'category' => 'Outerwear',
                    'inventory' => 45,
                ],
            ],
            [
                'product_upload_id' => $upload->id,
                'unique_key' => 'SKU-003',
                'product_title' => 'Eco Water Bottle',
                'product_description' => 'Reusable bottle made from recycled materials.',
                'style_number' => 'WB-305',
                'available_sizes' => '20oz',
                'price_text' => '$14.95',
                'piece_price' => 14.95,
                'color_name' => 'Forest',
                'sanmar_mainframe_color' => 'Green',
                'raw_payload' => [
                    'category' => 'Accessories',
                    'inventory' => 200,
                ],
            ],
        ]);
    }
}

