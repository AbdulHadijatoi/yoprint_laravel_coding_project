<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductCsvImporter
{
    protected const COLUMN_MAP = [
        'UNIQUE_KEY' => 'unique_key',
        'PRODUCT_TITLE' => 'product_title',
        'PRODUCT_DESCRIPTION' => 'product_description',
        'STYLE#' => 'style_number',
        'AVAILABLE_SIZES' => 'available_sizes',
        'BRAND_LOGO_IMAGE' => 'brand_logo_image',
        'THUMBNAIL_IMAGE' => 'thumbnail_image',
        'COLOR_SWATCH_IMAGE' => 'color_swatch_image',
        'PRODUCT_IMAGE' => 'product_image',
        'SPEC_SHEET' => 'spec_sheet',
        'PRICE_TEXT' => 'price_text',
        'SIZE' => 'size',
        'COLOR_NAME' => 'color_name',
        'SANMAR_MAINFRAME_COLOR' => 'sanmar_mainframe_color',
        'PIECE_PRICE' => 'piece_price',
        'DOZENS_PRICE' => 'dozens_price',
        'CASE_PRICE' => 'case_price',
    ];

    public function import(ProductUpload $upload): array
    {
        $file = $upload->file;

        if (! $file) {
            throw new RuntimeException('Upload does not have an associated file.');
        }

        $stream = Storage::disk($file->disk)->readStream($file->path);

        if ($stream === false) {
            throw new RuntimeException('Unable to read uploaded file.');
        }

        $firstLine = fgets($stream);

        if ($firstLine === false) {
            fclose($stream);

            return ['total' => 0, 'processed' => 0, 'failed' => 0];
        }

        $delimiter = $this->determineDelimiter($firstLine);
        $headers = $this->normaliseHeaders(str_getcsv($this->stripBom($firstLine), $delimiter));

        $total = 0;
        $processed = 0;
        $failed = 0;

        while (($data = fgetcsv($stream, 0, $delimiter)) !== false) {
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $total++;
            $row = $this->combineRow($headers, $data);

            if (empty($row['UNIQUE_KEY'])) {
                $failed++;
                continue;
            }

            $payload = $this->mapRowToProductAttributes($row);

            try {
                Product::updateOrCreate(
                    ['unique_key' => $payload['unique_key']],
                    array_merge($payload, [
                        'product_upload_id' => $upload->id,
                        'raw_payload' => $row,
                    ])
                );
                $processed++;
            } catch (\Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        fclose($stream);

        return [
            'total' => $total,
            'processed' => $processed,
            'failed' => $failed,
        ];
    }

    protected function determineDelimiter(string $line): string
    {
        if (Str::contains($line, "\t")) {
            return "\t";
        }

        return Str::contains($line, ';') && substr_count($line, ';') > substr_count($line, ',')
            ? ';'
            : ',';
    }

    protected function normaliseHeaders(array $headers): array
    {
        return array_map(function ($header) {
            return strtoupper($this->cleanValue($header) ?? '');
        }, $headers);
    }

    protected function combineRow(array $headers, array $values): array
    {
        $values = array_pad($values, count($headers), null);
        $row = array_combine($headers, $values) ?: [];

        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = $this->cleanValue($value);
            }
        }

        return $row;
    }

    protected function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function mapRowToProductAttributes(array $row): array
    {
        $attributes = [];

        foreach (self::COLUMN_MAP as $csvKey => $attribute) {
            $value = Arr::get($row, $csvKey);
            $value = is_string($value) ? trim($value) : $value;
            if ($value === '') {
                $value = null;
            }

            if (in_array($attribute, ['piece_price', 'dozens_price', 'case_price'], true) && $value !== null) {
                $value = $this->parseDecimal($value);
            }

            $attributes[$attribute] = $value;
        }

        return $attributes;
    }

    protected function parseDecimal($value): ?float
    {
        $normalised = str_replace([',', '$'], ['', ''], (string) $value);
        return is_numeric($normalised) ? (float) $normalised : null;
    }

    protected function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (! mb_detect_encoding($value, 'UTF-8', true)) {
            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E\x{80}-\x{10FFFF}]/u', '', $value);
    }

    protected function stripBom(string $value): string
    {
        if (substr($value, 0, 3) === "\xEF\xBB\xBF") {
            return substr($value, 3);
        }

        return $value;
    }
}

