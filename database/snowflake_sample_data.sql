-- =====================================================
-- PROJECT: Star vs Snowflake Data Warehouse
-- SAMPLE DATA: SNOWFLAKE SCHEMA
-- =====================================================

USE supermarket_snowflake;


-- =====================================================
-- 1. DATE DIMENSION
-- =====================================================

INSERT INTO dim_date (
    date_key,
    full_date,
    day_number,
    month_number,
    month_name,
    quarter_number,
    year_number,
    day_name,
    is_weekend
)
VALUES
(20260101, '2026-01-01', 1, 1, 'January', 1, 2026, 'Thursday', 0),
(20260102, '2026-01-02', 2, 1, 'January', 1, 2026, 'Friday', 1),
(20260103, '2026-01-03', 3, 1, 'January', 1, 2026, 'Saturday', 1),
(20260104, '2026-01-04', 4, 1, 'January', 1, 2026, 'Sunday', 0),
(20260105, '2026-01-05', 5, 1, 'January', 1, 2026, 'Monday', 0);


-- =====================================================
-- 2. CATEGORY DIMENSION
-- =====================================================

INSERT INTO dim_category (
    category_name,
    category_description
)
VALUES
('Dairy', 'Milk and dairy products'),
('Snacks', 'Snacks and confectionery'),
('Grocery', 'Daily grocery products'),
('Beverages', 'Drinks and beverages');


-- =====================================================
-- 3. SUBCATEGORY DIMENSION
-- =====================================================

INSERT INTO dim_subcategory (
    subcategory_name,
    category_key
)
SELECT 'Milk', category_key
FROM dim_category
WHERE category_name = 'Dairy';

INSERT INTO dim_subcategory (
    subcategory_name,
    category_key
)
SELECT 'Chocolate', category_key
FROM dim_category
WHERE category_name = 'Snacks';

INSERT INTO dim_subcategory (
    subcategory_name,
    category_key
)
SELECT 'Chips', category_key
FROM dim_category
WHERE category_name = 'Snacks';

INSERT INTO dim_subcategory (
    subcategory_name,
    category_key
)
SELECT 'Rice', category_key
FROM dim_category
WHERE category_name = 'Grocery';

INSERT INTO dim_subcategory (
    subcategory_name,
    category_key
)
SELECT 'Juice', category_key
FROM dim_category
WHERE category_name = 'Beverages';


-- =====================================================
-- 4. BRAND DIMENSION
-- =====================================================

INSERT INTO dim_brand (
    brand_name,
    brand_description
)
VALUES
('Aarong Dairy', 'Dairy product brand'),
('Cadbury', 'Chocolate brand'),
('Pran', 'Food and beverage brand'),
('Bombay Sweets', 'Snack food brand');


-- =====================================================
-- 5. PRODUCT DIMENSION
-- =====================================================

INSERT INTO dim_product (
    product_id,
    product_name,
    brand_key,
    subcategory_key,
    unit_of_measure,
    current_unit_price
)
SELECT
    'P001',
    'Full Cream Milk 1L',
    b.brand_key,
    s.subcategory_key,
    'Liter',
    100.00
FROM dim_brand b
JOIN dim_subcategory s
    ON s.subcategory_name = 'Milk'
JOIN dim_category c
    ON c.category_key = s.category_key
WHERE b.brand_name = 'Aarong Dairy'
  AND c.category_name = 'Dairy';


INSERT INTO dim_product (
    product_id,
    product_name,
    brand_key,
    subcategory_key,
    unit_of_measure,
    current_unit_price
)
SELECT
    'P002',
    'Chocolate Bar',
    b.brand_key,
    s.subcategory_key,
    'Piece',
    80.00
FROM dim_brand b
JOIN dim_subcategory s
    ON s.subcategory_name = 'Chocolate'
JOIN dim_category c
    ON c.category_key = s.category_key
WHERE b.brand_name = 'Cadbury'
  AND c.category_name = 'Snacks';


INSERT INTO dim_product (
    product_id,
    product_name,
    brand_key,
    subcategory_key,
    unit_of_measure,
    current_unit_price
)
SELECT
    'P003',
    'Basmati Rice 1KG',
    b.brand_key,
    s.subcategory_key,
    'KG',
    150.00
FROM dim_brand b
JOIN dim_subcategory s
    ON s.subcategory_name = 'Rice'
JOIN dim_category c
    ON c.category_key = s.category_key
WHERE b.brand_name = 'Pran'
  AND c.category_name = 'Grocery';


INSERT INTO dim_product (
    product_id,
    product_name,
    brand_key,
    subcategory_key,
    unit_of_measure,
    current_unit_price
)
SELECT
    'P004',
    'Orange Juice 1L',
    b.brand_key,
    s.subcategory_key,
    'Liter',
    180.00
FROM dim_brand b
JOIN dim_subcategory s
    ON s.subcategory_name = 'Juice'
JOIN dim_category c
    ON c.category_key = s.category_key
WHERE b.brand_name = 'Pran'
  AND c.category_name = 'Beverages';


INSERT INTO dim_product (
    product_id,
    product_name,
    brand_key,
    subcategory_key,
    unit_of_measure,
    current_unit_price
)
SELECT
    'P005',
    'Potato Chips',
    b.brand_key,
    s.subcategory_key,
    'Packet',
    30.00
