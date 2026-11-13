<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";


/**
 * Fetch monthly revenue, cost and profit data.
 */
function getMonthlySalesData(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            d.year_number,
            d.month_number,
            d.month_name,
            COALESCE(SUM(f.sales_amount), 0) AS total_revenue,
            COALESCE(SUM(f.cost_amount), 0) AS total_cost,
            COALESCE(SUM(f.profit_amount), 0) AS total_profit
        FROM fact_sales f
        INNER JOIN dim_date d
            ON f.date_key = d.date_key
        GROUP BY
            d.year_number,
            d.month_number,
            d.month_name
        ORDER BY
            d.year_number ASC,
            d.month_number ASC
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll();
}


/**
 * Fetch category-wise revenue data.
 */
function getCategoryRevenueData(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            p.category,
            COALESCE(SUM(f.quantity), 0) AS total_quantity,
            COALESCE(SUM(f.sales_amount), 0) AS total_revenue,
            COALESCE(SUM(f.profit_amount), 0) AS total_profit
        FROM fact_sales f
        INNER JOIN dim_product p
            ON f.product_key = p.product_key
        GROUP BY
            p.category
        ORDER BY
            total_revenue DESC
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll();
}


/**
 * Fetch payment method analysis data.
 */
function getPaymentMethodData(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            p.payment_method,
            COUNT(f.sales_key) AS total_transactions,
            COALESCE(SUM(f.sales_amount), 0) AS total_revenue
        FROM fact_sales f
        INNER JOIN dim_payment p
            ON f.payment_key = p.payment_key
        GROUP BY
            p.payment_method
        ORDER BY
            total_revenue DESC
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll();
}


/**
 * Fetch top 5 selling products by revenue.
 */
function getTopSellingProductsData(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            p.product_key,
            p.product_name,
            p.category,
            COALESCE(SUM(f.quantity), 0) AS total_quantity_sold,
            COALESCE(SUM(f.sales_amount), 0) AS total_revenue,
            COALESCE(SUM(f.profit_amount), 0) AS total_profit
        FROM fact_sales f
        INNER JOIN dim_product p
            ON f.product_key = p.product_key
        GROUP BY
            p.product_key,
            p.product_name,
            p.category
        ORDER BY
            total_revenue DESC
        LIMIT 5
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll();
}


/**
 * Fetch city-wise sales analysis data.
 */
function getCityWiseSalesData(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            s.city,
            COALESCE(SUM(f.quantity), 0) AS total_quantity_sold,
            COALESCE(SUM(f.sales_amount), 0) AS total_revenue,
            COALESCE(SUM(f.profit_amount), 0) AS total_profit
        FROM fact_sales f
        INNER JOIN dim_store s
            ON f.store_key = s.store_key
        GROUP BY
            s.city
        ORDER BY
            total_revenue DESC
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll();
}


/**
 * Fetch monthly revenue, cost and profit analysis.
 */
function getRevenueCostProfitData(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            d.year_number,
            d.month_number,
            d.month_name,
            COALESCE(SUM(f.sales_amount), 0) AS total_revenue,
            COALESCE(SUM(f.cost_amount), 0) AS total_cost,
            COALESCE(SUM(f.profit_amount), 0) AS total_profit
        FROM fact_sales f
        INNER JOIN dim_date d
            ON f.date_key = d.date_key
        GROUP BY
            d.year_number,
            d.month_number,
            d.month_name
        ORDER BY
            d.year_number ASC,
            d.month_number ASC
    ";

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll();
}