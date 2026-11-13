<?php

declare(strict_types=1);

require_once __DIR__ . "/../config/database.php";


/**
 * Find dimension keys from a selected database.
 */
function getSaleDimensionKeys(
    PDO $pdo,
    array $data,
    string $databaseName
): array {

    $allowedDatabases = [
        "supermarket_star",
        "supermarket_snowflake"
    ];

    if (!in_array($databaseName, $allowedDatabases, true)) {
        throw new InvalidArgumentException("Invalid database name.");
    }

    $database = "`" . $databaseName . "`";

    $queries = [

        "date" => [
            "SELECT date_key FROM {$database}.dim_date WHERE full_date = ?",
            $data["date"]
        ],

        "product" => [
            "SELECT product_key FROM {$database}.dim_product
             WHERE product_id = ? AND is_active = 1",
            $data["product_id"]
        ],

        "customer" => [
            "SELECT customer_key FROM {$database}.dim_customer
             WHERE customer_id = ?",
            $data["customer_id"]
        ],

        "store" => [
            "SELECT store_key FROM {$database}.dim_store
             WHERE store_id = ?",
            $data["store_id"]
        ],

        "payment" => [
            "SELECT payment_key FROM {$database}.dim_payment
             WHERE payment_method = ?",
            $data["payment_method"]
        ]

    ];

    $keys = [];

    foreach ($queries as $name => $queryData) {

        $stmt = $pdo->prepare($queryData[0]);

        $stmt->execute([$queryData[1]]);

        $key = $stmt->fetchColumn();

        if ($key === false) {
            throw new RuntimeException(
                ucfirst($name) . " was not found in " . $databaseName . "."
            );
        }

        $keys[$name] = (int) $key;
    }

    return $keys;
}


/**
 * Insert one sale into both databases
 * using a single PDO transaction.
 */
function insertSaleIntoBothDatabases(array $input): array
{

    // ========================================
    // 1. VALIDATE INPUT
    // ========================================

    $requiredFields = [
        "date",
        "product_id",
        "customer_id",
        "store_id",
        "payment_method",
        "quantity",
        "unit_price",
        "unit_cost",
        "discount_amount"
    ];

    foreach ($requiredFields as $field) {

        if (
            !isset($input[$field]) ||
            trim((string) $input[$field]) === ""
        ) {
            throw new InvalidArgumentException(
                "Please provide " . str_replace("_", " ", $field) . "."
            );
        }
    }


    // ========================================
    // 2. VALIDATE DATE
    // ========================================

    $date = trim((string) $input["date"]);

    $dateObject = DateTime::createFromFormat("!Y-m-d", $date);

    if (
        !$dateObject ||
        $dateObject->format("Y-m-d") !== $date
    ) {
        throw new InvalidArgumentException("Invalid transaction date.");
    }


    // ========================================
    // 3. VALIDATE NUMERIC VALUES
    // ========================================

    $quantity = filter_var($input["quantity"], FILTER_VALIDATE_FLOAT);
    $unitPrice = filter_var($input["unit_price"], FILTER_VALIDATE_FLOAT);
    $unitCost = filter_var($input["unit_cost"], FILTER_VALIDATE_FLOAT);
    $discount = filter_var($input["discount_amount"], FILTER_VALIDATE_FLOAT);

    if (
        $quantity === false ||
        $unitPrice === false ||
        $unitCost === false ||
        $discount === false
    ) {
        throw new InvalidArgumentException(
            "Invalid numeric sales information."
        );
    }

    if (
        !is_finite($quantity) ||
        !is_finite($unitPrice) ||
        !is_finite($unitCost) ||
        !is_finite($discount)
    ) {
        throw new InvalidArgumentException(
            "Numeric values must be finite."
        );
    }

    if (
        $quantity <= 0 ||
        $unitPrice < 0 ||
        $unitCost < 0 ||
        $discount < 0
    ) {
        throw new InvalidArgumentException(
            "Invalid quantity, price, cost, or discount."
        );
    }


    // ========================================
    // 4. CALCULATE SALES AMOUNTS
    // ========================================

    $grossAmount = $quantity * $unitPrice;

    if ($discount > $grossAmount) {
        throw new InvalidArgumentException(
            "Discount cannot exceed the gross sales amount."
        );
    }

    $salesAmount = round($grossAmount - $discount, 2);
    $costAmount = round($quantity * $unitCost, 2);
    $profitAmount = round($salesAmount - $costAmount, 2);


    // ========================================
    // 5. GENERATE TRANSACTION ID
    // ========================================

    $transactionId = "TXN"
        . date("YmdHis")
        . strtoupper(bin2hex(random_bytes(4)));

    $lineNumber = 1;


    // ========================================
    // 6. DATABASE CONFIGURATION
    // ========================================

    $databases = [
        "supermarket_star",
        "supermarket_snowflake"
    ];

    // One connection for both databases.
    $pdo = getDatabaseConnection("supermarket_star");


    try {

        // ========================================
        // 7. START TRANSACTION
        // ========================================

        $pdo->beginTransaction();


        // ========================================
        // 8. INSERT INTO BOTH DATABASES
        // ========================================

        foreach ($databases as $databaseName) {

            $keys = getSaleDimensionKeys(
                $pdo,
                [
                    "date" => $date,
                    "product_id" => trim((string) $input["product_id"]),
                    "customer_id" => trim((string) $input["customer_id"]),
                    "store_id" => trim((string) $input["store_id"]),
                    "payment_method" => trim((string) $input["payment_method"])
                ],
                $databaseName
            );

            $database = "`" . $databaseName . "`";

            $sql = "
                INSERT INTO {$database}.fact_sales (
                    transaction_id,
                    line_number,
                    date_key,
                    product_key,
                    customer_key,
                    store_key,
                    payment_key,
                    quantity,
                    unit_price,
                    unit_cost,
                    discount_amount,
                    sales_amount,
                    cost_amount,
                    profit_amount
                )
                VALUES (
                    :transaction_id,
                    :line_number,
                    :date_key,
                    :product_key,
                    :customer_key,
                    :store_key,
                    :payment_key,
                    :quantity,
                    :unit_price,
                    :unit_cost,
                    :discount_amount,
                    :sales_amount,
                    :cost_amount,
                    :profit_amount
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":transaction_id" => $transactionId,
                ":line_number" => $lineNumber,
                ":date_key" => $keys["date"],
                ":product_key" => $keys["product"],
                ":customer_key" => $keys["customer"],
                ":store_key" => $keys["store"],
                ":payment_key" => $keys["payment"],
                ":quantity" => $quantity,
                ":unit_price" => $unitPrice,
                ":unit_cost" => $unitCost,
                ":discount_amount" => $discount,
                ":sales_amount" => $salesAmount,
                ":cost_amount" => $costAmount,
                ":profit_amount" => $profitAmount
            ]);

        }


        // ========================================
        // 9. COMMIT TRANSACTION
        // ========================================

        $pdo->commit();


        // ========================================
        // 10. RETURN RESULT
        // ========================================

        return [
            "success" => true,
            "transaction_id" => $transactionId,
            "sales_amount" => $salesAmount,
            "cost_amount" => $costAmount,
            "profit_amount" => $profitAmount
        ];


    } catch (Throwable $e) {

        // ========================================
        // 11. ROLLBACK ON FAILURE
        // ========================================

        if ($pdo->inTransaction()) {

            try {
                $pdo->rollBack();
            } catch (Throwable $rollbackError) {
                // Preserve original error.
            }

        }

        throw new RuntimeException(
            "Sales insertion failed: " . $e->getMessage(),
            0,
            $e
        );

    }

}