FROM dim_brand b
JOIN dim_subcategory s
    ON s.subcategory_name = 'Chips'
JOIN dim_category c
    ON c.category_key = s.category_key
WHERE b.brand_name = 'Bombay Sweets'
  AND c.category_name = 'Snacks';


-- =====================================================
-- 6. CITY DIMENSION
-- =====================================================

INSERT INTO dim_city (
    city_name,
    state_or_division,
    country
)
VALUES
('Dhaka', 'Dhaka', 'Bangladesh'),
('Chattogram', 'Chattogram', 'Bangladesh'),
('Gazipur', 'Dhaka', 'Bangladesh'),
('Cumilla', 'Chattogram', 'Bangladesh');


-- =====================================================
-- 7. CUSTOMER DIMENSION
-- =====================================================

INSERT INTO dim_customer (
    customer_id,
    customer_name,
    gender,
    age_group,
    city_key,
    customer_segment
)
SELECT
    'C001', 'Rahim Ahmed', 'Male', '18-25',
    city_key, 'Regular'
FROM dim_city
WHERE city_name = 'Dhaka'
AND state_or_division = 'Dhaka'
AND country = 'Bangladesh';


INSERT INTO dim_customer (
    customer_id,
    customer_name,
    gender,
    age_group,
    city_key,
    customer_segment
)
SELECT
    'C002', 'Karim Hasan', 'Male', '26-35',
    city_key, 'Premium'
FROM dim_city
WHERE city_name = 'Chattogram'
AND state_or_division = 'Chattogram'
AND country = 'Bangladesh';


INSERT INTO dim_customer (
    customer_id,
    customer_name,
    gender,
    age_group,
    city_key,
    customer_segment
)
SELECT
    'C003', 'Nusrat Jahan', 'Female', '18-25',
    city_key, 'Regular'
FROM dim_city
WHERE city_name = 'Dhaka'
AND state_or_division = 'Dhaka'
AND country = 'Bangladesh';


INSERT INTO dim_customer (
    customer_id,
    customer_name,
    gender,
    age_group,
    city_key,
    customer_segment
)
SELECT
    'C004', 'Fatima Akter', 'Female', '36-45',
    city_key, 'Premium'
FROM dim_city
WHERE city_name = 'Gazipur'
AND state_or_division = 'Dhaka'
AND country = 'Bangladesh';


INSERT INTO dim_customer (
    customer_id,
    customer_name,
    gender,
    age_group,
    city_key,
    customer_segment
)
SELECT
    'C005', 'Sabbir Hossain', 'Male', '26-35',
    city_key, 'Regular'
FROM dim_city
WHERE city_name = 'Cumilla'
AND state_or_division = 'Chattogram'
AND country = 'Bangladesh';


-- =====================================================
-- 8. STORE DIMENSION
-- =====================================================

INSERT INTO dim_store (
    store_id,
    store_name,
    store_type,
    city_key,
    opening_date
)
SELECT
    'S001', 'Dhaka Central Store',
    'Supermarket', city_key, '2020-01-01'
FROM dim_city
WHERE city_name = 'Dhaka'
AND state_or_division = 'Dhaka'
AND country = 'Bangladesh';


INSERT INTO dim_store (
    store_id,
    store_name,
    store_type,
    city_key,
    opening_date
)
SELECT
    'S002', 'Chattogram City Store',
    'Supermarket', city_key, '2021-03-15'
FROM dim_city
WHERE city_name = 'Chattogram'
AND state_or_division = 'Chattogram'
AND country = 'Bangladesh';


INSERT INTO dim_store (
    store_id,
    store_name,
    store_type,
    city_key,
    opening_date
)
SELECT
    'S003', 'Gazipur Express Store',
    'Express', city_key, '2022-06-10'
FROM dim_city
WHERE city_name = 'Gazipur'
AND state_or_division = 'Dhaka'
AND country = 'Bangladesh';


-- =====================================================
-- 9. PAYMENT CATEGORY DIMENSION
-- =====================================================

INSERT INTO dim_payment_category (
    category_name,
    category_description
)
VALUES
('Physical', 'Physical cash payment'),
('Card', 'Bank card payment'),
('Mobile Banking', 'Mobile financial service');


-- =====================================================
-- 10. PAYMENT DIMENSION
-- =====================================================

INSERT INTO dim_payment (
    payment_method,
    payment_category_key,
    payment_description
)
SELECT
    'Cash', payment_category_key,
    'Cash payment'
FROM dim_payment_category
WHERE category_name = 'Physical';


INSERT INTO dim_payment (
    payment_method,
    payment_category_key,
    payment_description
)
SELECT
    'Credit Card', payment_category_key,
    'Credit card payment'
FROM dim_payment_category
WHERE category_name = 'Card';


INSERT INTO dim_payment (
    payment_method,
    payment_category_key,
    payment_description
)
SELECT
    'Debit Card', payment_category_key,
    'Debit card payment'
FROM dim_payment_category
WHERE category_name = 'Card';


INSERT INTO dim_payment (
    payment_method,
    payment_category_key,
    payment_description
)
SELECT
    'bKash', payment_category_key,
    'Mobile financial service payment'
FROM dim_payment_category
WHERE category_name = 'Mobile Banking';


-- =====================================================
-- END OF SNOWFLAKE SAMPLE DATA
-- =====================================================