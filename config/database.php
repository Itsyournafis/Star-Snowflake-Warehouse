<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Database Connection Configuration
|--------------------------------------------------------------------------
*/

function getDatabaseConnection(string $databaseName): PDO
{
    // Database credentials
    $host = "127.0.0.1";
    $username = "root";
    $password = "";
    $charset = "utf8mb4";

    // Allowed databases
    $allowedDatabases = [
        "supermarket_star",
        "supermarket_snowflake"
    ];

    // Validate database name
    if (!in_array($databaseName, $allowedDatabases, true)) {
        throw new InvalidArgumentException(
            "Invalid database name."
        );
    }

    // PDO connection string
    $dsn = "mysql:host=$host;dbname=$databaseName;charset=$charset";

    // PDO configuration
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES => false
    ];

    // Establish connection
    return new PDO(
        $dsn,
        $username,
        $password,
        $options
    );
}