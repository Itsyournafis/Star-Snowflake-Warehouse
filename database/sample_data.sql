-- =====================================================
-- SAMPLE DATA
-- STAR SCHEMA
-- =====================================================

USE supermarket_star;


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
-- 2. PRODUCT DIMENSION
-- =====================================================

INSERT INTO dim_product (
    product_id,
    product_name,
    brand,
    category,
    subcategory,
    unit_of_measure,
    current_unit_price
)
VALUES
('P001', 'Full Cream Milk 1L', 'Aarong Dairy', 'Dairy', 'Milk', 'Liter', 100.00),
('P002', 'Chocolate Bar', 'Cadbury', 'Snacks', 'Chocolate', 'Piece', 80.00),
('P003', 'Basmati Rice 1KG', 'Pran', 'Grocery', 'Rice', 'KG', 150.00),
('P004', 'Orange Juice 1L', 'Pran', 'Beverages', 'Juice', 'Liter', 180.00),
('P005', 'Potato Chips', 'Bombay Sweets', 'Snacks', 'Chips', 'Packet', 30.00);


-- =====================================================
-- 3. CUSTOMER DIMENSION
-- =====================================================

INSERT INTO dim_customer (
    customer_id,
    customer_name,
    gender,
    age_group,
    city,
    customer_segment
)
VALUES
('C001', 'Rahim Ahmed', 'Male', '18-25', 'Dhaka', 'Regular'),
('C002', 'Karim Hasan', 'Male', '26-35', 'Chattogram', 'Premium'),
('C003', 'Nusrat Jahan', 'Female', '18-25', 'Dhaka', 'Regular'),
('C004', 'Fatima Akter', 'Female', '36-45', 'Gazipur', 'Premium'),
('C005', 'Sabbir Hossain', 'Male', '26-35', 'Cumilla', 'Regular');


-- =====================================================
-- 4. STORE DIMENSION
-- =====================================================

INSERT INTO dim_store (
    store_id,
    store_name,
    store_type,
    city,
    state_or_division,
    country,
    opening_date
)
VALUES
('S001', 'Dhaka Central Store', 'Supermarket', 'Dhaka', 'Dhaka', 'Bangladesh', '2020-01-01'),
('S002', 'Chattogram City Store', 'Supermarket', 'Chattogram', 'Chattogram', 'Bangladesh', '2021-03-15'),
('S003', 'Gazipur Express Store', 'Express', 'Gazipur', 'Dhaka', 'Bangladesh', '2022-06-10');


-- =====================================================
-- 5. PAYMENT DIMENSION
-- =====================================================

INSERT INTO dim_payment (
    payment_method,
    payment_category,
    payment_description
)
VALUES
('Cash', 'Physical', 'Cash payment'),
('Credit Card', 'Card', 'Credit card payment'),
('Debit Card', 'Card', 'Debit card payment'),
('bKash', 'Mobile Banking', 'Mobile financial service payment');


-- =====================================================
-- END OF STAR SCHEMA SAMPLE DATA
-- =====================================================