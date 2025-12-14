<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;

class SupplyAuditService
{
    protected static function storagePath()
    {
        return 'supply_history.json';
    }

    protected static function readAll()
    {
        try {
            if (!Storage::exists(self::storagePath())) return [];
            $raw = Storage::get(self::storagePath());
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            return [];
        }
    }

    protected static function writeAll(array $data)
    {
        try {
            Storage::put(self::storagePath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected static function ensureStockBucket(array &$all, $stock)
    {
        if (!isset($all[$stock]) || !is_array($all[$stock])) {
            $all[$stock] = ['updates' => [], 'requests' => []];
        } else {
            if (!isset($all[$stock]['updates'])) $all[$stock]['updates'] = [];
            if (!isset($all[$stock]['requests'])) $all[$stock]['requests'] = [];
        }
    }

    public static function logSupplyCreated($supply)
    {
        try {
            $all = self::readAll();
            $stock = (string) ($supply->StockNumber ?? $supply['StockNumber'] ?? '');
            if (!$stock) return false;
            self::ensureStockBucket($all, $stock);

            $entry = [
                'type' => 'created',
                'timestamp' => now()->toDateTimeString(),
                'user' => optional(Auth::user())->email ?? optional(Auth::user())->name ?? 'System',
                'item' => $supply->ItemDescription ?? ($supply['ItemDescription'] ?? null),
                'previous_quantity' => null,
                'new_quantity' => intval($supply->Quantity ?? 0),
                'note' => 'Supply created'
            ];

            array_unshift($all[$stock]['updates'], $entry);
            self::writeAll($all);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function logSupplyUpdated($supply, $originalData = [])
    {
        try {
            $all = self::readAll();
            $stock = (string) ($supply->StockNumber ?? $supply['StockNumber'] ?? '');
            if (!$stock) return false;
            self::ensureStockBucket($all, $stock);

            $prevQty = isset($originalData['Quantity']) ? intval($originalData['Quantity']) : null;
            $newQty  = intval($supply->Quantity ?? ($originalData['Quantity'] ?? 0));
            $user = optional(Auth::user())->email ?? optional(Auth::user())->name ?? 'System';

            $entry = [
                'type' => 'updated',
                'timestamp' => now()->toDateTimeString(),
                'user' => $user,
                'previous_quantity' => $prevQty,
                'new_quantity' => $newQty,
                'note' => 'Supply updated',
                'delta' => is_null($prevQty) ? null : ($newQty - $prevQty)
            ];

            array_unshift($all[$stock]['updates'], $entry);
            self::writeAll($all);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function logSupplyDeleted($supply)
    {
        try {
            $all = self::readAll();
            $stock = (string) ($supply->StockNumber ?? $supply['StockNumber'] ?? '');
            if (!$stock) return false;
            self::ensureStockBucket($all, $stock);

            $entry = [
                'type' => 'deleted',
                'timestamp' => now()->toDateTimeString(),
                'user' => optional(Auth::user())->email ?? optional(Auth::user())->name ?? 'System',
                'previous_quantity' => intval($supply->Quantity ?? 0),
                'new_quantity' => null,
                'note' => 'Supply deleted'
            ];

            array_unshift($all[$stock]['updates'], $entry);
            self::writeAll($all);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function logRequestApproval($stock, array $requestData)
    {
        try {
            $all = self::readAll();
            $stock = (string) $stock;
            if (!$stock) return false;
            self::ensureStockBucket($all, $stock);

            $entry = array_merge([
                'id' => $requestData['id'] ?? null,
                'requester' => $requestData['requester'] ?? ($requestData['requested_by'] ?? 'Unknown'),
                'requested_quantity' => isset($requestData['requested_quantity']) ? intval($requestData['requested_quantity']) : null,
                'approved_quantity' => isset($requestData['approved_quantity']) ? intval($requestData['approved_quantity']) : null,
                'approved_by' => $requestData['approved_by'] ?? ($requestData['approver'] ?? null),
                'status' => $requestData['status'] ?? 'approved',
                'date' => $requestData['date'] ?? now()->toDateTimeString(),
                'note' => $requestData['note'] ?? null
            ], $requestData);

            array_unshift($all[$stock]['requests'], $entry);
            self::writeAll($all);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getHistory($stock)
    {
        try {
            $all = self::readAll();
            $stock = (string) $stock;
            if (!isset($all[$stock])) {
                return ['updates' => [], 'requests' => []];
            }
            return [
                'updates' => array_values($all[$stock]['updates'] ?? []),
                'requests' => array_values($all[$stock]['requests'] ?? [])
            ];
        } catch (Exception $e) {
            return ['updates' => [], 'requests' => []];
        }
    }
}
