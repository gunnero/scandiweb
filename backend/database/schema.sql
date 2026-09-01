SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(64) NOT NULL,
    type_name VARCHAR(64) NOT NULL DEFAULT 'Category',
    position SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY categories_name_unique (name),
    KEY categories_position_index (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    public_id VARCHAR(64) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    product_type VARCHAR(32) NOT NULL,
    name VARCHAR(255) NOT NULL,
    in_stock TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    description MEDIUMTEXT NOT NULL,
    brand VARCHAR(255) NOT NULL,
    position SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY products_public_id_unique (public_id),
    KEY products_category_position_index (category_id, position),
    CONSTRAINT products_category_foreign
        FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_gallery (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    url TEXT NOT NULL,
    position SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY product_gallery_position_unique (product_id, position),
    CONSTRAINT product_gallery_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_prices (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    amount DECIMAL(12, 2) UNSIGNED NOT NULL,
    currency_label VARCHAR(8) NOT NULL,
    currency_symbol VARCHAR(8) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY product_prices_currency_unique (product_id, currency_label),
    CONSTRAINT product_prices_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_attribute_sets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    external_id VARCHAR(64) NOT NULL,
    name VARCHAR(128) NOT NULL,
    type VARCHAR(32) NOT NULL,
    position SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY product_attribute_sets_external_unique (product_id, external_id),
    KEY product_attribute_sets_position_index (product_id, position),
    CONSTRAINT product_attribute_sets_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_attribute_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    attribute_set_id INT UNSIGNED NOT NULL,
    external_id VARCHAR(64) NOT NULL,
    display_value VARCHAR(128) NOT NULL,
    value VARCHAR(128) NOT NULL,
    position SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY product_attribute_items_external_unique (attribute_set_id, external_id),
    KEY product_attribute_items_position_index (attribute_set_id, position),
    CONSTRAINT product_attribute_items_set_foreign
        FOREIGN KEY (attribute_set_id) REFERENCES product_attribute_sets (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    status VARCHAR(32) NOT NULL,
    total DECIMAL(12, 2) UNSIGNED NOT NULL,
    currency_label VARCHAR(8) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY orders_created_at_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_public_id VARCHAR(64) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    unit_price DECIMAL(12, 2) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY order_items_order_index (order_id),
    KEY order_items_product_index (product_id),
    CONSTRAINT order_items_order_foreign
        FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT order_items_product_foreign
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_item_attributes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_item_id BIGINT UNSIGNED NOT NULL,
    attribute_id VARCHAR(64) NOT NULL,
    attribute_name VARCHAR(128) NOT NULL,
    item_id VARCHAR(64) NOT NULL,
    item_display_value VARCHAR(128) NOT NULL,
    item_value VARCHAR(128) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY order_item_attributes_attribute_unique (order_item_id, attribute_id),
    CONSTRAINT order_item_attributes_item_foreign
        FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
