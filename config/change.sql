ALTER TABLE `order_items` ADD `server_item_id` INT NULL AFTER `is_status_updated`;
ALTER TABLE `order_items` ADD `is_status_sync` BOOLEAN NULL DEFAULT FALSE AFTER `is_status_updated`;
