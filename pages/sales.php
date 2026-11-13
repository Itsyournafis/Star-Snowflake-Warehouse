<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/sales_form_data.php";
require_once __DIR__ . "/../includes/sales_insert.php";


// ========================================
// 1. DATABASE SELECTION
// ========================================

$selectedDatabase = $_GET["database"] ?? "star";

if (!in_array($selectedDatabase, ["star", "snowflake"], true)) {
    $selectedDatabase = "star";
}

$databaseName = $selectedDatabase === "star"
    ? "supermarket_star"
    : "supermarket_snowflake";

$databaseLabel = $selectedDatabase === "star"
    ? "Star Schema"
    : "Snowflake Schema";


// ========================================
// 2. INITIALIZE VARIABLES
// ========================================

$salesRecords = [];

$totalRecords = 0;
$totalRevenue = 0;
$totalProfit = 0;

$formOptions = [
    "dates" => [],
    "products" => [],
    "customers" => [],
    "stores" => [],
    "payments" => []
];

$errorMessage = null;
$formError = null;

$redirectAfterSave = false;
$newTransactionId = "";


// ========================================
// 3. HANDLE NEW SALE SUBMISSION
// ========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && ($_POST["action"] ?? "") === "add_sale"
) {

    try {

        $result = insertSaleIntoBothDatabases($_POST);

        if ($result["success"] === true) {

            $redirectAfterSave = true;

            $newTransactionId = $result["transaction_id"];

        }

    } catch (Throwable $e) {

        $formError = $e->getMessage();

    }

}


// ========================================
// 4. FETCH SALES RECORDS AND FORM OPTIONS
// ========================================

try {

    $pdo = getDatabaseConnection($databaseName);

    $sql = "
        SELECT
            f.sales_key,
            f.transaction_id,
            f.line_number,
            d.full_date,
            p.product_name,
            c.customer_name,
            s.store_name,
            pm.payment_method,
            f.quantity,
            f.unit_price,
            f.discount_amount,
            f.sales_amount,
            f.cost_amount,
            f.profit_amount

        FROM fact_sales f

        INNER JOIN dim_date d
            ON f.date_key = d.date_key

        INNER JOIN dim_product p
            ON f.product_key = p.product_key

        INNER JOIN dim_customer c
            ON f.customer_key = c.customer_key

        INNER JOIN dim_store s
            ON f.store_key = s.store_key

        INNER JOIN dim_payment pm
            ON f.payment_key = pm.payment_key

        ORDER BY f.sales_key DESC
    ";

    $stmt = $pdo->query($sql);

    $salesRecords = $stmt->fetchAll();


    // Calculate Summary

    $totalRecords = count($salesRecords);

    foreach ($salesRecords as $record) {

        $totalRevenue += (float) $record["sales_amount"];

        $totalProfit += (float) $record["profit_amount"];

    }


    // Fetch dropdown options

    $formOptions = getSalesFormOptions($databaseName);


} catch (Throwable $e) {

    $errorMessage = "Unable to load sales data: " . $e->getMessage();

}


// ========================================
// 5. FORMATTING FUNCTION
// ========================================

function salesCurrency($amount): string
{
    return "৳" . number_format((float) $amount, 2);
}

?>


<!-- ======================================== -->
<!-- PAGE INTRO -->
<!-- ======================================== -->

<div class="page-intro">

    <div>

        <h1>Sales Management</h1>

        <p>
            Manage and monitor supermarket sales transactions.
        </p>

    </div>

    <span class="analytics-badge">
        <?= htmlspecialchars($databaseLabel) ?>
    </span>

</div>


<!-- ======================================== -->
<!-- SUCCESS MESSAGE -->
<!-- ======================================== -->

<?php if (($_GET["status"] ?? "") === "success"): ?>

    <div class="success-message" style="
        padding:16px;
        margin-bottom:20px;
        border-radius:10px;
        background:#dcfce7;
        color:#166534;
        border:1px solid #86efac;
    ">

        <strong>Sale saved successfully!</strong>

        <br>

        Both Star and Snowflake databases have been updated.

        <br>

        Transaction ID:
        <?= htmlspecialchars($_GET["transaction"] ?? "") ?>

    </div>

