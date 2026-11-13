<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";

$databaseName = "supermarket_snowflake";
$tables = [];
$errorMessage = null;

try {
    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            TABLE_NAME,
            TABLE_ROWS
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = :database_name
        ORDER BY TABLE_NAME
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "database_name" => $databaseName
    ]);

    $tables = $stmt->fetchAll();

} catch (Throwable $error) {
    $errorMessage = "Unable to load Snowflake Schema information.";
}

$totalTables = count($tables);
$dimensionTables = 0;
$factTables = 0;

foreach ($tables as $table) {
    if ($table["TABLE_NAME"] === "fact_sales") {
        $factTables++;
    } else {
        $dimensionTables++;
    }
}
?>

<section class="page-intro">

    <div>
        <span class="analytics-badge">DATABASE ARCHITECTURE</span>

        <h1>Snowflake Schema</h1>

        <p>
            Explore the normalized Snowflake Schema structure
            of the Supermarket Sales Data Warehouse.
        </p>
    </div>

</section>


<?php if ($errorMessage !== null): ?>

    <div class="product-error">
        <?= htmlspecialchars($errorMessage) ?>
    </div>

<?php else: ?>


<!-- DATABASE SUMMARY -->

<section class="kpi-grid">

    <div class="kpi-card">
        <div class="kpi-info">
            <p>Total Tables</p>
            <h2><?= $totalTables ?></h2>
            <span>Snowflake database</span>
        </div>
    </div>


    <div class="kpi-card">
        <div class="kpi-info">
            <p>Fact Tables</p>
            <h2><?= $factTables ?></h2>
            <span>Central transaction table</span>
        </div>
    </div>


    <div class="kpi-card">
        <div class="kpi-info">
            <p>Dimension Tables</p>
            <h2><?= $dimensionTables ?></h2>
            <span>Normalized dimensions</span>
        </div>
    </div>

</section>


<!-- TABLE STRUCTURE -->

<section class="dashboard-section">

    <div class="section-header">

        <div>
            <h2>Database Tables</h2>

            <p>
                Tables currently available in
                supermarket_snowflake.
            </p>
        </div>

        <span class="analytics-badge">
            <?= $totalTables ?> TABLES
        </span>

    </div>


    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Table Name</th>
                    <th>Table Type</th>
                    <th>Estimated Rows</th>
                </tr>
            </thead>


            <tbody>

                <?php foreach ($tables as $index => $table): ?>

                    <?php
                    $isFactTable =
                        $table["TABLE_NAME"] === "fact_sales";
                    ?>

                    <tr>

                        <td>
                            <?= $index + 1 ?>
                        </td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($table["TABLE_NAME"]) ?>
                            </strong>
                        </td>

                        <td>

                            <?php if ($isFactTable): ?>

                                <span class="table-type fact">
                                    FACT TABLE
                                </span>

                            <?php else: ?>

                                <span class="table-type dimension">
                                    DIMENSION
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= number_format((int) $table["TABLE_ROWS"]) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (empty($tables)): ?>

                    <tr>
                        <td colspan="4">
                            No tables found in this database.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <p class="table-note">
        Note: TABLE_ROWS may show estimated values for
        InnoDB tables rather than exact row counts.
    </p>

</section>


<!-- SNOWFLAKE ARCHITECTURE -->

<section class="dashboard-section">

    <div class="section-header">
        <div>
            <h2>Snowflake Architecture</h2>

            <p>
                Understanding the normalized dimension structure.
            </p>
        </div>
    </div>


    <div class="architecture-info">

        <div class="architecture-item">

            <h3>01. Central Fact Table</h3>

            <p>
                <strong>fact_sales</strong> stores sales transaction
                information, including quantity, revenue, cost,
                profit and dimension keys.
            </p>

        </div>


        <div class="architecture-item">

            <h3>02. Product Hierarchy</h3>

            <p>
                Product-related information is distributed across
                separate dimension tables such as
                <strong>dim_product</strong>,
                <strong>dim_category</strong>,
                <strong>dim_subcategory</strong> and
                <strong>dim_brand</strong>.
            </p>

        </div>


        <div class="architecture-item">

            <h3>03. Customer Location</h3>

            <p>
                Customer and geographical information are separated
                into tables such as
                <strong>dim_customer</strong> and
                <strong>dim_city</strong>.
            </p>

        </div>


        <div class="architecture-item">

            <h3>04. Payment Hierarchy</h3>

            <p>
                Payment details and payment categories are maintained
                separately through
                <strong>dim_payment</strong> and
                <strong>dim_payment_category</strong>.
            </p>

        </div>

    </div>

</section>


<!-- KEY CHARACTERISTICS -->

<section class="dashboard-section">

    <div class="section-header">
        <div>
            <h2>Key Characteristics</h2>

            <p>
                Important properties of the Snowflake Schema.
            </p>
        </div>
    </div>


    <div class="architecture-info">

        <div class="architecture-item">
            <h3>Normalization</h3>
            <p>
                Dimension data is divided into related tables
                to reduce repeated descriptive information.
            </p>
        </div>

        <div class="architecture-item">
            <h3>More Relationships</h3>
            <p>
                Analytical queries may require additional JOIN
                operations to retrieve related dimension details.
            </p>
        </div>

        <div class="architecture-item">
            <h3>Structured Data</h3>
            <p>
                Hierarchical information is represented through
                multiple connected dimension tables.
            </p>
        </div>

        <div class="architecture-item">
            <h3>Analytical Purpose</h3>
            <p>
                The schema supports multidimensional analysis
                of supermarket sales data.
            </p>
        </div>

    </div>

</section>

<?php endif; ?>