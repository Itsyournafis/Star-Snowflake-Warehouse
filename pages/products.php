<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";

$products = [];

try {

    // Connect to Star Schema Database
    $pdo = getDatabaseConnection("supermarket_star");

    // Fetch Product Sales Information
    $sql = "
        SELECT
            p.product_key,
            p.product_name,
            p.category,
            SUM(f.quantity) AS total_quantity_sold,
            SUM(f.sales_amount) AS total_revenue,
            SUM(f.profit_amount) AS total_profit

        FROM fact_sales f

        INNER JOIN dim_product p
            ON f.product_key = p.product_key

        GROUP BY
            p.product_key,
            p.product_name,
            p.category

        ORDER BY total_revenue DESC
    ";

    $stmt = $pdo->query($sql);

    $products = $stmt->fetchAll();

} catch (Throwable $e) {

    $errorMessage = "Unable to load product analytics.";

}

$totalProducts = count($products);

$totalQuantity = 0;
$totalRevenue = 0;
$totalProfit = 0;

foreach ($products as $product) {

    $totalQuantity += (float) $product["total_quantity_sold"];

    $totalRevenue += (float) $product["total_revenue"];

    $totalProfit += (float) $product["total_profit"];

}

?>

<!-- PRODUCTS PAGE -->

<section class="page-intro">

    <div>

        <h2>Product Analytics</h2>

        <p>
            Monitor product sales, revenue and profitability.
        </p>

    </div>

    <div class="analytics-badge">
        Star Schema Database
    </div>

</section>


<!-- PRODUCT SUMMARY -->

<section class="stats-grid">

    <div class="stat-card">

        <div class="stat-icon">▣</div>

        <h3>Total Products</h3>

        <div class="stat-value">
            <?= number_format($totalProducts) ?>
        </div>

        <p class="stat-description">
            Products with recorded sales
        </p>

    </div>


    <div class="stat-card">

        <div class="stat-icon">📦</div>

        <h3>Units Sold</h3>

        <div class="stat-value">
            <?= number_format($totalQuantity) ?>
        </div>

        <p class="stat-description">
            Total quantity sold
        </p>

    </div>


    <div class="stat-card">

        <div class="stat-icon">৳</div>

        <h3>Product Revenue</h3>

        <div class="stat-value">
            ৳<?= number_format($totalRevenue, 2) ?>
        </div>

        <p class="stat-description">
            Total product sales revenue
        </p>

    </div>

</section>


<!-- PRODUCT TABLE -->

<section class="dashboard-section">

    <div class="section-title">
        Product Sales Details
    </div>

    <p class="section-description">
        Product-wise sales performance retrieved directly
        from the Star Schema database.
    </p>


    <?php if (isset($errorMessage)): ?>

        <div class="product-error">
            <?= htmlspecialchars($errorMessage) ?>
        </div>

    <?php elseif (empty($products)): ?>

        <p class="section-description">
            No product sales data found.
        </p>

    <?php else: ?>

        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>Rank</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                        <th>Profit</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($products as $index => $product): ?>

                        <tr>

                            <td>
                                #<?= $index + 1 ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($product["product_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($product["category"]) ?>
                            </td>

                            <td>
                                <?= number_format((float) $product["total_quantity_sold"]) ?>
                            </td>

                            <td>
                                ৳<?= number_format((float) $product["total_revenue"], 2) ?>
                            </td>

                            <td>
                                ৳<?= number_format((float) $product["total_profit"], 2) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>


<!-- PROFIT SUMMARY -->

<section class="dashboard-section">

    <div class="section-title">
        Product Profit Summary
    </div>

    <div class="analytics-grid">

        <div class="analytics-card">

            <h3>Total Product Profit</h3>

            <div class="analytics-number">
                ৳<?= number_format($totalProfit, 2) ?>
            </div>

            <p>
                Total profit generated by all products.
            </p>

        </div>


        <div class="analytics-card">

            <h3>Average Revenue per Product</h3>

            <div class="analytics-number">
                ৳<?= number_format(
                    $totalProducts > 0 ? $totalRevenue / $totalProducts : 0,
                    2
                ) ?>
            </div>

            <p>
                Average revenue across products with sales.
            </p>

        </div>


        <div class="analytics-card">

            <h3>Average Profit per Product</h3>

            <div class="analytics-number">
                ৳<?= number_format(
                    $totalProducts > 0 ? $totalProfit / $totalProducts : 0,
                    2
                ) ?>
            </div>

            <p>
                Average profit across products with sales.
            </p>

        </div>

    </div>

</section>