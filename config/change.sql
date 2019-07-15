ALTER TABLE `order_items` ADD `server_item_id` INT NULL AFTER `is_status_updated`;
ALTER TABLE `order_items` ADD `is_status_sync` BOOLEAN NULL DEFAULT FALSE AFTER `is_status_updated`;
ALTER TABLE `pharmacies` CHANGE `pharmacy_shop_branch_name` `pharmacy_shop_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `pharmacies` CHANGE `pharmacy_shop_branch_licence_no` `pharmacy_shop_licence_no` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `orders` ADD `server_order_id` INT NULL AFTER `is_sync`;
