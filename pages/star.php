<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";

$tables = [];

$errorMessage = null;

try {

    $pdo = getDatabaseConnection("supermarket_star");

    // Get all tables from Star Schema database
    $sql = "
        SELECT
            TABLE_NAME,
            TABLE_ROWS
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        ORDER BY TABLE_NAME
    ";

    $stmt = $pdo->query($sql);

    $tables = $stmt->fetchAll();

} catch (Throwable $e) {

    $errorMessage = "Unable to load Star Schema information.";

}

$tableCount = count($tables);

?>

<!-- STAR SCHEMA PAGE -->

<section class="page-intro">

    <div>

        <h2>Star Schema Architecture</h2>

        <p>
            Explore the structure of the Supermarket Star Schema database.
        </p>

    </div>

    <div class="analytics-badge">
        Star Schema
    </div>

</section>


<!-- DATABASE OVERVIEW -->

<section class="dashboard-section">

    <div class="section-title">
        Database Overview
    </div>

    <div class="analytics-grid">

        <div class="analytics-card">

            <h3>Database Name</h3>

            <div class="analytics-number">
                Star Schema
            </div>

            <p>supermarket_star</p>

        </div>


        <div class="analytics-card">

            <h3>Total Tables</h3>

            <div class="analytics-number">
                <?= number_format($tableCount) ?>
            </div>

            <p>Tables detected from MySQL metadata</p>

        </div>


        <div class="analytics-card">

            <h3>Architecture</h3>

            <div class="analytics-number">
                Dimensional
            </div>

            <p>Denormalized dimension model</p>

        </div>

    </div>

</section>


<!-- STAR SCHEMA STRUCTURE -->

<section class="dashboard-section">

    <div class="section-title">
        Star Schema Table Structure
    </div>

    <p class="section-description">

        The Star Schema uses a central fact table connected
        directly to dimension tables.

    </p>

    <?php if ($errorMessage !== null): ?>

        <div class="product-error">
            <?= htmlspecialchars($errorMessage) ?>
        </div>

    <?php elseif (empty($tables)): ?>

        <p>No tables found in the database.</p>

    <?php else: ?>

        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>SL</th>
                        <th>Table Name</th>
                        <th>Table Type</th>
                        <th>Estimated Rows</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($tables as $index => $table): ?>

                        <tr>

                            <td>
                                <?= $index + 1 ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($table["TABLE_NAME"]) ?>
                            </td>

                            <td>

                                <?php if ($table["TABLE_NAME"] === "fact_sales"): ?>

                                    <span class="table-type fact">
                                        Fact Table
                                    </span>

                                <?php else: ?>

                                    <span class="table-type dimension">
                                        Dimension Table
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= number_format((int) $table["TABLE_ROWS"]) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>


<!-- ARCHITECTURE EXPLANATION -->

<section class="dashboard-section">

    <div class="section-title">
        How Star Schema Works
    </div>

    <div class="architecture-info">

        <h3>Central Fact Table</h3>

        <p>
            The fact_sales table stores measurable business
            information such as quantity, sales amount,
            cost amount and profit amount.
        </p>

        <h3>Dimension Tables</h3>

        <p>
            Dimension tables provide descriptive information
            about dates, products, customers, stores and payments.
        </p>

        <h3>Direct Relationships</h3>

        <p>
            Dimension tables connect directly to the central
            fact table through their respective keys.
        </p>

    </div>

</section>


<!-- ADVANTAGES -->

<section class="dashboard-section">

    <div class="section-title">
        Star Schema Characteristics
    </div>

    <div class="analytics-grid">

        <div class="analytics-card">

            <h3>Simple Queries</h3>

            <p>
                Direct relationships can reduce the number
                of JOIN operations required.
            </p>

        </div>


        <div class="analytics-card">

            <h3>Analytics Friendly</h3>

            <p>
                Suitable for reporting and analytical queries.
            </p>

        </div>


        <div class="analytics-card">

            <h3>Denormalized Dimensions</h3>

            <p>
                Related descriptive attributes are stored
                together in dimension tables.
            </p>

        </div>

    </div>

</section>