<?php endif; ?>


<!-- ======================================== -->
<!-- FORM ERROR MESSAGE -->
<!-- ======================================== -->

<?php if ($formError !== null): ?>

    <div class="error-message" style="margin-bottom:20px;">

        <?= htmlspecialchars($formError) ?>

    </div>

<?php endif; ?>


<!-- ======================================== -->
<!-- DATABASE SELECTOR -->
<!-- ======================================== -->

<section class="dashboard-section">

    <div class="section-header">

        <div>

            <h2>Database Selection</h2>

            <p>
                Select a database to view its sales records.
            </p>

        </div>

    </div>


    <div style="display:flex; gap:12px; flex-wrap:wrap;">

        <a
            href="?page=sales&database=star"
            class="btn <?= $selectedDatabase === 'star' ? 'btn-primary' : '' ?>"
        >
            ⭐ Star Schema
        </a>


        <a
            href="?page=sales&database=snowflake"
            class="btn <?= $selectedDatabase === 'snowflake' ? 'btn-primary' : '' ?>"
        >
            ❄ Snowflake Schema
        </a>

    </div>

</section>


<?php if ($errorMessage !== null): ?>

    <div class="error-message">

        <?= htmlspecialchars($errorMessage) ?>

    </div>

<?php else: ?>


<!-- ======================================== -->
<!-- ADD NEW SALE FORM -->
<!-- ======================================== -->

