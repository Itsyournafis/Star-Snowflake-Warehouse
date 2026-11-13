<?php

declare(strict_types=1);

require_once __DIR__ . "/includes/dashboard_data.php";


// ========================================
// 1. LOAD DATABASE METRICS
// ========================================

$starMetrics = getDashboardMetrics("supermarket_star");

$snowflakeMetrics = getDashboardMetrics("supermarket_snowflake");

$metrics = $starMetrics;


// ========================================
// 2. PAGE ROUTING
// ========================================

$allowedPages = [

    "dashboard" => __DIR__ . "/pages/dashboard.php",

    "analytics" => __DIR__ . "/pages/analytics.php",

    "comparison" => __DIR__ . "/pages/comparison.php",

    "products" => __DIR__ . "/pages/products.php",

    "sales" => __DIR__ . "/pages/sales.php",

    "star" => __DIR__ . "/pages/star.php",

    "snowflake" => __DIR__ . "/pages/snowflake.php"

];

$page = $_GET["page"] ?? "dashboard";

if (!array_key_exists($page, $allowedPages)) {
    $page = "dashboard";
}


// ========================================
// 3. PAGE TITLES
// ========================================

$pageTitles = [

    "dashboard" => "Dashboard",

    "analytics" => "Sales Analytics",

    "comparison" => "Schema Comparison",

    "products" => "Products",

    "sales" => "Sales Management",

    "star" => "Star Schema",

    "snowflake" => "Snowflake Schema"

];

$currentPageTitle = $pageTitles[$page];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($currentPageTitle) ?> | DataWarehouse
    </title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>


<body>


<div class="dashboard-container">


    <!-- ================================= -->
    <!-- SIDEBAR OVERLAY -->
    <!-- ================================= -->

    <div class="sidebar-overlay" id="sidebarOverlay"></div>


    <!-- ================================= -->
    <!-- SIDEBAR -->
    <!-- ================================= -->

    <aside class="sidebar" id="sidebar">


        <div class="sidebar-header">

            <div class="sidebar-logo">
                DW
            </div>

            <div class="sidebar-brand">

                <h2>DataWarehouse</h2>

                <p>Business Intelligence</p>

            </div>

            <button
                class="close-sidebar"
                id="closeSidebar"
                type="button"
                aria-label="Close sidebar"
            >
                &times;
            </button>

        </div>


        <!-- MAIN MENU -->

        <div class="sidebar-section">

            <p class="sidebar-section-title">
                MAIN MENU
            </p>


            <nav class="sidebar-menu">

                <a
                    href="?page=dashboard"
                    class="<?= $page === 'dashboard' ? 'active' : '' ?>"
                >
                    <span>▦</span>
                    Dashboard
                </a>


                <a
                    href="?page=analytics"
                    class="<?= $page === 'analytics' ? 'active' : '' ?>"
                >
                    <span>▥</span>
                    Sales Analytics
                </a>


                <a
                    href="?page=comparison"
                    class="<?= $page === 'comparison' ? 'active' : '' ?>"
                >
                    <span>⇄</span>
                    Schema Comparison
                </a>


                <a
                    href="?page=products"
                    class="<?= $page === 'products' ? 'active' : '' ?>"
                >
                    <span>▣</span>
                    Products
                </a>

            </nav>

        </div>


        <!-- DATA MANAGEMENT MENU -->

        <div class="sidebar-section">

            <p class="sidebar-section-title">
                DATA MANAGEMENT
            </p>


            <nav class="sidebar-menu">

                <a
                    href="?page=sales"
                    class="<?= $page === 'sales' ? 'active' : '' ?>"
                >
                    <span>▤</span>
                    Sales Management
                </a>

            </nav>

        </div>


        <!-- DATABASE MENU -->

        <div class="sidebar-section">

            <p class="sidebar-section-title">
                DATABASE
            </p>


            <nav class="sidebar-menu">

                <a
                    href="?page=star"
                    class="<?= $page === 'star' ? 'active' : '' ?>"
                >
                    <span>▤</span>
                    Star Schema
                </a>


                <a
                    href="?page=snowflake"
                    class="<?= $page === 'snowflake' ? 'active' : '' ?>"
                >
                    <span>❄</span>
                    Snowflake Schema
                </a>

            </nav>

        </div>


        <!-- SIDEBAR FOOTER -->

        <div class="sidebar-footer">

            <p>Data Warehouse Project</p>

            <span>Supermarket Sales Analytics</span>

        </div>


    </aside>


    <!-- ================================= -->
    <!-- MAIN CONTENT -->
    <!-- ================================= -->

    <main class="main-content">


        <!-- TOPBAR -->

        <header class="topbar">


            <div class="topbar-left">

                <button
                    class="menu-toggle"
                    id="menuToggle"
                    type="button"
                    aria-label="Open sidebar"
                    aria-expanded="false"
                >
                    ☰
                </button>


                <div class="topbar-heading">

                    <h2>
                        <?= htmlspecialchars($currentPageTitle) ?>
                    </h2>

                    <p>
                        Supermarket Data Warehouse & BI Platform
                    </p>

                </div>

            </div>


            <div class="topbar-right">

                <div class="system-status">

                    <span class="status-dot"></span>

                    System Online

                </div>

            </div>


        </header>


        <!-- PAGE CONTENT -->

        <div class="content-area">

            <?php

            require $allowedPages[$page];

            ?>

        </div>


        <!-- FOOTER -->

        <footer class="main-footer">

            <p>
                &copy; <?= date("Y") ?> DataWarehouse.
                Supermarket Sales Data Warehouse & Business Intelligence.
            </p>

        </footer>


    </main>


</div>


<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/app.js"></script>


</body>

</html>