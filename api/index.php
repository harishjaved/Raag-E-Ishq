<?php

// Ensure HTTPS is strictly recognized in Vercel serverless environment
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

// Vercel Serverless Function entry point for Laravel
// Create writable directories in /tmp since Vercel's root filesystem is read-only
$tmpDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/cache',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Copy pre-seeded SQLite database to /tmp if it doesn't already exist or is empty
$srcDb = __DIR__ . '/../database/database.sqlite';
$dstDb = '/tmp/database.sqlite';
if ((!file_exists($dstDb) || filesize($dstDb) === 0) && file_exists($srcDb)) {
    @copy($srcDb, $dstDb);
} elseif (!file_exists($dstDb)) {
    @touch($dstDb);
}

require __DIR__ . '/../public/index.php';
