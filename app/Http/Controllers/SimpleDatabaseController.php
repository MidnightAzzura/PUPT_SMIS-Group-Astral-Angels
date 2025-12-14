<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Ultra-Simple Database Controller - X100 Senior Developer Bulletproof Solution
 * Zero dependencies, maximum compatibility
 */
class SimpleDatabaseController extends Controller
{
    public function backup(Request $request)
    {
        // Ultimate simple authentication
        if (!session('user_id') && !auth()->check()) {
            return response('Unauthorized', 401);
        }

        $user = auth()->user();
        
        // Simple role check - case insensitive
        if (!$user || strtolower($user->role ?? '') !== 'superadmin') {
            return response('Access Denied - SuperAdmin Only', 403);
        }

        try {
            // Database credentials from Laravel config (X100 Senior Dev Method)
            $host = config('database.connections.mysql.host', '127.0.0.1');
            $database = config('database.connections.mysql.database', '');
            $username = config('database.connections.mysql.username', '');
            $password = config('database.connections.mysql.password', '');
            $port = config('database.connections.mysql.port', 3306);

            if (empty($database)) {
                throw new \Exception('Database name not configured');
            }

            // Create backup file
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "db_backup_{$timestamp}.sql";
            
            // Ensure backup directory exists
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $filepath = $backupDir . '/' . $filename;

            // Execute mysqldump - X100 Senior Dev with fallback
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database)
            );

            // Try mysqldump first
            $output = shell_exec($command . ' 2>&1');
            
            if ($output && !str_contains($output, 'command not found') && !str_contains($output, 'not recognized')) {
                // mysqldump worked
                file_put_contents($filepath, $output);
                $success = true;
            } else {
                // Fallback to PHP PDO method
                $success = $this->backupUsingPDO($host, $port, $database, $username, $password, $filepath);
            }

            if ($success && file_exists($filepath) && filesize($filepath) > 0) {
                // Log success
                Log::info("Database backup created: {$filename} by user: {$user->email}");

                // Send file download
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                
                readfile($filepath);
                unlink($filepath); // Clean up
                exit;
            } else {
                throw new \Exception('Backup failed. Output: ' . ($output ?: 'No output received'));
            }

        } catch (\Exception $e) {
            Log::error("Backup failed: " . $e->getMessage());
            return response('Backup failed: ' . $e->getMessage(), 500);
        }
    }

    public function restore(Request $request)
    {
        // Ultimate simple authentication
        if (!session('user_id') && !auth()->check()) {
            return response('Unauthorized', 401);
        }

        $user = auth()->user();
        
        // Simple role check
        if (!$user || strtolower($user->role ?? '') !== 'superadmin') {
            return response('Access Denied - SuperAdmin Only', 403);
        }

        try {
            // Check file upload
            if (!$request->hasFile('sql_file')) {
                throw new \Exception('No file uploaded');
            }

            $file = $request->file('sql_file');
            
            // Basic validation
            if ($file->getSize() > 100 * 1024 * 1024) {
                throw new \Exception('File too large (max 100MB)');
            }

            $allowedExtensions = ['sql', 'txt'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('Invalid file type. Only SQL files allowed.');
            }

            // Database credentials from Laravel config (X100 Senior Dev Method)
            $host = config('database.connections.mysql.host', '127.0.0.1');
            $database = config('database.connections.mysql.database', '');
            $username = config('database.connections.mysql.username', '');
            $password = config('database.connections.mysql.password', '');
            $port = config('database.connections.mysql.port', 3306);

            if (empty($database)) {
                throw new \Exception('Database name not configured');
            }

            // Save uploaded file temporarily
            $tempFile = tempnam(sys_get_temp_dir(), 'restore_');
            $file->move(dirname($tempFile), basename($tempFile));

            // Create pre-restore backup
            $backupTimestamp = date('Y-m-d_H-i-s');
            $backupFile = storage_path("app/backups/pre_restore_{$backupTimestamp}.sql");
            
            // Ensure backup directory exists
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $backupCommand = sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($backupFile)
            );

            exec($backupCommand);

            // Restore database
            $restoreCommand = sprintf(
                'mysql -h%s -P%s -u%s -p%s %s < %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($tempFile)
            );

            exec($restoreCommand, $output, $returnCode);

            // Clean up
            unlink($tempFile);

            if ($returnCode === 0) {
                Log::info("Database restored from {$file->getClientOriginalName()} by user: {$user->email}");
                return redirect()->back()->with('success', 'Database restored successfully!');
            } else {
                throw new \Exception('Restore failed. Output: ' . implode(', ', $output));
            }

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            Log::error("Restore failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Backup database using PDO when mysqldump is not available
     * X100 Senior Dev Bulletproof Fallback Method
     */
    private function backupUsingPDO($host, $port, $database, $username, $password, $filepath)
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);

            $sql = '';
            
            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Table structure
                $sql .= "\n-- Table structure for `{$table}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                
                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
                $sql .= $createTable[1] . ";\n\n";
                
                // Table data
                $sql .= "-- Data for table `{$table}`\n";
                $data = $pdo->query("SELECT * FROM `{$table}`");
                
                while ($row = $data->fetch(\PDO::FETCH_ASSOC)) {
                    $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', array_keys($row)) . "`) VALUES (";
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = $pdo->quote($value);
                    }
                    $sql .= implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }

            return file_put_contents($filepath, $sql) !== false;
            
        } catch (\Exception $e) {
            Log::error("PDO Backup failed: " . $e->getMessage());
            return false;
        }
    }
}
