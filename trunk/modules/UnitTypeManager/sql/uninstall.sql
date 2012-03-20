DELETE FROM `rm_modules` WHERE `name`='UnitTypeManager';
DELETE FROM `rm_unit_types` WHERE `rm_unit_types`.`id` = 2 LIMIT 1;
DELETE FROM `rm_unit_types` WHERE `rm_unit_types`.`id` = 3 LIMIT 1;
DELETE FROM `rm_unit_types` WHERE `rm_unit_types`.`id` = 4 LIMIT 1;
DELETE FROM `rm_unit_types` WHERE `rm_unit_types`.`id` = 5 LIMIT 1;
DELETE FROM `rm_unit_types` WHERE `rm_unit_types`.`id` = 6 LIMIT 1;
DELETE FROM `rm_unit_types` WHERE `rm_unit_types`.`id` = 7 LIMIT 1;
DELETE FROM `rm_unit_types` WHERE `rm_unit_types`.`id` = 8 LIMIT 1;

ALTER TABLE `rm_unit_types` DROP `price`;
ALTER TABLE `rm_unit_types` DROP `default`;

