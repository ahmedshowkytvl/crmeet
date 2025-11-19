<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\WarehouseCabinet;
use App\Models\WarehouseShelf;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssetLocationService
{
    /**
     * تخزين أصل في دولاب ورف محددين
     */
    public function storeAsset(Asset $asset, int $cabinetId, int $shelfId, int $userId, string $notes = null): bool
    {
        return DB::transaction(function () use ($asset, $cabinetId, $shelfId, $userId, $notes) {
            $cabinet = WarehouseCabinet::findOrFail($cabinetId);
            $shelf = WarehouseShelf::findOrFail($shelfId);

            // التحقق من توفر الرف
            if (!$shelf->isAvailable()) {
                throw new \Exception('الرف غير متاح أو ممتلئ');
            }

            // تحديث موقع الأصل
            $asset->update([
                'current_cabinet_id' => $cabinetId,
                'current_shelf_id' => $shelfId,
                'current_availability_status' => 'available',
                'current_location_description' => "دولاب {$cabinet->cabinet_number} - رف {$shelf->shelf_code}",
                'last_movement_at' => now(),
                'assigned_to' => null, // إزالة التعيين إذا كان موجود
            ]);

            // زيادة استخدام الرف
            $shelf->incrementUsage();

            // تسجيل الحركة
            $this->logAssetAction($asset, 'stored', $userId, $notes, [
                'cabinet_id' => $cabinetId,
                'shelf_id' => $shelfId,
                'cabinet_number' => $cabinet->cabinet_number,
                'shelf_code' => $shelf->shelf_code,
                'availability_status' => 'available'
            ]);

            return true;
        });
    }

    /**
     * تسليم أصل لموظف
     */
    public function checkOutAsset(Asset $asset, int $userId, int $assignedToUserId, string $notes = null): bool
    {
        return DB::transaction(function () use ($asset, $userId, $assignedToUserId, $notes) {
            $assignedUser = User::findOrFail($assignedToUserId);

            // تحديث حالة الأصل
            $asset->update([
                'current_availability_status' => 'checked_out',
                'current_location_description' => "مع {$assignedUser->name}",
                'last_movement_at' => now(),
                'assigned_to' => $assignedToUserId,
            ]);

            // تقليل استخدام الرف إذا كان في رف
            if ($asset->current_shelf_id) {
                $shelf = WarehouseShelf::find($asset->current_shelf_id);
                if ($shelf) {
                    $shelf->decrementUsage();
                }
            }

            // تسجيل الحركة
            $this->logAssetAction($asset, 'checked_out', $userId, $notes, [
                'assigned_to_user' => $assignedToUserId,
                'assigned_user_name' => $assignedUser->name,
                'availability_status' => 'checked_out'
            ]);

            return true;
        });
    }

    /**
     * إرجاع أصل للمخزن
     */
    public function returnAsset(Asset $asset, int $userId, int $cabinetId = null, int $shelfId = null, string $notes = null): bool
    {
        return DB::transaction(function () use ($asset, $userId, $cabinetId, $shelfId, $notes) {
            // إذا لم يتم تحديد موقع، استخدم الموقع السابق أو الافتراضي
            if (!$cabinetId || !$shelfId) {
                $cabinetId = $asset->current_cabinet_id;
                $shelfId = $asset->current_shelf_id;
            }

            if ($cabinetId && $shelfId) {
                $cabinet = WarehouseCabinet::findOrFail($cabinetId);
                $shelf = WarehouseShelf::findOrFail($shelfId);

                if (!$shelf->isAvailable()) {
                    throw new \Exception('الرف غير متاح أو ممتلئ');
                }

                // زيادة استخدام الرف
                $shelf->incrementUsage();

                $locationDescription = "دولاب {$cabinet->cabinet_number} - رف {$shelf->shelf_code}";
            } else {
                $locationDescription = 'مخزن - موقع غير محدد';
            }

            // تحديث حالة الأصل
            $asset->update([
                'current_cabinet_id' => $cabinetId,
                'current_shelf_id' => $shelfId,
                'current_availability_status' => 'available',
                'current_location_description' => $locationDescription,
                'last_movement_at' => now(),
                'assigned_to' => null,
            ]);

            // تسجيل الحركة
            $this->logAssetAction($asset, 'returned', $userId, $notes, [
                'cabinet_id' => $cabinetId,
                'shelf_id' => $shelfId,
                'availability_status' => 'available'
            ]);

            return true;
        });
    }

    /**
     * نقل أصل من موقع لآخر
     */
    public function moveAsset(Asset $asset, int $userId, int $newCabinetId, int $newShelfId, string $notes = null): bool
    {
        return DB::transaction(function () use ($asset, $userId, $newCabinetId, $newShelfId, $notes) {
            $oldCabinetId = $asset->current_cabinet_id;
            $oldShelfId = $asset->current_shelf_id;

            $newCabinet = WarehouseCabinet::findOrFail($newCabinetId);
            $newShelf = WarehouseShelf::findOrFail($newShelfId);

            if (!$newShelf->isAvailable()) {
                throw new \Exception('الرف الجديد غير متاح أو ممتلئ');
            }

            // تقليل استخدام الرف القديم
            if ($oldShelfId) {
                $oldShelf = WarehouseShelf::find($oldShelfId);
                if ($oldShelf) {
                    $oldShelf->decrementUsage();
                }
            }

            // زيادة استخدام الرف الجديد
            $newShelf->incrementUsage();

            // تحديث موقع الأصل
            $asset->update([
                'current_cabinet_id' => $newCabinetId,
                'current_shelf_id' => $newShelfId,
                'current_location_description' => "دولاب {$newCabinet->cabinet_number} - رف {$newShelf->shelf_code}",
                'last_movement_at' => now(),
            ]);

            // تسجيل الحركة
            $this->logAssetAction($asset, 'moved', $userId, $notes, [
                'old_cabinet_id' => $oldCabinetId,
                'old_shelf_id' => $oldShelfId,
                'new_cabinet_id' => $newCabinetId,
                'new_shelf_id' => $newShelfId,
                'old_location' => $oldCabinetId ? "دولاب {$oldCabinetId} - رف {$oldShelfId}" : 'غير محدد',
                'new_location' => "دولاب {$newCabinet->cabinet_number} - رف {$newShelf->shelf_code}",
                'availability_status' => $asset->current_availability_status
            ]);

            return true;
        });
    }

    /**
     * وضع أصل في الصيانة
     */
    public function setAssetMaintenance(Asset $asset, int $userId, string $notes = null): bool
    {
        return DB::transaction(function () use ($asset, $userId, $notes) {
            // تقليل استخدام الرف إذا كان في رف
            if ($asset->current_shelf_id) {
                $shelf = WarehouseShelf::find($asset->current_shelf_id);
                if ($shelf) {
                    $shelf->decrementUsage();
                }
            }

            // تحديث حالة الأصل
            $asset->update([
                'current_availability_status' => 'maintenance',
                'current_location_description' => 'في الصيانة',
                'last_movement_at' => now(),
                'assigned_to' => null,
            ]);

            // تسجيل الحركة
            $this->logAssetAction($asset, 'maintenance', $userId, $notes, [
                'availability_status' => 'maintenance'
            ]);

            return true;
        });
    }

    /**
     * تسجيل حركة الأصل
     */
    private function logAssetAction(Asset $asset, string $action, int $userId, string $notes = null, array $metadata = []): void
    {
        AssetLog::create([
            'asset_id' => $asset->id,
            'action' => $action,
            'user_id' => $userId,
            'date' => now()->toDateString(),
            'action_timestamp' => now(),
            'notes' => $notes,
            'metadata' => $metadata,
            'cabinet_id' => $asset->current_cabinet_id,
            'shelf_id' => $asset->current_shelf_id,
            'assigned_to_user' => $asset->assigned_to,
            'availability_status' => $asset->current_availability_status,
            'location_description' => $asset->current_location_description,
        ]);
    }

    /**
     * الحصول على آخر موقع للأصل
     */
    public function getLastLocation(Asset $asset): ?AssetLog
    {
        return $asset->logs()
            ->whereNotNull('cabinet_id')
            ->orderBy('action_timestamp', 'desc')
            ->first();
    }

    /**
     * الحصول على سجل الحركة الكامل للأصل
     */
    public function getAssetMovementHistory(Asset $asset): \Illuminate\Database\Eloquent\Collection
    {
        return $asset->logs()
            ->with(['cabinet', 'shelf', 'assignedToUser', 'user'])
            ->orderBy('action_timestamp', 'desc')
            ->get();
    }

    /**
     * الحصول على الأصول المتاحة في مخزن معين
     */
    public function getAvailableAssetsInWarehouse(int $warehouseId): \Illuminate\Database\Eloquent\Collection
    {
        return Asset::where('warehouse_id', $warehouseId)
            ->where('current_availability_status', 'available')
            ->with(['currentCabinet', 'currentShelf', 'category'])
            ->get();
    }

    /**
     * الحصول على الأصول في رف معين
     */
    public function getAssetsInShelf(int $shelfId): \Illuminate\Database\Eloquent\Collection
    {
        return Asset::where('current_shelf_id', $shelfId)
            ->where('current_availability_status', 'available')
            ->with(['category', 'assignedTo'])
            ->get();
    }
}
