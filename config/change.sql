ALTER TABLE `order_items` ADD `server_item_id` INT NULL AFTER `is_status_updated`;
ALTER TABLE `order_items` ADD `is_status_sync` BOOLEAN NULL DEFAULT FALSE AFTER `is_status_updated`;
ALTER TABLE `pharmacies` CHANGE `pharmacy_shop_branch_name` `pharmacy_shop_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `pharmacies` CHANGE `pharmacy_shop_branch_licence_no` `pharmacy_shop_licence_no` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `orders` ADD `server_order_id` INT NULL AFTER `is_sync`;

ALTER TABLE `dgdasp`.`orders`
CHANGE COLUMN `total_amount` `total_amount` FLOAT(15,2) NULL DEFAULT '0.00' ,
CHANGE COLUMN `total_payble_amount` `total_payble_amount` FLOAT(15,2) NULL DEFAULT '0.00' ,
CHANGE COLUMN `total_advance_amount` `total_advance_amount` FLOAT(15,2) NULL DEFAULT '0.00' ;

ALTER TABLE `dgdasp`.`order_items`
CHANGE COLUMN `total` `total` FLOAT(15,2) NULL DEFAULT '0.00' ,
CHANGE COLUMN `tax` `tax` FLOAT(15,2) NULL DEFAULT '0.00' ;


SET SQL_SAFE_UPDATES = 0;
update dgdasp.orders set orders.tax=0 where orders.tax is null;
update dgdasp.orders set orders.quantity=0 where orders.quantity is null;
update dgdasp.orders set orders.discount=0 where orders.discount is null;
update dgdasp.orders set orders.total_advance_amount=0 where orders.total_advance_amount is null;
update dgdasp.orders set orders.total_amount=0 where orders.total_amount is null;
update dgdasp.orders set orders.total_due_amount=0 where orders.total_due_amount is null;
update dgdasp.orders set orders.total_payble_amount=0 where orders.total_payble_amount is null;
SET SQL_SAFE_UPDATES = 1;
