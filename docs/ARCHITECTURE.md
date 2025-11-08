# YoPrint Laravel Architecture Notes

## Overview

The application ingests large CSV catalogs asynchronously so the web tier remains responsive while long-running imports execute in the background.

```
Client ──► HTTP API (`ProductUploadController`)
        └─► Validates file (`StoreProductUploadRequest`)
             └─► Persists metadata (`File`, `ProductUpload`)
                  └─► Queues `ProcessProductUpload`
                       └─► Streams CSV (`ProductCsvImporter`)
                            └─► Upserts `Product` rows
```

## Key Components

- **File storage** (`App\Models\File`)  
  Handles checksum generation, disk-aware storage, and cleanup so uploads never orphan files. The model appends `file_url` for quick API responses.

- **Upload tracking** (`App\Models\ProductUpload`)  
  Persists status, counters, and timestamps. Model appends `file_url` and triggers cascading file deletion.

- **Job orchestration** (`App\Jobs\ProcessProductUpload`)  
  Executes on the queue, updates progress metrics, and ensures transactional writes. Failures mark uploads as `failed` with descriptive messages.

- **Importer service** (`App\Services\ProductCsvImporter`)  
  Normalizes headers, handles deduplication by `unique_key`, and upserts products. Uses chunked reading to keep memory usage low.

- **HTTP resource** (`App\Http\Resources\ProductUploadResource`)  
  Shapes API responses, ensuring clients receive consistent payloads including file URL, status, and counters.

## Data Flow

1. CSV upload accepted (up to 50 MB) and stored on the configured disk.
2. `ProductUpload` created with `pending` status.
3. Queue job ingests CSV line-by-line, updating counters every chunk.
4. Products synchronized via `unique_key` to avoid duplicates.
5. Completion updates status, timestamps, and metrics; API/UI can poll index endpoint.

## Resilience

- **Idempotent jobs** thanks to unique keys and upserts.
- **Validation** rejects non-CSV input and oversized files before disk operations.
- **Storage cleanup** occurs automatically via Eloquent model events.
- **Test coverage** includes queue dispatch, importer correctness, validation boundaries, and sample fixtures.

## Observability Ideas

- Emit `UploadCompleted` and `UploadFailed` events for broadcasting.
- Store per-row failure reasons in a JSON column to surface partial success analytics.
- Attach Laravel Telescope or Horizon for queue monitoring during heavy loads.

## Scaling Considerations

- Move storage to S3-compatible service for multi-node deployments.
- Use Redis queues for higher throughput.
- Shard uploads by tenant/customer to isolate load and offer rate limiting.

---

_Last updated: 2025-11-08_

