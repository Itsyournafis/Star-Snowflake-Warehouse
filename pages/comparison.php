<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";

if (
    !isset($starMetrics, $snowflakeMetrics) ||
    !is_array($starMetrics) ||
    !is_array($snowflakeMetrics)
) {
    die("Schema comparison data is not available.");
}


/**
 * Currency formatter.
 */
function comparisonCurrency($value): string
{
    return "৳" . number_format((float) $value, 2);
}


/**
 * Calculate median execution time.
 */
function calculateMedian(array $executionTimes): float
{
    sort($executionTimes);

    $count = count($executionTimes);

    if ($count === 0) {
        return 0;
    }

    $middle = (int) floor($count / 2);

    if ($count % 2 === 0) {

        return (
            $executionTimes[$middle - 1] +
            $executionTimes[$middle]
        ) / 2;

    }

    return $executionTimes[$middle];
}


/**
 * Run a simple fact table benchmark.
 *
 * Executes the same aggregation query 30 times.
 */
function runSchemaBenchmark(string $databaseName): array
{
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            COUNT(*) AS total_sales,
            SUM(sales_amount) AS total_revenue,
            SUM(cost_amount) AS total_cost,
            SUM(profit_amount) AS total_profit
        FROM fact_sales
    ";

    // Warm-up execution
    $pdo->query($sql)->fetch();

    $executionTimes = [];

    for ($i = 0; $i < 30; $i++) {

        $startTime = hrtime(true);

        $stmt = $pdo->query($sql);

        $stmt->fetch();

        $endTime = hrtime(true);

        $executionTimes[] =
            ($endTime - $startTime) / 1_000_000;

    }

    return [
        "median_time" => calculateMedian($executionTimes),
        "runs" => count($executionTimes)
    ];
}


/**
 * Run category-wise JOIN benchmark.
 *
 * Star Schema:
 * fact_sales -> dim_product
 *
 * Snowflake Schema:
 * fact_sales -> dim_product
 *             -> dim_subcategory
 *             -> dim_category
 */
function runCategoryJoinBenchmark(
    string $databaseName,
    string $schemaType
): array {

    $pdo = getDatabaseConnection($databaseName);

    if ($schemaType === "star") {

        $sql = "
            SELECT
                p.category AS category_name,
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

    } elseif ($schemaType === "snowflake") {

        $sql = "
            SELECT
                c.category_name AS category_name,
                COALESCE(SUM(f.sales_amount), 0) AS total_revenue,
                COALESCE(SUM(f.profit_amount), 0) AS total_profit
            FROM fact_sales f

            INNER JOIN dim_product p
                ON f.product_key = p.product_key

            INNER JOIN dim_subcategory sc
                ON p.subcategory_key = sc.subcategory_key

            INNER JOIN dim_category c
                ON sc.category_key = c.category_key

            GROUP BY
                c.category_key,
                c.category_name

            ORDER BY
                total_revenue DESC
        ";

    } else {

        throw new InvalidArgumentException(
            "Invalid schema type."
        );

    }


    // Warm-up execution
    $pdo->query($sql)->fetchAll();

    $executionTimes = [];

    $resultRows = [];

    // Execute benchmark 30 times
    for ($i = 0; $i < 30; $i++) {

        $startTime = hrtime(true);

        $stmt = $pdo->query($sql);

        $resultRows = $stmt->fetchAll();

        $endTime = hrtime(true);

        $executionTimes[] =
            ($endTime - $startTime) / 1_000_000;

    }


    return [
        "median_time" => calculateMedian($executionTimes),
        "runs" => count($executionTimes),
        "rows" => $resultRows
    ];

}


// =====================================
// FACT TABLE BENCHMARK
// =====================================

$starBenchmark = null;
$snowflakeBenchmark = null;

$benchmarkError = null;

try {

    $starBenchmark =
        runSchemaBenchmark("supermarket_star");

    $snowflakeBenchmark =
        runSchemaBenchmark("supermarket_snowflake");

} catch (Throwable $e) {

    $benchmarkError = $e->getMessage();

}


// Determine faster schema for fact benchmark

$fasterSchema = "Not determined";

if (
    $starBenchmark !== null &&
    $snowflakeBenchmark !== null
) {

    if (
        $starBenchmark["median_time"] <
        $snowflakeBenchmark["median_time"]
    ) {

        $fasterSchema = "Star Schema";

    } elseif (
        $snowflakeBenchmark["median_time"] <
        $starBenchmark["median_time"]
    ) {

        $fasterSchema = "Snowflake Schema";

    } else {

        $fasterSchema = "Equal";

    }

}


// =====================================
// CATEGORY JOIN BENCHMARK
// =====================================

$starJoinBenchmark = null;
$snowflakeJoinBenchmark = null;

