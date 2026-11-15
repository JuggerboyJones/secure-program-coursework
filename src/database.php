<?php

declare(strict_types=1);

/** Shared connection for Person A's pages and Person B's authentication/admin work. */
function database(): PDO
{
    $host = getenv('DB_HOST') ?: 'database';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_DATABASE') ?: 'incident_reporting';
    $user = getenv('DB_USERNAME') ?: 'incident_app';
    $password = getenv('DB_PASSWORD');

    if ($password === false || $password === '') {
        throw new RuntimeException('The database password is not configured.');
    }

    return new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3,
        ]
    );
}
