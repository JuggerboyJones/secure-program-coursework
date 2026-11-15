<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/database.php';

try {
    $connection = database();
    foreach (['users', 'categories', 'incident_reports'] as $table) {
        // Table names are fixed application constants, never request input.
        $connection->query("SELECT 1 FROM {$table} LIMIT 1");
    }

    $categoryCount = (int) $connection->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    if ($categoryCount < 4) {
        throw new RuntimeException('The starter categories are missing.');
    }

    echo "OK: PHP is running, MySQL is connected, all three tables exist, and {$categoryCount} categories are available.\n";
} catch (Throwable $exception) {
    // Do not print a connection string, credentials or database exception text.
    fwrite(STDERR, "Setup check failed: verify the database service, local settings and initial schema.\n");
    exit(1);
}