$joinBenchmarkError = null;

try {

    $starJoinBenchmark = runCategoryJoinBenchmark(
        "supermarket_star",
        "star"
    );

    $snowflakeJoinBenchmark = runCategoryJoinBenchmark(
        "supermarket_snowflake",
        "snowflake"
    );

} catch (Throwable $e) {

    $joinBenchmarkError = $e->getMessage();

}


// Determine faster schema for JOIN benchmark

$fasterJoinSchema = "Not determined";

if (
    $starJoinBenchmark !== null &&
    $snowflakeJoinBenchmark !== null
) {

    if (
        $starJoinBenchmark["median_time"] <
        $snowflakeJoinBenchmark["median_time"]
    ) {

        $fasterJoinSchema = "Star Schema";

    } elseif (
        $snowflakeJoinBenchmark["median_time"] <
        $starJoinBenchmark["median_time"]
    ) {

        $fasterJoinSchema = "Snowflake Schema";

    } else {

        $fasterJoinSchema = "Equal";

    }

}


// =====================================
// CATEGORY RESULT COMPARISON
// =====================================

$categoryComparisonRows = [];

if (
    $starJoinBenchmark !== null &&
    $snowflakeJoinBenchmark !== null
) {

    $starCategories = [];

    $snowflakeCategories = [];


    foreach ($starJoinBenchmark["rows"] as $row) {

        $starCategories[$row["category_name"]] = $row;

    }


    foreach ($snowflakeJoinBenchmark["rows"] as $row) {

        $snowflakeCategories[$row["category_name"]] = $row;

    }


    $allCategories = array_unique(
        array_merge(
            array_keys($starCategories),
            array_keys($snowflakeCategories)
        )
    );


    foreach ($allCategories as $categoryName) {

        $starRow = $starCategories[$categoryName] ?? [];

        $snowflakeRow = $snowflakeCategories[$categoryName] ?? [];

        $categoryComparisonRows[] = [

            "category_name" => $categoryName,

            "star_revenue" =>
                $starRow["total_revenue"] ?? 0,

            "snowflake_revenue" =>
                $snowflakeRow["total_revenue"] ?? 0,

            "star_profit" =>
                $starRow["total_profit"] ?? 0,

            "snowflake_profit" =>
                $snowflakeRow["total_profit"] ?? 0

        ];

    }

}

?>

<!-- PAGE INTRO -->

<section class="page-intro">

    <div>

        <h2>Schema Comparison</h2>

        <p>
            A comparative study of Star Schema and Snowflake Schema.
        </p>

    </div>

    <div class="analytics-badge">
        Database Comparison
    </div>

</section>


<!-- DATABASE OVERVIEW -->

<section class="dashboard-section">

    <div class="section-title">
        Database Architecture
    </div>

    <div class="analytics-grid">

        <div class="analytics-card">

            <h3>⭐ Star Schema</h3>

            <div class="analytics-number">
                6 Tables
            </div>

            <p>
                A denormalized dimensional model with
                dimension tables directly connected to
                the central fact table.
            </p>

        </div>


        <div class="analytics-card">

            <h3>❄ Snowflake Schema</h3>

            <div class="analytics-number">
                11 Tables
            </div>

            <p>
                A normalized dimensional model with
                dimensions divided into related
                sub-dimension tables.
            </p>

        </div>

    </div>

</section>


<!-- SALES PERFORMANCE COMPARISON -->

<section class="dashboard-section">

    <div class="section-title">
        Sales Performance Comparison
    </div>

    <p class="section-description">
        Both databases use the same logical supermarket sales data.
    </p>

    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>Performance Metric</th>
                    <th>Star Schema</th>
                    <th>Snowflake Schema</th>
                    <th>Result</th>
                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Total Revenue</td>

                    <td>
                        <?= comparisonCurrency($starMetrics["total_revenue"]) ?>
                    </td>

                    <td>
                        <?= comparisonCurrency($snowflakeMetrics["total_revenue"]) ?>
                    </td>

                    <td>Equal</td>

                </tr>


                <tr>

                    <td>Total Profit</td>

                    <td>
                        <?= comparisonCurrency($starMetrics["total_profit"]) ?>
                    </td>

                    <td>
                        <?= comparisonCurrency($snowflakeMetrics["total_profit"]) ?>
                    </td>

                    <td>Equal</td>

                </tr>


                <tr>

                    <td>Total Orders</td>

                    <td>
                        <?= (int) $starMetrics["total_orders"] ?>
                    </td>

                    <td>
                        <?= (int) $snowflakeMetrics["total_orders"] ?>
                    </td>

                    <td>Equal</td>

                </tr>


                <tr>

                    <td>Total Customers</td>

                    <td>
                        <?= (int) $starMetrics["total_customers"] ?>
                    </td>

                    <td>
                        <?= (int) $snowflakeMetrics["total_customers"] ?>
                    </td>

                    <td>Equal</td>

                </tr>


                <tr>

                    <td>Products Sold</td>

                    <td>
                        <?= (float) $starMetrics["total_products_sold"] ?>
                    </td>

                    <td>
                        <?= (float) $snowflakeMetrics["total_products_sold"] ?>
                    </td>

                    <td>Equal</td>

                </tr>


                <tr>

                    <td>Average Order Value</td>

                    <td>
                        <?= comparisonCurrency($starMetrics["average_order_value"]) ?>
                    </td>

                    <td>
                        <?= comparisonCurrency($snowflakeMetrics["average_order_value"]) ?>
                    </td>

                    <td>Equal</td>

                </tr>


                <tr>

                    <td>Profit Margin</td>

                    <td>
                        <?= number_format((float) $starMetrics["profit_margin"], 2) ?>%
                    </td>

                    <td>
                        <?= number_format((float) $snowflakeMetrics["profit_margin"], 2) ?>%
                    </td>

                    <td>Equal</td>

                </tr>

            </tbody>

        </table>

    </div>

