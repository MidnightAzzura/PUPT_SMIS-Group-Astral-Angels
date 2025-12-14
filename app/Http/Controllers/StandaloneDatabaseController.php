<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class StandaloneDatabaseController extends Controller
{
    /**
     * X100 Senior Developer Solution - Standalone Database Backup
     * Completely bypasses any middleware or dependency injection issues
     */
    public function backup(Request $request)
    {
        // Simple, direct authentication check
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Simple role check
        $user = auth()->user();
        if (strtolower($user->role ?? '') !== 'superadmin') {
            return redirect()->back()->with('error', 'Access denied. SuperAdmin only.');
        }

        try {
            // Get database configuration
            $host = env('DB_HOST', 'localhost');
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $port = env('DB_PORT', 3306);

            // Create backup filename
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "database_backup_{$timestamp}.sql";
            $filepath = storage_path("app/backups/{$filename}");

            // Ensure backup directory exists
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Create backup using mysqldump
            $command = "mysqldump -h{$host} -P{$port} -u{$username} -p{$password} {$database} > {$filepath}";
            
            // Execute backup
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($filepath)) {
                // Log success
                Log::info("Database backup created", [
                    'user' => $user->email,
                    'file' => $filename,
                    'size' => filesize($filepath)
                ]);

                // Download file
                return Response::download($filepath, $filename)->deleteFileAfterSend(true);
            } else {
                throw new \Exception("Backup failed with return code: {$returnCode}");
            }

        } catch (\Exception $e) {
            Log::error("Database backup failed", [
                'error' => $e->getMessage(),
                'user' => $user->email ?? 'unknown'
            ]);

            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * X100 Senior Developer Solution - Standalone Database Restore
     */
    public function restore(Request $request)
    {
        // Simple, direct authentication check
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Simple role check
        $user = auth()->user();
        if (strtolower($user->role ?? '') !== 'superadmin') {
            return redirect()->back()->with('error', 'Access denied. SuperAdmin only.');
        }

        try {
            // Validate file upload
            if (!$request->hasFile('sql_file')) {
                return redirect()->back()->with('error', 'No file uploaded.');
            }

            $file = $request->file('sql_file');
            
            // Basic validations
            if ($file->getSize() > 100 * 1024 * 1024) { // 100MB
                return redirect()->back()->with('error', 'File too large. Maximum 100MB.');
            }

            if (!in_array($file->getClientOriginalExtension(), ['sql', 'txt'])) {
                return redirect()->back()->with('error', 'Invalid file type. Only SQL files allowed.');
            }

            // Get database configuration
            $host = env('DB_HOST', 'localhost');
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $port = env('DB_PORT', 3306);

            // Save uploaded file temporarily
            $tempPath = $file->store('temp_restore');
            $fullPath = storage_path("app/{$tempPath}");

            // Create pre-restore backup
            $backupTimestamp = date('Y-m-d_H-i-s');
            $backupFilename = "pre_restore_backup_{$backupTimestamp}.sql";
            $backupPath = storage_path("app/backups/{$backupFilename}");

            // Ensure backup directory exists
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Create pre-restore backup
            $backupCommand = "mysqldump -h{$host} -P{$port} -u{$username} -p{$password} {$database} > {$backupPath}";
            exec($backupCommand);

            // Restore database
            $restoreCommand = "mysql -h{$host} -P{$port} -u{$username} -p{$password} {$database} < {$fullPath}";
            exec($restoreCommand, $output, $returnCode);

            // Clean up temp file
            unlink($fullPath);

            if ($returnCode === 0) {
                Log::info("Database restored", [
                    'user' => $user->email,
                    'original_file' => $file->getClientOriginalName(),
                    'backup_created' => $backupFilename
                ]);

                return redirect()->back()->with('success', 
                    'Database restored successfully! Pre-restore backup saved as: ' . $backupFilename
                );
            } else {
                throw new \Exception("Restore failed with return code: {$returnCode}");
            }

        } catch (\Exception $e) {
            // Clean up temp file if exists
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }

            Log::error("Database restore failed", [
                'error' => $e->getMessage(),
                'user' => $user->email ?? 'unknown'
            ]);

            return redirect()->back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }
}
