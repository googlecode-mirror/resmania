CREATE TABLE `rm_categories` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`parent_id` INT NOT NULL ,
`en_GB` VARCHAR( 255 ) NOT NULL ,
`fr_FR` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `rm_unit_categories` (
`unit_id` INT( 11 ) NOT NULL ,
`category_id` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `unit_id` , `category_id` )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO `rm_form_panels` (`id` ,`form_id` ,`name`) VALUES ('category_advancedsearch', 'advancedsearch', 'Category');