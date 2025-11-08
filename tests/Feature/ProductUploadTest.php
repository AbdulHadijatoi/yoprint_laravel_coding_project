<?php

namespace Tests\Feature;

use App\Jobs\ProcessProductUpload;
use App\Models\Product;
use App\Models\ProductUpload;
use App\Services\ProductCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_upload_and_dispatches_job(): void
    {
        Queue::fake();
        Storage::fake('public');
        config(['filesystems.default' => 'public']);

        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            file_get_contents(base_path('tests/Fixtures/products.csv'))
        );

        $response = $this->post(route('product-uploads.store'), [
            'file' => $file,
        ], ['HTTP_ACCEPT' => 'application/json']);

        $response->assertCreated();

        $this->assertDatabaseCount('product_uploads', 1);
        $upload = ProductUpload::first();

        $this->assertNotNull($upload);
        $this->assertEquals(ProductUpload::STATUS_PENDING, $upload->status);

        Queue::assertPushed(ProcessProductUpload::class, function ($job) use ($upload) {
            return $job->upload->is($upload);
        });
    }

    public function test_it_processes_csv_and_upserts_products(): void
    {
        Queue::fake();
        Storage::fake('public');
        config(['filesystems.default' => 'public']);

        $fileContent = file_get_contents(base_path('tests/Fixtures/products.csv'));

        $this->post(route('product-uploads.store'), [
            'file' => UploadedFile::fake()->createWithContent('products.csv', $fileContent),
        ], ['HTTP_ACCEPT' => 'application/json']);

        $upload = ProductUpload::first();

        $this->assertNotNull($upload);
        $this->assertEquals(ProductUpload::STATUS_PENDING, $upload->status);

        // Run the job synchronously
        (new ProcessProductUpload($upload))->handle(app(ProductCsvImporter::class));

        $upload->refresh();
        $this->assertEquals(ProductUpload::STATUS_COMPLETED, $upload->status);
        $this->assertEquals(3, $upload->total_rows);
        $this->assertEquals(3, $upload->processed_rows);
        $this->assertEquals(0, $upload->failed_rows);

        $this->assertDatabaseCount('products', 2);

        $product = Product::where('unique_key', 'ABC123')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Test Product Updated', $product->product_title);
        $this->assertEquals(10.99, (float) $product->piece_price);
    }
}
