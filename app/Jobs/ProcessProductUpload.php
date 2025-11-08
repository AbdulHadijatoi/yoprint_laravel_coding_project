<?php

namespace App\Jobs;

use App\Models\ProductUpload;
use App\Services\ProductCsvImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessProductUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ProductUpload $upload;

    public function __construct(ProductUpload $upload)
    {
        $this->upload = $upload;
    }

    public function handle(ProductCsvImporter $importer): void
    {
        $upload = $this->upload->fresh(['file']);

        if (! $upload) {
            return;
        }

        $upload->update([
            'status' => ProductUpload::STATUS_PROCESSING,
            'started_at' => Carbon::now(),
            'error_message' => null,
        ]);

        try {
            $result = $importer->import($upload);

            $upload->update([
                'status' => ProductUpload::STATUS_COMPLETED,
                'finished_at' => Carbon::now(),
                'total_rows' => $result['total'],
                'processed_rows' => $result['processed'],
                'failed_rows' => $result['failed'],
            ]);
        } catch (\Throwable $exception) {
            $upload->update([
                'status' => ProductUpload::STATUS_FAILED,
                'finished_at' => Carbon::now(),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
