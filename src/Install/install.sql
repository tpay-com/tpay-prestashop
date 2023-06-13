CREATE TABLE IF NOT EXISTS `_DB_PREFIX_tpay_transaction` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT NULL,
    `crc` VARCHAR(255),
    `transaction_id` VARCHAR(255),
    `payment_type` VARCHAR(255),
    `register_user` INT NULL,
    `surcharge` DECIMAL(10, 2),
    `status` VARCHAR(255),
    PRIMARY KEY (`id`)
    ) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_tpay_refund` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT NULL,
    `transaction_id` VARCHAR(255),
    `date` DATETIME,
    `amount` DECIMAL(10, 2),
    PRIMARY KEY (`id`)
    ) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_tpay_credit_card` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `card_vendor` VARCHAR(255),
     `card_shortcode` VARCHAR(255),
     `card_hash` VARCHAR(255),
     `card_token` VARCHAR(255),
     `user_id` int(11) NOT NULL,
     `crc` VARCHAR(255),
     `date_add` DATETIME,
     `date_update` DATETIME,
     PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_tpay_blik` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT NULL,
      `alias` VARCHAR(255) NULL,
      PRIMARY KEY (`id`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;
