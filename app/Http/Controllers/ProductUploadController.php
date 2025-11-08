<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductUploadRequest;
use App\Http\Resources\ProductUploadResource;
use App\Jobs\ProcessProductUpload;
use App\Models\File;
use App\Models\ProductUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductUploadController extends Controller
{
    public function index(): JsonResponse
    {
        $uploads = ProductUpload::with('file')
            ->latest()
            ->get();

        return ProductUploadResource::collection($uploads)->response();
    }

    public function store(StoreProductUploadRequest $request)
    {
        $file = File::saveFile($request->file('file'));

        $upload = ProductUpload::create([
            'file_id' => $file->id,
            'status' => ProductUpload::STATUS_PENDING,
        ])->fresh('file');

        ProcessProductUpload::dispatch($upload);

        if ($request->wantsJson()) {
            return (new ProductUploadResource($upload))
                ->response()
                ->setStatusCode(201);
        }

        return redirect()
            ->back()
            ->with('status', 'File uploaded successfully. Processing has started.');
    }
}
