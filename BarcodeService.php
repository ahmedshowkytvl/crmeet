<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BarcodeService
{
    protected $generator;

    public function __construct()
    {
        $this->generator = new BarcodeGeneratorPNG();
    }

    /**
     * Generate barcode image and save to storage
     */
    public function generateBarcodeImage(string $code, string $filename = null): string
    {
        $filename = $filename ?: $code . '.png';
        $path = 'barcodes/' . $filename;

        // Generate barcode
        $barcode = $this->generator->getBarcode($code, BarcodeGeneratorPNG::TYPE_CODE_128);

        // Save to storage
        Storage::disk('public')->put($path, $barcode);

        return $path;
    }

    /**
     * Generate barcode HTML for display
     */
    public function generateBarcodeHTML(string $code): string
    {
        $generator = new BarcodeGeneratorHTML();
        return $generator->getBarcode($code, BarcodeGeneratorHTML::TYPE_CODE_128);
    }

    /**
     * Generate unique barcode code
     */
    public function generateUniqueCode(): string
    {
        do {
            $code = 'AST' . date('Ymd') . Str::random(6);
        } while (\App\Models\Asset::where('barcode', $code)->exists());

        return $code;
    }

    /**
     * Delete barcode image
     */
    public function deleteBarcodeImage(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    /**
     * Get barcode image URL
     */
    public function getBarcodeImageUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}

