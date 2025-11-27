CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    variable_symbol VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    items TEXT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    shipping VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
