ALTER TABLE `rm_unit_types` ADD `price` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `rm_unit_types` ADD `default` TINYINT( 1 ) NOT NULL DEFAULT '0';
UPDATE `rm_unit_types` SET `default` = '1' WHERE `id` = 1;