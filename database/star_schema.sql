-- =====================================================
-- PROJECT: Star vs Snowflake Data Warehouse
-- DATABASE: supermarket_star
-- SCHEMA: STAR SCHEMA
-- =====================================================

-- Use the Star Schema database
USE supermarket_star;

-- =====================================================
-- 1. DIMENSION TABLE: DATE
-- =====================================================

CREATE TABLE dim_date (
    date_key INT PRIMARY KEY,
    full_date DATE NOT NULL UNIQUE,
    day_number TINYINT NOT NULL,
    month_number TINYINT NOT NULL,
    month_name VARCHAR(20) NOT NULL,
    quarter_number TINYINT NOT NULL,
    year_number SMALLINT NOT NULL,
    day_name VARCHAR(20) NOT NULL,
    is_weekend BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB;


-- =====================================================
-- 2. DIMENSION TABLE: PRODUCT
-- =====================================================

CREATE TABLE dim_product (
    product_key INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(30) NOT NULL UNIQUE,
    product_name VARCHAR(150) NOT NULL,
    brand VARCHAR(100),
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    unit_of_measure VARCHAR(30),
    current_unit_price DECIMAL(12,2),
    is_active BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;


-- =====================================================
-- 3. DIMENSION TABLE: CUSTOMER
-- =====================================================

CREATE TABLE dim_customer (
    customer_key INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(30) NOT NULL UNIQUE,
    customer_name VARCHAR(120) NOT NULL,
    gender VARCHAR(20),
    age_group VARCHAR(30),
    city VARCHAR(100),
    customer_segment VARCHAR(50)
) ENGINE=InnoDB;


-- =====================================================
-- 4. DIMENSION TABLE: STORE
-- =====================================================

CREATE TABLE dim_store (
    store_key INT AUTO_INCREMENT PRIMARY KEY,
    store_id VARCHAR(30) NOT NULL UNIQUE,
    store_name VARCHAR(120) NOT NULL,
    store_type VARCHAR(50),
    city VARCHAR(100) NOT NULL,
    state_or_division VARCHAR(100),
    country VARCHAR(100) NOT NULL,
    opening_date DATE
) ENGINE=InnoDB;


-- =====================================================
-- 5. DIMENSION TABLE: PAYMENT
-- =====================================================

CREATE TABLE dim_payment (
    payment_key INT AUTO_INCREMENT PRIMARY KEY,
    payment_method VARCHAR(50) NOT NULL UNIQUE,
    payment_category VARCHAR(50),
    payment_description VARCHAR(150)
) ENGINE=InnoDB;


-- =====================================================
-- 6. FACT TABLE: SALES
-- =====================================================

CREATE TABLE fact_sales (
    sales_key BIGINT AUTO_INCREMENT PRIMARY KEY,

    transaction_id VARCHAR(50) NOT NULL,
    line_number INT NOT NULL,

    date_key INT NOT NULL,
    product_key INT NOT NULL,
    customer_key INT NOT NULL,
    store_key INT NOT NULL,
    payment_key INT NOT NULL,

    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL,

    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    sales_amount DECIMAL(14,2) NOT NULL,
    cost_amount DECIMAL(14,2) NOT NULL,
    profit_amount DECIMAL(14,2) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_transaction_line
        UNIQUE (transaction_id, line_number),

    CONSTRAINT fk_sales_date
        FOREIGN KEY (date_key)
        REFERENCES dim_date(date_key),

    CONSTRAINT fk_sales_product
        FOREIGN KEY (product_key)
        REFERENCES dim_product(product_key),

    CONSTRAINT fk_sales_customer
        FOREIGN KEY (customer_key)
        REFERENCES dim_customer(customer_key),

    CONSTRAINT fk_sales_store
        FOREIGN KEY (store_key)
        REFERENCES dim_store(store_key),

    CONSTRAINT fk_sales_payment
        FOREIGN KEY (payment_key)
        REFERENCES dim_payment(payment_key),

    CONSTRAINT chk_quantity
        CHECK (quantity > 0),

    CONSTRAINT chk_unit_price
        CHECK (unit_price >= 0),

    CONSTRAINT chk_unit_cost
        CHECK (unit_cost >= 0),

    CONSTRAINT chk_discount
        CHECK (discount_amount >= 0),

    CONSTRAINT chk_sales_amount
        CHECK (sales_amount >= 0),

    CONSTRAINT chk_cost_amount
        CHECK (cost_amount >= 0)
) ENGINE=InnoDB;


-- =====================================================
-- INDEXES FOR ANALYTICAL QUERIES
-- =====================================================

CREATE INDEX idx_sales_date
ON fact_sales(date_key);

CREATE INDEX idx_sales_product
ON fact_sales(product_key);

CREATE INDEX idx_sales_customer
ON fact_sales(customer_key);

CREATE INDEX idx_sales_store
ON fact_sales(store_key);

CREATE INDEX idx_sales_payment
ON fact_sales(payment_key);

CREATE INDEX idx_sales_transaction
ON fact_sales(transaction_id);


-- =====================================================
-- END OF STAR SCHEMA
-- =====================================================