CREATE TABLE IF NOT EXISTS `UserProducts`
(
    `id`         int auto_increment not null,
    `user_id`    int,
    `product_id` int,
    `product_name` VARCHAR(32),
    `price` DECIMAL(7,2),
    `quantity` int,
    `is_active`  TINYINT(1) default 1,
    `created`    timestamp default current_timestamp,
    `modified`   timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES Users(`id`),
    FOREIGN KEY (`product_id`) REFERENCES Products(`id`),
    UNIQUE KEY (`user_id`, `product_id`),
    check (price > 0),
    check (quantity > 0)
)