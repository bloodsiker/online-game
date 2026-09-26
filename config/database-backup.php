<?php

return [
    'path' => env('DB_BACKUP_PATH', storage_path('app/backups/database')),
    'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
    'timeout_seconds' => (int) env('DB_BACKUP_TIMEOUT', 1800),
];
