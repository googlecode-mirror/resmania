DROP TABLE IF EXISTS `rm_categories`;
DROP TABLE IF EXISTS `rm_unit_categories`;
DELETE FROM `rm_form_panels` WHERE `rm_form_panels`.`id` = 'category_advancedsearch' LIMIT 1;