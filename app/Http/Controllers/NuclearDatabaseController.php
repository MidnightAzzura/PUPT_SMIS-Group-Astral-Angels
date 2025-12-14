<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * NUCLEAR OPTION - X10000000 Senior Dev ZERO DEPENDENCY Solution
 * Completely bypasses Laravel container and all bindings
 */
class NuclearDatabaseController extends Controller
{
    public function backup(Request $request)
    {
        // Raw session check
        if (empty($_SESSION) && !session_id()) {
            session_start();
        }
        
        // Get user directly from database without any models
        $userId = session('login_web_' . sha1('Illuminate\Auth\SessionGuard'));
        if (!$userId) {
            return response('Unauthorized', 401);
        }

        // Direct database query for user - NO MODELS
        try {
            $user = DB::select("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]);
            if (empty($user)) {
                return response('User not found', 401);
            }
            $user = $user[0];
            
            // Check if SuperAdmin - case insensitive
            if (strtolower($user->role ?? '') !== 'superadmin') {
                return response('Access Denied - SuperAdmin Only', 403);
            }
        } catch (\Exception $e) {
            return response('Authentication error', 401);
        }

        try {
            // Get database config directly
            $config = config('database.connections.mysql');
            $host = $config['host'];
            $database = $config['database'];
            $username = $config['username'];
            $password = $config['password'];
            $port = $config['port'];

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

            // NUCLEAR OPTION: Use PHP PDO directly - NO LARAVEL MODELS
            $success = $this->createNuclearBackup($host, $port, $database, $username, $password, $filepath);

            if ($success && file_exists($filepath) && filesize($filepath) > 0) {
                // Optional logging - don't fail if table doesn't exist
                try {
                    DB::insert("INSERT INTO activity_log (description, subject_type, subject_id, causer_id, created_at) VALUES (?, ?, ?, ?, ?)", [
                        "Database backup created: {$filename}",
                        'App\\Models\\User',
                        $user->id,
                        $user->id,
                        now()
                    ]);
                } catch (\Exception $logError) {
                    // Logging failed but backup succeeded - continue
                }

                // Send file download
                return response()->download($filepath, $filename, [
                    'Content-Type' => 'application/sql',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ])->deleteFileAfterSend(true);
            } else {
                throw new \Exception('backup failed');
            }

        } catch (\Exception $e) {
            return response('Backup failed: ' . $e->getMessage(), 500);
        }
    }

    public function restore(Request $request)
    {
        // Raw session check
        if (empty($_SESSION) && !session_id()) {
            session_start();
        }
        
        // Get user directly from database without any models
        $userId = session('login_web_' . sha1('Illuminate\Auth\SessionGuard'));
        if (!$userId) {
            return response('Unauthorized', 401);
        }

        // Direct database query for user - NO MODELS
        try {
            $user = DB::select("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]);
            if (empty($user)) {
                return response('User not found', 401);
            }
            $user = $user[0];
            
            // Check if SuperAdmin
            if (strtolower($user->role ?? '') !== 'superadmin') {
                return response('Access Denied - SuperAdmin Only', 403);
            }
        } catch (\Exception $e) {
            return response('Authentication error', 401);
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

            // Store uploaded file
            $sqlContent = file_get_contents($file->getPathname());
            
            if (empty($sqlContent)) {
                throw new \Exception('Empty file uploaded');
            }

            // Execute SQL directly
            DB::unprepared($sqlContent);

            // Optional logging - don't fail if table doesn't exist
            try {
                DB::insert("INSERT INTO activity_log (description, subject_type, subject_id, causer_id, created_at) VALUES (?, ?, ?, ?, ?)", [
                    "database restore from {$file->getClientOriginalName()}",
                    'App\\Models\\User',
                    $user->id,
                    $user->id,
                    now()
                ]);
            } catch (\Exception $logError) {
                // Logging failed but restore succeeded - continue
            }

            return redirect()->back()->with('success', 'Database Restore Successful! 🚀');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * NUCLEAR BACKUP METHOD - Pure PHP, zero dependencies
     */
    private function createNuclearBackup($host, $port, $database, $username, $password, $filepath)
    {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);

            $sql = "-- BACKUP CREATED: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: {$database}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                $sql .= "-- Table: {$table}\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                
                // Get CREATE TABLE statement
                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
                $sql .= $createTable['Create Table'] . ";\n\n";
                
                // Get table data
                $rowCount = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                
                if ($rowCount > 0) {
                    $sql .= "-- Data for table `{$table}`\n";
                    $data = $pdo->query("SELECT * FROM `{$table}`");
                    
                    while ($row = $data->fetch()) {
                        $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', array_keys($row)) . "`) VALUES (";
                        $values = [];
                        foreach ($row as $value) {
                            $values[] = $pdo->quote($value);
                        }
                        $sql .= implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            return file_put_contents($filepath, $sql) !== false;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
