<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_is_required(): void
    {
        $response = $this->postJson(route('product-uploads.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_large_files_up_to_fifty_megabytes_are_accepted(): void
    {
        Storage::fake('public');
        config(['filesystems.default' => 'public']);

        $file = UploadedFile::fake()->create('large.csv', 50 * 1024, 'text/csv');

        $response = $this->postJson(route('product-uploads.store'), [
            'file' => $file,
        ]);

        $response->assertCreated();
    }

    public function test_files_over_the_limit_are_rejected(): void
    {
        $file = UploadedFile::fake()->create('huge.csv', (50 * 1024) + 1, 'text/csv');

        $response = $this->postJson(route('product-uploads.store'), [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}

