<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\AssetAssignment;
use App\Services\BarcodeService;
use Illuminate\Support\Facades\DB;

class AssetService
{
    protected $barcodeService;

    public function __construct(BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    /**
     * Create new asset
     */
    public function createAsset(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            // Generate unique codes
            $assetCode = $this->generateAssetCode();
            $barcode = $this->barcodeService->generateUniqueCode();

            // Create asset
            $asset = Asset::create([
                'asset_code' => $assetCode,
                'barcode' => $barcode,
                'name' => $data['name'],
                'name_ar' => $data['name_ar'] ?? null,
                'category_id' => $data['category_id'],
                'serial_number' => $data['serial_number'] ?? null,
                'description' => $data['description'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'purchase_date' => $data['purchase_date'] ?? null,
                'warranty_expiry' => $data['warranty_expiry'] ?? null,
                'cost' => $data['cost'] ?? null,
                'status' => $data['status'] ?? 'active',
                'location_id' => $data['location_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'inventory_status' => $data['inventory_status'] ?? 'in_stock',
                'quantity' => $data['quantity'] ?? 1,
                'store_code' => $data['store_code'] ?? null,
                'price' => $data['price'] ?? null,
                'currency' => $data['currency'] ?? 'EGP',
            ]);

            // Generate barcode image
            $barcodePath = $this->barcodeService->generateBarcodeImage($barcode, $asset->id . '.png');
            $asset->update(['barcode_image' => $barcodePath]);

            // Save custom properties
            if (isset($data['properties'])) {
                $this->saveAssetProperties($asset, $data['properties']);
            }

            // Log creation
            $this->logAssetAction($asset, 'created', auth()->id(), $data['notes'] ?? null);

            return $asset;
        });
    }

    /**
     * Update asset
     */
    public function updateAsset(Asset $asset, array $data): Asset
    {
        return DB::transaction(function () use ($asset, $data) {
            $oldData = $asset->toArray();
            
            $asset->update([
                'name' => $data['name'],
                'name_ar' => $data['name_ar'] ?? $asset->name_ar,
                'category_id' => $data['category_id'],
                'serial_number' => $data['serial_number'] ?? $asset->serial_number,
                'description' => $data['description'] ?? $asset->description,
                'description_ar' => $data['description_ar'] ?? $asset->description_ar,
                'purchase_date' => $data['purchase_date'] ?? $asset->purchase_date,
                'warranty_expiry' => $data['warranty_expiry'] ?? $asset->warranty_expiry,
                'cost' => $data['cost'] ?? $asset->cost,
                'status' => $data['status'] ?? $asset->status,
                'location_id' => $data['location_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? $asset->assigned_to,
                'warehouse_id' => $data['warehouse_id'] ?? $asset->warehouse_id,
                'inventory_status' => $data['inventory_status'] ?? $asset->inventory_status,
                'quantity' => $data['quantity'] ?? $asset->quantity,
                'store_code' => $data['store_code'] ?? $asset->store_code,
                'price' => $data['price'] ?? $asset->price,
                'currency' => $data['currency'] ?? $asset->currency,
            ]);

            // Update custom properties
            if (isset($data['properties'])) {
                $this->saveAssetProperties($asset, $data['properties']);
            }

            // Log update
            $this->logAssetAction($asset, 'updated', auth()->id(), $data['notes'] ?? null, [
                'old_data' => $oldData,
                'new_data' => $asset->toArray()
            ]);

            return $asset;
        });
    }

    /**
     * Assign asset to user
     */
    public function assignAsset(Asset $asset, int $userId, string $notes = null): AssetAssignment
    {
        return DB::transaction(function () use ($asset, $userId, $notes) {
            // Return current assignment if exists
            $currentAssignment = $asset->assignments()->active()->first();
            if ($currentAssignment) {
                $currentAssignment->return($notes);
            }

            // Create new assignment
            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id,
                'user_id' => $userId,
                'assigned_date' => now(),
                'notes' => $notes,
                'assigned_by' => auth()->id(),
            ]);

            // Update asset
            $asset->update(['assigned_to' => $userId]);

            // Log assignment
            $this->logAssetAction($asset, 'assigned', auth()->id(), $notes, [
                'assigned_to' => $userId,
                'assignment_id' => $assignment->id
            ]);

            return $assignment;
        });
    }

    /**
     * Return asset from user
     */
    public function returnAsset(Asset $asset, string $notes = null): bool
    {
        return DB::transaction(function () use ($asset, $notes) {
            $assignment = $asset->assignments()->active()->first();
            if (!$assignment) {
                return false;
            }

            $assignment->return($notes);
            $asset->update(['assigned_to' => null]);

            // Log return
            $this->logAssetAction($asset, 'returned', auth()->id(), $notes, [
                'returned_from' => $assignment->user_id,
                'assignment_id' => $assignment->id
            ]);

            return true;
        });
    }

    /**
     * Move asset to new location
     */
    public function moveAsset(Asset $asset, int $locationId, string $notes = null): bool
    {
        return DB::transaction(function () use ($asset, $locationId, $notes) {
            $oldLocationId = $asset->location_id;
            $asset->update(['location_id' => $locationId]);

            // Log move
            $this->logAssetAction($asset, 'moved', auth()->id(), $notes, [
                'from_location' => $oldLocationId,
                'to_location' => $locationId
            ]);

            return true;
        });
    }

    /**
     * Generate asset code
     */
    protected function generateAssetCode(): string
    {
        $prefix = 'AST';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}-{$year}{$month}-{$random}";
    }

    /**
     * Save asset properties
     */
    protected function saveAssetProperties(Asset $asset, array $properties): void
    {
        foreach ($properties as $propertyId => $value) {
            $asset->setPropertyValue($propertyId, $value);
        }
    }

    /**
     * Log asset action
     */
    protected function logAssetAction(Asset $asset, string $action, int $userId, string $notes = null, array $metadata = []): void
    {
        AssetLog::create([
            'asset_id' => $asset->id,
            'action' => $action,
            'user_id' => $userId,
            'date' => now(),
            'notes' => $notes,
            'metadata' => $metadata
        ]);
    }
}

