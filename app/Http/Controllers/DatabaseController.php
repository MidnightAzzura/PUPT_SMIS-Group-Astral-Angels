<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DatabaseController extends Controller
{
    /**
     * Backup the entire database to SQL file
     */
    public function backup(Request $request)
    {
        try {
            // Ensure only SuperAdmin can access this
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'You must be logged in.');
            }

            $userRole = strtolower(Auth::user()->role);
            if ($userRole !== 'superadmin') {
                return redirect()->back()->with('error', 'Unauthorized access. Only SuperAdmin can backup database.');
            }

            $dbHost = config('database.connections.mysql.host');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbPort = config('database.connections.mysql.port', 3306);

            // Create backup filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupFileName = "database_backup_{$timestamp}.sql";
            $backupPath = storage_path("app/backups/{$backupFileName}");

            // Create backups directory if it doesn't exist
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            // Use mysqldump to create backup
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($backupPath)
            );

            // Execute the command
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($backupPath)) {
                // Log the backup activity
                Log::info("Database backup created by user: " . Auth::user()->email, [
                    'backup_file' => $backupFileName,
                    'file_size' => filesize($backupPath),
                    'timestamp' => now()
                ]);

                // Return the file for download
                return response()->download($backupPath, $backupFileName)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('Failed to create database backup. Return code: ' . $returnVar);
            }

        } catch (\Exception $e) {
            Log::error('Database backup failed: ' . $e->getMessage(), [
                'user' => Auth::user()->email,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('error', 'Database backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from uploaded SQL file
     */
    public function restore(Request $request)
    {
        try {
            // Ensure only SuperAdmin can access this
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'You must be logged in.');
            }

            $userRole = strtolower(Auth::user()->role);
            if ($userRole !== 'superadmin') {
                return redirect()->back()->with('error', 'Unauthorized access. Only SuperAdmin can restore database.');
            }

            // Validate the uploaded file
            $request->validate([
                'sql_file' => 'required|file|mimes:sql,txt|max:102400' // 100MB max
            ]);

            $dbHost = config('database.connections.mysql.host');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbPort = config('database.connections.mysql.port', 3306);

            // Get the uploaded file
            $uploadedFile = $request->file('sql_file');
            $originalName = $uploadedFile->getClientOriginalName();
            
            // Store the uploaded file temporarily
            $tempPath = $uploadedFile->store('temp_restore', 'local');
            $fullTempPath = storage_path("app/{$tempPath}");

            // Validate SQL file content (basic check)
            $fileContent = file_get_contents($fullTempPath);
            if (strpos($fileContent, 'DROP TABLE') === false && strpos($fileContent, 'CREATE TABLE') === false) {
                unlink($fullTempPath);
                return redirect()->back()->with('error', 'Invalid SQL file. The file does not appear to contain database structure.');
            }

            // Create a backup before restoration (safety measure)
            $preRestoreBackup = "pre_restore_backup_" . now()->format('Y-m-d_H-i-s') . ".sql";
            $preRestoreBackupPath = storage_path("app/backups/{$preRestoreBackup}");
            
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            $backupCommand = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($preRestoreBackupPath)
            );

            exec($backupCommand, $backupOutput, $backupReturnVar);

            // Execute the restore command with transaction safety
            $restoreCommand = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s --init-command="SET SESSION FOREIGN_KEY_CHECKS=0;" --execute="SET autocommit=0; START TRANSACTION; SOURCE %s; COMMIT;"',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($fullTempPath)
            );

            $output = [];
            $returnVar = 0;
            exec($restoreCommand, $output, $returnVar);

            // Clean up temporary file
            unlink($fullTempPath);

            if ($returnVar === 0) {
                // Log the restore activity
                Log::info("Database restored by user: " . Auth::user()->email, [
                    'original_file' => $originalName,
                    'pre_restore_backup' => $preRestoreBackup,
                    'timestamp' => now()
                ]);

                return redirect()->back()->with('success', 
                    'Database successfully restored from ' . $originalName . '. ' .
                    'A backup of the previous database was created as: ' . $preRestoreBackup
                );
            } else {
                throw new \Exception('Failed to restore database. Return code: ' . $returnVar . '. Output: ' . implode('\n', $output));
            }

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($fullTempPath) && file_exists($fullTempPath)) {
                unlink($fullTempPath);
            }

            Log::error('Database restore failed: ' . $e->getMessage(), [
                'user' => Auth::user()->email,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('error', 'Database restore failed: ' . $e->getMessage());
        }
    }
}
