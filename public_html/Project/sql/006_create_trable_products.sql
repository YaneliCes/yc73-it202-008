CREATE TABLE IF NOT EXISTS `Products` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `api_id` VARCHAR(20) UNIQUE,
    `name` VARCHAR(32),
    `price` DECIMAL(7,2),
    `measurement` VARCHAR(75),
    `typeName` VARCHAR(100),
    `image` TEXT,
    `contextualImageUrl` TEXT,
    `imageAlt` TEXT,
    `url` TEXT,
    `categoryPath` VARCHAR(100),
    `stock` INT DEFAULT 0,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    check (stock >= 0),
    check (price >= 0)
)