<section class="dashboard-section">

    <div class="section-header">

        <div>

            <h2>Add New Sale</h2>

            <p>
                Enter transaction information using the form below.
            </p>

        </div>

        <span class="analytics-badge">
            New Transaction
        </span>

    </div>


    <form method="POST" id="salesForm">

        <input
            type="hidden"
            name="action"
            value="add_sale"
        >

        <div class="form-grid" style="
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:20px;
        ">


            <!-- DATE -->

            <div class="form-group">

                <label for="date">
                    Transaction Date
                </label>

                <select
                    name="date"
                    id="date"
                    class="form-control"
                    required
                >

                    <option value="">
                        Select Date
                    </option>

                    <?php foreach ($formOptions["dates"] as $date): ?>

                        <option value="<?= htmlspecialchars($date["full_date"]) ?>">

                            <?= htmlspecialchars($date["full_date"]) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- PRODUCT -->

            <div class="form-group">

                <label for="product">
                    Product
                </label>

                <select
                    name="product_id"
                    id="product"
                    class="form-control"
                    required
                >

                    <option value="">
                        Select Product
                    </option>

                    <?php foreach ($formOptions["products"] as $product): ?>

                        <option
                            value="<?= htmlspecialchars($product["product_id"]) ?>"
                            data-price="<?= htmlspecialchars((string) $product["current_unit_price"]) ?>"
                        >

                            <?= htmlspecialchars($product["product_name"]) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- CUSTOMER -->

            <div class="form-group">

                <label for="customer">
                    Customer
                </label>

                <select
                    name="customer_id"
                    id="customer"
                    class="form-control"
                    required
                >

                    <option value="">
                        Select Customer
                    </option>

                    <?php foreach ($formOptions["customers"] as $customer): ?>

                        <option value="<?= htmlspecialchars($customer["customer_id"]) ?>">

                            <?= htmlspecialchars($customer["customer_name"]) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- STORE -->

            <div class="form-group">

                <label for="store">
                    Store
                </label>

                <select
                    name="store_id"
                    id="store"
                    class="form-control"
                    required
                >

                    <option value="">
                        Select Store
                    </option>

                    <?php foreach ($formOptions["stores"] as $store): ?>

                        <option value="<?= htmlspecialchars($store["store_id"]) ?>">

                            <?= htmlspecialchars($store["store_name"]) ?>
                            (<?= htmlspecialchars($store["city"]) ?>)

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- PAYMENT -->

            <div class="form-group">

                <label for="payment">
                    Payment Method
                </label>

                <select
                    name="payment_method"
                    id="payment"
                    class="form-control"
                    required
                >

                    <option value="">
                        Select Payment Method
                    </option>

                    <?php foreach ($formOptions["payments"] as $payment): ?>

                        <option value="<?= htmlspecialchars($payment["payment_method"]) ?>">

                            <?= htmlspecialchars($payment["payment_method"]) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- QUANTITY -->

            <div class="form-group">

                <label for="quantity">
                    Quantity
                </label>

                <input
                    type="number"
                    name="quantity"
                    id="quantity"
                    class="form-control"
                    min="0.01"
                    step="0.01"
                    placeholder="Enter quantity"
                    required
                >

            </div>


            <!-- UNIT PRICE -->

            <div class="form-group">

                <label for="unitPrice">
                    Unit Price (৳)
                </label>

                <input
                    type="number"
                    name="unit_price"
                    id="unitPrice"
                    class="form-control"
                    min="0"
                    step="0.01"
                    placeholder="Enter unit price"
                    required
                >

            </div>


            <!-- UNIT COST -->

            <div class="form-group">

                <label for="unitCost">
                    Unit Cost (৳)
                </label>

                <input
                    type="number"
                    name="unit_cost"
                    id="unitCost"
                    class="form-control"
                    min="0"
                    step="0.01"
                    placeholder="Enter unit cost"
                    required
                >

            </div>


            <!-- DISCOUNT -->

            <div class="form-group">

                <label for="discount">
                    Discount (৳)
                </label>

                <input
                    type="number"
                    name="discount_amount"
                    id="discount"
                    class="form-control"
                    min="0"
                    step="0.01"
                    value="0"
                    required
                >

            </div>


        </div>


        <!-- CALCULATION PREVIEW -->

        <div style="
            margin-top:25px;
            padding:20px;
            background:var(--background);
            border:1px solid var(--border);
            border-radius:12px;
        ">

            <h3 style="margin-bottom:15px;">
                Sales Calculation Preview
            </h3>


            <div style="
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
                gap:15px;
            ">

                <div>

                    <p class="kpi-label">
                        Sales Amount
                    </p>

                    <h3 id="previewRevenue">
                        ৳0.00
                    </h3>

                </div>


                <div>

                    <p class="kpi-label">
                        Cost Amount
                    </p>

                    <h3 id="previewCost">
                        ৳0.00
                    </h3>

                </div>


                <div>

                    <p class="kpi-label">
                        Profit Amount
                    </p>

                    <h3 id="previewProfit">
                        ৳0.00
                    </h3>

                </div>

            </div>

        </div>


        <!-- SAVE BUTTON -->

        <div style="margin-top:20px;">

            <button
                type="submit"
                class="btn btn-primary"
                id="saveSaleButton"
            >

                <span id="saveButtonText">
                    Save Sale
                </span>

            </button>

        </div>

    </form>

</section>


<!-- ======================================== -->
<!-- SALES SUMMARY -->
<!-- ======================================== -->

<div class="kpi-grid">

    <div class="kpi-card">

        <div class="kpi-label">
            Total Sales Records
        </div>

        <div class="kpi-value">
            <?= number_format($totalRecords) ?>
        </div>

    </div>


    <div class="kpi-card">

        <div class="kpi-label">
            Total Revenue
        </div>

        <div class="kpi-value">
            <?= salesCurrency($totalRevenue) ?>
        </div>

    </div>


    <div class="kpi-card">

        <div class="kpi-label">
            Total Profit
        </div>

        <div class="kpi-value">
            <?= salesCurrency($totalProfit) ?>
        </div>

    </div>

</div>


<!-- ======================================== -->
<!-- SALES RECORDS TABLE -->
<!-- ======================================== -->

