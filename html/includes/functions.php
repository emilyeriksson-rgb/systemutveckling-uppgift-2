<?php

function connectDatabase(): PDO
{
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');
    $database = 'faceit';

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    try {
        $pdo = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        return $pdo;
    } catch (PDOException $exception) {
        error_log($exception->getMessage());

        http_response_code(500);
        exit('Could not connect to the database.');
    }
}