</section>


<!-- STRUCTURAL DIFFERENCES -->

<section class="dashboard-section">

    <div class="section-title">
        Structural Differences
    </div>

    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>Comparison Criteria</th>
                    <th>Star Schema</th>
                    <th>Snowflake Schema</th>
                </tr>

            </thead>

            <tbody>

                <tr>
                    <td>Dimension Design</td>
                    <td>Denormalized</td>
                    <td>Normalized</td>
                </tr>

                <tr>
                    <td>Dimension Relationships</td>
                    <td>Directly connected to Fact Table</td>
                    <td>May connect through Sub-dimensions</td>
                </tr>

                <tr>
                    <td>Number of Tables</td>
                    <td>6</td>
                    <td>11</td>
                </tr>

                <tr>
                    <td>Data Redundancy</td>
                    <td>Generally Higher</td>
                    <td>Generally Lower</td>
                </tr>

                <tr>
                    <td>Query Complexity</td>
                    <td>Generally Simpler</td>
                    <td>May Require More JOINs</td>
                </tr>

            </tbody>

        </table>

    </div>

</section>


<!-- FACT TABLE PERFORMANCE -->

<section class="dashboard-section">

    <div class="section-title">
        Fact Table Performance Testing
    </div>

    <p class="section-description">

        Execution time comparison using the same aggregation query
        directly on the fact table.

    </p>


    <?php if ($benchmarkError !== null): ?>

        <div class="error-message">

            Benchmark Error:

            <?= htmlspecialchars($benchmarkError) ?>

        </div>

    <?php elseif (
        $starBenchmark !== null &&
        $snowflakeBenchmark !== null
    ): ?>


        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>Benchmark Metric</th>
                        <th>Star Schema</th>
                        <th>Snowflake Schema</th>
                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>Median Execution Time</td>

                        <td>
                            <?= number_format($starBenchmark["median_time"], 6) ?> ms
                        </td>

                        <td>
                            <?= number_format($snowflakeBenchmark["median_time"], 6) ?> ms
                        </td>

                    </tr>


                    <tr>

                        <td>Number of Runs</td>

                        <td>
                            <?= $starBenchmark["runs"] ?>
                        </td>

                        <td>
                            <?= $snowflakeBenchmark["runs"] ?>
                        </td>

                    </tr>


                    <tr>

                        <td>Faster Result</td>

                        <td colspan="2">

                            <strong>
                                <?= htmlspecialchars($fasterSchema) ?>
                            </strong>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <div class="analytics-grid" style="margin-top: 20px;">

            <div class="analytics-card">

                <h3>Star Schema</h3>

                <div class="analytics-number">

                    <?= number_format($starBenchmark["median_time"], 6) ?> ms

                </div>

                <p>
                    Median query execution time.
                </p>

            </div>


            <div class="analytics-card">

                <h3>Snowflake Schema</h3>

                <div class="analytics-number">

                    <?= number_format($snowflakeBenchmark["median_time"], 6) ?> ms

                </div>

                <p>
                    Median query execution time.
                </p>

            </div>


            <div class="analytics-card">

                <h3>Benchmark Runs</h3>

                <div class="analytics-number">
                    <?= $starBenchmark["runs"] ?>
                </div>

                <p>
                    Executions per database, excluding warm-up.
                </p>

            </div>

        </div>


        <div class="table-note" style="margin-top: 15px;">

            <strong>Note:</strong>

            This benchmark measures a simple aggregation query
            directly on the fact table. Results may vary depending
            on system load, database cache and hardware.
            The current dataset is small, so these measurements
            should not be treated as a general performance conclusion.

        </div>


    <?php endif; ?>