<section class="dashboard-section">

    <div class="section-header">

        <div>

            <h2>Sales Transactions</h2>

            <p>
                Showing records from <?= htmlspecialchars($databaseLabel) ?>.
            </p>

        </div>

        <span class="analytics-badge">
            <?= number_format($totalRecords) ?> Records
        </span>

    </div>


    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>Sales ID</th>
                    <th>Transaction ID</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Store</th>
                    <th>Payment</th>
                    <th>Quantity</th>
                    <th>Revenue</th>
                    <th>Profit</th>

                </tr>

            </thead>


            <tbody>

                <?php if (empty($salesRecords)): ?>

                    <tr>

                        <td colspan="10" style="text-align:center;">
                            No sales records found.
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($salesRecords as $record): ?>

                        <tr>

                            <td>
                                <?= (int) $record["sales_key"] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($record["transaction_id"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($record["full_date"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($record["product_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($record["customer_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($record["store_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($record["payment_method"]) ?>
                            </td>

                            <td>
                                <?= number_format((float) $record["quantity"], 2) ?>
                            </td>

                            <td>
                                <?= salesCurrency($record["sales_amount"]) ?>
                            </td>

                            <td>
                                <?= salesCurrency($record["profit_amount"]) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <div class="table-note">

        Sales records are loaded directly from the selected database.

        Changes made through phpMyAdmin will appear after refreshing this page.

    </div>

</section>


<?php endif; ?>


<!-- ======================================== -->
<!-- LIVE CALCULATION JAVASCRIPT -->
<!-- ======================================== -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    // ========================================
    // 1. SUCCESSFUL INSERT REDIRECT
    // ========================================

    <?php if ($redirectAfterSave): ?>

        const transactionId = <?= json_encode($newTransactionId) ?>;

        const redirectUrl =
            "?page=sales&database=<?= urlencode($selectedDatabase) ?>"
            + "&status=success"
            + "&transaction=" + encodeURIComponent(transactionId);

        window.location.replace(redirectUrl);

        return;

    <?php endif; ?>


    // ========================================
    // 2. FORM ELEMENTS
    // ========================================

    const form = document.getElementById("salesForm");

    const product = document.getElementById("product");

    const quantity = document.getElementById("quantity");

    const unitPrice = document.getElementById("unitPrice");

    const unitCost = document.getElementById("unitCost");

    const discount = document.getElementById("discount");


    const revenueOutput = document.getElementById("previewRevenue");

    const costOutput = document.getElementById("previewCost");

    const profitOutput = document.getElementById("previewProfit");


    // ========================================
    // 3. CURRENCY FORMAT
    // ========================================

    function formatCurrency(value) {

        return "৳" + value.toFixed(2);

    }


    // ========================================
    // 4. CALCULATE SALES
    // ========================================

    function calculateSales() {

        const qty = Number(quantity.value) || 0;

        const price = Number(unitPrice.value) || 0;

        const cost = Number(unitCost.value) || 0;

        const discountValue = Number(discount.value) || 0;


        const revenue = (qty * price) - discountValue;

        const totalCost = qty * cost;

        const profit = revenue - totalCost;


        revenueOutput.textContent = formatCurrency(revenue);

        costOutput.textContent = formatCurrency(totalCost);

        profitOutput.textContent = formatCurrency(profit);

    }


    // ========================================
    // 5. PRODUCT PRICE AUTO-FILL
    // ========================================

    product.addEventListener("change", function () {

        const selectedOption = product.options[product.selectedIndex];

        const price = selectedOption.dataset.price;


        if (price !== undefined && price !== "") {

            unitPrice.value = price;

        }


        calculateSales();

    });


    // ========================================
    // 6. LIVE INPUT EVENTS
    // ========================================

    quantity.addEventListener("input", calculateSales);

    unitPrice.addEventListener("input", calculateSales);

    unitCost.addEventListener("input", calculateSales);

    discount.addEventListener("input", calculateSales);


    // ========================================
    // 7. LOADING ANIMATION
    // ========================================

    if (form) {

        form.addEventListener("submit", function (event) {

            const saveButton = document.getElementById("saveSaleButton");

            const buttonText = document.getElementById("saveButtonText");


            // Prevent repeated submission.

            if (form.dataset.submitting === "true") {

                event.preventDefault();

                return;

            }


            // Native HTML validation runs before this submit event.
            // Therefore, invalid forms will not reach this point.

            form.dataset.submitting = "true";


            // Disable the button.

            saveButton.disabled = true;

            saveButton.setAttribute("aria-busy", "true");


            // Show loading spinner.

            buttonText.innerHTML =
                '<span class="loading-spinner"></span> Saving Sale...';


        });

    }

});

</script>