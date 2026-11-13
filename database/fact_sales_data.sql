-- =====================================================
-- STAR vs SNOWFLAKE DATA WAREHOUSE
-- Sample Fact Sales Data
-- Same transactions for both schemas
-- =====================================================


-- =====================================================
-- PART 1: INSERT DATA INTO STAR SCHEMA
-- =====================================================

USE supermarket_star;

INSERT INTO fact_sales (
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

SELECT
    src.transaction_id,
    src.line_number,
    src.date_key,
    p.product_key,
    c.customer_key,
    s.store_key,
    pm.payment_key,
    src.quantity,
    src.unit_price,
    src.unit_cost,
    src.discount_amount,
    (src.quantity * src.unit_price) - src.discount_amount,
    src.quantity * src.unit_cost,
    ((src.quantity * src.unit_price) - src.discount_amount)
        - (src.quantity * src.unit_cost)

FROM (
    SELECT 'TXN001' AS transaction_id, 1 AS line_number, 20260101 AS date_key, 'P001' AS product_id, 'C001' AS customer_id, 'S001' AS store_id, 'Cash' AS payment_method, 2 AS quantity, 100.00 AS unit_price, 75.00 AS unit_cost, 10.00 AS discount_amount
    UNION ALL
    SELECT 'TXN002', 1, 20260101, 'P003', 'C002', 'S002', 'bKash', 1, 150.00, 120.00, 0.00
    UNION ALL
    SELECT 'TXN003', 1, 20260102, 'P002', 'C003', 'S001', 'Credit Card', 3, 80.00, 55.00, 15.00
    UNION ALL
    SELECT 'TXN004', 1, 20260102, 'P005', 'C004', 'S003', 'Cash', 5, 30.00, 18.00, 0.00
    UNION ALL
    SELECT 'TXN005', 1, 20260103, 'P004', 'C005', 'S002', 'Debit Card', 2, 180.00, 130.00, 20.00
    UNION ALL
    SELECT 'TXN006', 1, 20260103, 'P001', 'C002', 'S002', 'bKash', 1, 100.00, 75.00, 0.00
    UNION ALL
    SELECT 'TXN007', 1, 20260104, 'P003', 'C003', 'S001', 'Cash', 2, 150.00, 120.00, 10.00
    UNION ALL
    SELECT 'TXN008', 1, 20260104, 'P002', 'C001', 'S003', 'Credit Card', 2, 80.00, 55.00, 0.00
    UNION ALL
    SELECT 'TXN009', 1, 20260105, 'P004', 'C004', 'S003', 'Debit Card', 1, 180.00, 130.00, 10.00
    UNION ALL
    SELECT 'TXN010', 1, 20260105, 'P005', 'C005', 'S002', 'Cash', 4, 30.00, 18.00, 5.00
) AS src

JOIN dim_product p
    ON p.product_id = src.product_id

JOIN dim_customer c
    ON c.customer_id = src.customer_id

JOIN dim_store s
    ON s.store_id = src.store_id

JOIN dim_payment pm
    ON pm.payment_method = src.payment_method;


-- =====================================================
-- PART 2: INSERT SAME DATA INTO SNOWFLAKE SCHEMA
-- =====================================================

USE supermarket_snowflake;

INSERT INTO fact_sales (
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

SELECT
    src.transaction_id,
    src.line_number,
    src.date_key,
    p.product_key,
    c.customer_key,
    s.store_key,
    pm.payment_key,
    src.quantity,
    src.unit_price,
    src.unit_cost,
    src.discount_amount,
    (src.quantity * src.unit_price) - src.discount_amount,
    src.quantity * src.unit_cost,
    ((src.quantity * src.unit_price) - src.discount_amount)
        - (src.quantity * src.unit_cost)

FROM (
    SELECT 'TXN001' AS transaction_id, 1 AS line_number, 20260101 AS date_key, 'P001' AS product_id, 'C001' AS customer_id, 'S001' AS store_id, 'Cash' AS payment_method, 2 AS quantity, 100.00 AS unit_price, 75.00 AS unit_cost, 10.00 AS discount_amount
    UNION ALL
    SELECT 'TXN002', 1, 20260101, 'P003', 'C002', 'S002', 'bKash', 1, 150.00, 120.00, 0.00
    UNION ALL
    SELECT 'TXN003', 1, 20260102, 'P002', 'C003', 'S001', 'Credit Card', 3, 80.00, 55.00, 15.00
    UNION ALL
    SELECT 'TXN004', 1, 20260102, 'P005', 'C004', 'S003', 'Cash', 5, 30.00, 18.00, 0.00
    UNION ALL
    SELECT 'TXN005', 1, 20260103, 'P004', 'C005', 'S002', 'Debit Card', 2, 180.00, 130.00, 20.00
    UNION ALL
    SELECT 'TXN006', 1, 20260103, 'P001', 'C002', 'S002', 'bKash', 1, 100.00, 75.00, 0.00
    UNION ALL
    SELECT 'TXN007', 1, 20260104, 'P003', 'C003', 'S001', 'Cash', 2, 150.00, 120.00, 10.00
    UNION ALL
    SELECT 'TXN008', 1, 20260104, 'P002', 'C001', 'S003', 'Credit Card', 2, 80.00, 55.00, 0.00
    UNION ALL
    SELECT 'TXN009', 1, 20260105, 'P004', 'C004', 'S003', 'Debit Card', 1, 180.00, 130.00, 10.00
    UNION ALL
    SELECT 'TXN010', 1, 20260105, 'P005', 'C005', 'S002', 'Cash', 4, 30.00, 18.00, 5.00
) AS src

JOIN dim_product p
    ON p.product_id = src.product_id

JOIN dim_customer c
    ON c.customer_id = src.customer_id

JOIN dim_store s
    ON s.store_id = src.store_id

JOIN dim_payment pm
    ON pm.payment_method = src.payment_method;
    