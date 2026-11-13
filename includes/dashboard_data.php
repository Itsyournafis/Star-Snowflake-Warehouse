<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";

/**
 * Fetch dashboard KPI metrics from a selected database.
 */
function getDashboardMetrics(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            COUNT(*) AS total_sales,

            COUNT(DISTINCT transaction_id) AS total_orders,

            COUNT(DISTINCT customer_key) AS total_customers,

            COUNT(DISTINCT product_key) AS total_products,

            COALESCE(SUM(quantity), 0) AS total_products_sold,

            COALESCE(SUM(sales_amount), 0) AS total_revenue,

            COALESCE(SUM(cost_amount), 0) AS total_cost,

            COALESCE(SUM(profit_amount), 0) AS total_profit

        FROM fact_sales
    ";

    $stmt = $pdo->query($sql);

    $metrics = $stmt->fetch();

    $totalRevenue = (float) $metrics["total_revenue"];
    $totalProfit = (float) $metrics["total_profit"];
    $totalOrders = (int) $metrics["total_orders"];

    $metrics["average_order_value"] = $totalOrders > 0
        ? $totalRevenue / $totalOrders
        : 0;

    $metrics["profit_margin"] = $totalRevenue > 0
        ? ($totalProfit / $totalRevenue) * 100
        : 0;

    return $metrics;
}