<?php

if (!isset($metrics) || !is_array($metrics)) {
    die("Dashboard metrics are not available.");
}

function dashboardCurrency($value): string
{
    return "৳" . number_format((float) $value, 2);
}

function dashboardNumber($value): string
{
    return number_format((float) $value, 0);
}

?>

<!-- BUSINESS OVERVIEW -->
<section id="dashboard">

    <div class="section-heading">

        <div>
            <h2>Business Overview</h2>

            <p>
                Supermarket sales performance summary
            </p>
        </div>

    </div>


    <!-- KPI CARDS -->
    <div class="stats-grid">


        <!-- TOTAL REVENUE -->
        <div class="stat-card">

            <div class="stat-icon">৳</div>

            <h3>Total Revenue</h3>

            <div class="stat-value">
                <?= dashboardCurrency($metrics["total_revenue"]) ?>
            </div>

            <p class="stat-description">
                Total sales revenue
            </p>

        </div>


        <!-- TOTAL PROFIT -->
        <div class="stat-card">

            <div class="stat-icon">↗</div>

            <h3>Total Profit</h3>

            <div class="stat-value">
                <?= dashboardCurrency($metrics["total_profit"]) ?>
            </div>

            <p class="stat-description">
                Total business profit
            </p>

        </div>


        <!-- TOTAL ORDERS -->
        <div class="stat-card">

            <div class="stat-icon">▤</div>

            <h3>Total Orders</h3>

            <div class="stat-value">
                <?= dashboardNumber($metrics["total_orders"]) ?>
            </div>

            <p class="stat-description">
                Total transactions
            </p>

        </div>


        <!-- TOTAL CUSTOMERS -->
        <div class="stat-card">

            <div class="stat-icon">♙</div>

            <h3>Total Customers</h3>

            <div class="stat-value">
                <?= dashboardNumber($metrics["total_customers"]) ?>
            </div>

            <p class="stat-description">
                Unique customers
            </p>

        </div>


        <!-- PRODUCTS SOLD -->
        <div class="stat-card">

            <div class="stat-icon">▣</div>

            <h3>Products Sold</h3>

            <div class="stat-value">
                <?= dashboardNumber($metrics["total_products_sold"]) ?>
            </div>

            <p class="stat-description">
                Total quantity sold
            </p>

        </div>


        <!-- PROFIT MARGIN -->
        <div class="stat-card">

            <div class="stat-icon">%</div>

            <h3>Profit Margin</h3>

            <div class="stat-value">
                <?= number_format((float) $metrics["profit_margin"], 2) ?>%
            </div>

            <p class="stat-description">
                Revenue profitability
            </p>

        </div>


    </div>

</section>


<!-- SALES SUMMARY -->
<section class="dashboard-section">

    <div class="section-title">
        Sales Summary
    </div>

    <div class="analytics-grid">


        <div class="analytics-card">

            <h3>Average Order Value</h3>

            <div class="analytics-number">
                <?= dashboardCurrency($metrics["average_order_value"]) ?>
            </div>

            <p>
                Average revenue per order
            </p>

        </div>


        <div class="analytics-card">

            <h3>Total Cost</h3>

            <div class="analytics-number">
                <?= dashboardCurrency($metrics["total_cost"]) ?>
            </div>

            <p>
                Total product cost
            </p>

        </div>


        <div class="analytics-card">

            <h3>Total Products</h3>

            <div class="analytics-number">
                <?= dashboardNumber($metrics["total_products"]) ?>
            </div>

            <p>
                Unique products sold
            </p>

        </div>


    </div>

</section>