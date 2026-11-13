-- =====================================================
-- PROJECT: Star vs Snowflake Data Warehouse
-- DATABASE: supermarket_snowflake
-- SCHEMA: SNOWFLAKE SCHEMA
-- =====================================================

USE supermarket_snowflake;


-- =====================================================
-- 1. DATE DIMENSION
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
-- 2. CATEGORY DIMENSION
-- =====================================================

CREATE TABLE dim_category (
    category_key INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description VARCHAR(255)
) ENGINE=InnoDB;


-- =====================================================
-- 3. SUBCATEGORY DIMENSION
-- =====================================================

CREATE TABLE dim_subcategory (
    subcategory_key INT AUTO_INCREMENT PRIMARY KEY,
    subcategory_name VARCHAR(100) NOT NULL,
    category_key INT NOT NULL,

    CONSTRAINT uq_subcategory
        UNIQUE (subcategory_name, category_key),

    CONSTRAINT fk_subcategory_category
        FOREIGN KEY (category_key)
        REFERENCES dim_category(category_key)
) ENGINE=InnoDB;


-- =====================================================
-- 4. BRAND DIMENSION
-- =====================================================

CREATE TABLE dim_brand (
    brand_key INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL UNIQUE,
    brand_description VARCHAR(255)
) ENGINE=InnoDB;


-- =====================================================
-- 5. PRODUCT DIMENSION
-- =====================================================

CREATE TABLE dim_product (
    product_key INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(30) NOT NULL UNIQUE,
    product_name VARCHAR(150) NOT NULL,

    brand_key INT,
    subcategory_key INT NOT NULL,

    unit_of_measure VARCHAR(30),
    current_unit_price DECIMAL(12,2),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT fk_product_brand
        FOREIGN KEY (brand_key)
        REFERENCES dim_brand(brand_key),

    CONSTRAINT fk_product_subcategory
        FOREIGN KEY (subcategory_key)
        REFERENCES dim_subcategory(subcategory_key)
) ENGINE=InnoDB;


-- =====================================================
-- 6. CITY DIMENSION
-- =====================================================

CREATE TABLE dim_city (
    city_key INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    state_or_division VARCHAR(100),
    country VARCHAR(100) NOT NULL,

    CONSTRAINT uq_city
        UNIQUE (city_name, state_or_division, country)
) ENGINE=InnoDB;


-- =====================================================
-- 7. CUSTOMER DIMENSION
-- =====================================================

CREATE TABLE dim_customer (
    customer_key INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(30) NOT NULL UNIQUE,
    customer_name VARCHAR(120) NOT NULL,

    gender VARCHAR(20),
    age_group VARCHAR(30),

    city_key INT,
    customer_segment VARCHAR(50),

    CONSTRAINT fk_customer_city
        FOREIGN KEY (city_key)
        REFERENCES dim_city(city_key)
) ENGINE=InnoDB;


-- =====================================================
-- 8. STORE DIMENSION
-- =====================================================

CREATE TABLE dim_store (
    store_key INT AUTO_INCREMENT PRIMARY KEY,
    store_id VARCHAR(30) NOT NULL UNIQUE,
    store_name VARCHAR(120) NOT NULL,

    store_type VARCHAR(50),
    city_key INT NOT NULL,
    opening_date DATE,

    CONSTRAINT fk_store_city
        FOREIGN KEY (city_key)
        REFERENCES dim_city(city_key)
) ENGINE=InnoDB;


-- =====================================================
-- 9. PAYMENT CATEGORY DIMENSION
-- =====================================================

CREATE TABLE dim_payment_category (
    payment_category_key INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    category_description VARCHAR(150)
) ENGINE=InnoDB;


-- =====================================================
-- 10. PAYMENT DIMENSION
-- =====================================================

CREATE TABLE dim_payment (
    payment_key INT AUTO_INCREMENT PRIMARY KEY,
    payment_method VARCHAR(50) NOT NULL UNIQUE,

    payment_category_key INT NOT NULL,
    payment_description VARCHAR(150),

    CONSTRAINT fk_payment_category
        FOREIGN KEY (payment_category_key)
        REFERENCES dim_payment_category(payment_category_key)
) ENGINE=InnoDB;


-- =====================================================
-- 11. SALES FACT TABLE
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

    CONSTRAINT fk_snow_sales_date
        FOREIGN KEY (date_key)
        REFERENCES dim_date(date_key),

    CONSTRAINT fk_snow_sales_product
        FOREIGN KEY (product_key)
        REFERENCES dim_product(product_key),

    CONSTRAINT fk_snow_sales_customer
        FOREIGN KEY (customer_key)
        REFERENCES dim_customer(customer_key),

    CONSTRAINT fk_snow_sales_store
        FOREIGN KEY (store_key)
        REFERENCES dim_store(store_key),

    CONSTRAINT fk_snow_sales_payment
        FOREIGN KEY (payment_key)
        REFERENCES dim_payment(payment_key),

    CONSTRAINT chk_snow_quantity
        CHECK (quantity > 0),

    CONSTRAINT chk_snow_unit_price
        CHECK (unit_price >= 0),

    CONSTRAINT chk_snow_unit_cost
        CHECK (unit_cost >= 0),

    CONSTRAINT chk_snow_discount
        CHECK (discount_amount >= 0),

    CONSTRAINT chk_snow_sales_amount
        CHECK (sales_amount >= 0),

    CONSTRAINT chk_snow_cost_amount
        CHECK (cost_amount >= 0)
) ENGINE=InnoDB;


-- =====================================================
-- INDEXES FOR ANALYTICAL QUERIES
-- =====================================================

CREATE INDEX idx_snow_sales_date
ON fact_sales(date_key);

CREATE INDEX idx_snow_sales_product
ON fact_sales(product_key);

CREATE INDEX idx_snow_sales_customer
ON fact_sales(customer_key);

CREATE INDEX idx_snow_sales_store
ON fact_sales(store_key);

CREATE INDEX idx_snow_sales_payment
ON fact_sales(payment_key);

CREATE INDEX idx_snow_sales_transaction
ON fact_sales(transaction_id);

CREATE INDEX idx_product_subcategory
ON dim_product(subcategory_key);

CREATE INDEX idx_subcategory_category
ON dim_subcategory(category_key);

CREATE INDEX idx_customer_city
ON dim_customer(city_key);

CREATE INDEX idx_store_city
ON dim_store(city_key);


-- =====================================================
-- END OF SNOWFLAKE SCHEMA
-- =====================================================