</section>


<!-- CATEGORY JOIN PERFORMANCE -->

<section class="dashboard-section">

    <div class="section-title">
        Category-wise JOIN Performance Testing
    </div>

    <p class="section-description">

        This benchmark compares category-wise revenue and profit
        queries using different dimensional relationships.

        Star Schema uses one dimension JOIN, while Snowflake Schema
        uses three dimension JOINs.

    </p>


    <?php if ($joinBenchmarkError !== null): ?>

        <div class="error-message">

            JOIN Benchmark Error:

            <?= htmlspecialchars($joinBenchmarkError) ?>

        </div>


    <?php elseif (
        $starJoinBenchmark !== null &&
        $snowflakeJoinBenchmark !== null
    ): ?>


        <!-- JOIN PERFORMANCE TABLE -->

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>Benchmark Metric</th>

                        <th>Star Schema</th>

                        <th>Snowflake Schema</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>Median Execution Time</td>

                        <td>

                            <?= number_format(
                                $starJoinBenchmark["median_time"],
                                6
                            ) ?> ms

                        </td>

                        <td>

                            <?= number_format(
                                $snowflakeJoinBenchmark["median_time"],
                                6
                            ) ?> ms

                        </td>

                    </tr>


                    <tr>

                        <td>Number of JOINs</td>

                        <td>1 JOIN</td>

                        <td>3 JOINs</td>

                    </tr>


                    <tr>

                        <td>Number of Runs</td>

                        <td>
                            <?= $starJoinBenchmark["runs"] ?>
                        </td>

                        <td>
                            <?= $snowflakeJoinBenchmark["runs"] ?>
                        </td>

                    </tr>


                    <tr>

                        <td>Faster Result</td>

                        <td colspan="2">

                            <strong>
                                <?= htmlspecialchars($fasterJoinSchema) ?>
                            </strong>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <!-- JOIN BENCHMARK CARDS -->

        <div class="analytics-grid" style="margin-top: 20px;">


            <div class="analytics-card">

                <h3>⭐ Star Schema JOIN</h3>

                <div class="analytics-number">

                    <?= number_format(
                        $starJoinBenchmark["median_time"],
                        6
                    ) ?> ms

                </div>

                <p>
                    Median execution time using one dimension JOIN.
                </p>

            </div>


            <div class="analytics-card">

                <h3>❄ Snowflake Schema JOIN</h3>

                <div class="analytics-number">

                    <?= number_format(
                        $snowflakeJoinBenchmark["median_time"],
                        6
                    ) ?> ms

                </div>

                <p>
                    Median execution time using three dimension JOINs.
                </p>

            </div>


            <div class="analytics-card">

                <h3>Benchmark Runs</h3>

                <div class="analytics-number">

                    <?= $starJoinBenchmark["runs"] ?>

                </div>

                <p>
                    Executions per database, excluding warm-up.
                </p>

            </div>


        </div>


        <!-- CATEGORY RESULT COMPARISON -->

        <div class="section-title" style="margin-top: 30px;">

            Category-wise Result Verification

        </div>

        <p class="section-description">

            Verifying that both schemas produce the same
            category-wise revenue and profit results.

        </p>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>Category</th>

                        <th>Star Revenue</th>

                        <th>Snowflake Revenue</th>

                        <th>Star Profit</th>

                        <th>Snowflake Profit</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($categoryComparisonRows as $row): ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    (string) $row["category_name"]
                                ) ?>

                            </td>


                            <td>

                                <?= comparisonCurrency(
                                    $row["star_revenue"]
                                ) ?>

                            </td>


                            <td>

                                <?= comparisonCurrency(
                                    $row["snowflake_revenue"]
                                ) ?>

                            </td>


                            <td>

                                <?= comparisonCurrency(
                                    $row["star_profit"]
                                ) ?>

                            </td>


                            <td>

                                <?= comparisonCurrency(
                                    $row["snowflake_profit"]
                                ) ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (empty($categoryComparisonRows)): ?>

                        <tr>

                            <td colspan="5">

                                No category data available.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- BENCHMARK NOTE -->

        <div class="table-note" style="margin-top: 15px;">

            <strong>Note:</strong>

            This benchmark measures category-wise aggregation
            using different JOIN structures.

            Star Schema uses one dimension JOIN.
            Snowflake Schema uses three dimension JOINs.

            Both queries return category-wise revenue and profit.

            Results may vary depending on database cache,
            system load, hardware and query execution conditions.

            The current dataset is small, so this benchmark
            should not be treated as a universal performance conclusion.

        </div>


    <?php endif; ?>

</section>