<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";


/**
 * Fetch dropdown options required by the Sales Management form.
 *
 * Supports both Star Schema and Snowflake Schema databases.
 */
function getSalesFormOptions(string $databaseName): array
{
    $allowedDatabases = [
        "supermarket_star",
        "supermarket_snowflake"
    ];

    if (!in_array($databaseName, $allowedDatabases, true)) {
        throw new InvalidArgumentException("Invalid database name.");
    }

    $pdo = getDatabaseConnection($databaseName);


    // ========================================
    // 1. FETCH AVAILABLE DATES
    // ========================================

    $dateSql = "
        SELECT
            date_key,
            full_date
        FROM dim_date
        ORDER BY full_date DESC
    ";

    $dates = $pdo->query($dateSql)->fetchAll();


    // ========================================
    // 2. FETCH PRODUCTS
    // ========================================

    $productSql = "
        SELECT
            product_key,
            product_id,
            product_name,
            current_unit_price
        FROM dim_product
        WHERE is_active = 1
        ORDER BY product_name ASC
    ";

    $products = $pdo->query($productSql)->fetchAll();


    // ========================================
    // 3. FETCH CUSTOMERS
    // ========================================

    $customerSql = "
        SELECT
            customer_key,
            customer_id,
            customer_name
        FROM dim_customer
        ORDER BY customer_name ASC
    ";

    $customers = $pdo->query($customerSql)->fetchAll();


    // ========================================
    // 4. FETCH STORES
    // ========================================

    if ($databaseName === "supermarket_star") {

        $storeSql = "
            SELECT
                store_key,
                store_id,
                store_name,
                city
            FROM dim_store
            ORDER BY store_name ASC
        ";

    } else {

        $storeSql = "
            SELECT
                s.store_key,
                s.store_id,
                s.store_name,
                c.city_name AS city
            FROM dim_store s
            INNER JOIN dim_city c
                ON s.city_key = c.city_key
            ORDER BY s.store_name ASC
        ";

    }

    $stores = $pdo->query($storeSql)->fetchAll();


    // ========================================
    // 5. FETCH PAYMENT METHODS
    // ========================================

    $paymentSql = "
        SELECT
            payment_key,
            payment_method
        FROM dim_payment
        ORDER BY payment_method ASC
    ";

    $payments = $pdo->query($paymentSql)->fetchAll();


    // ========================================
    // 6. RETURN ALL OPTIONS
    // ========================================

    return [
        "dates" => $dates,
        "products" => $products,
        "customers" => $customers,
        "stores" => $stores,
        "payments" => $payments
    ];
}