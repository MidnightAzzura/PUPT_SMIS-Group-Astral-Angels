<?php

namespace App\Helpers;

class NotificationCleaner
{
    /**
     * Completely remove all batch metadata from notification messages
     * This function ensures NO batch-related data is visible to users
     */
    public static function cleanBatchMetadata($message)
    {
        if (empty($message)) {
            return $message;
        }

        // Step 1: Find and remove everything after [BATCH:
        $batchPos = strpos($message, '[BATCH:');
        if ($batchPos !== false) {
            $message = substr($message, 0, $batchPos);
        }

        // Step 2: Remove any remaining batch-related artifacts
        $batchTerms = [
            '[BATCH:',
            'BATCH:',
            'batch_id',
            'request_ids', 
            'batch_count',
            'supply_names',
            'supply_list'
        ];
        
        foreach ($batchTerms as $term) {
            $message = str_replace($term, '', $message);
        }

        // Step 3: Remove ALL JSON-related characters
        $jsonChars = ['{', '}', '[', ']', '"', ':', ','];
        $message = str_replace($jsonChars, '', $message);

        // Step 4: Remove any JSON patterns that might remain
        $message = preg_replace('/\{[^}]*\}/', '', $message);
        $message = preg_replace('/\[[^\]]*\]/', '', $message);

        // Step 5: Clean up whitespace and formatting
        $message = trim($message);
        $message = rtrim($message, ',[{');
        $message = trim($message);
        
        // Step 6: Remove multiple spaces
        $message = preg_replace('/\s+/', ' ', $message);

        // Step 7: Final safety check - if message still contains suspicious patterns, clean more aggressively
        if (preg_match('/[{\[\]"}:]/', $message) || strpos($message, 'batch') !== false) {
            // Super aggressive cleaning - keep only letters, numbers, spaces, and basic punctuation
            $message = preg_replace('/[^a-zA-Z0-9\s\(\)\-\.\,\:\'\"]+/', ' ', $message);
            $message = preg_replace('/\s+/', ' ', $message);
            $message = trim($message);
        }

        return $message;
    }

    /**
     * Check if a notification is a batch notification
     */
    public static function isBatchNotification($message)
    {
        return str_contains($message, '[BATCH:');
    }

    /**
     * Get cleaned message for display with length limit
     */
    public static function getDisplayMessage($message, $limit = 100)
    {
        $cleaned = self::cleanBatchMetadata($message);
        return \Illuminate\Support\Str::limit($cleaned, $limit);
    }
}
?>
