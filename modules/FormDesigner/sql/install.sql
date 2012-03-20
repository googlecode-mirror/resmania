CREATE TABLE `rm_forms` (
`name` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `name` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `rm_form_panels` (
`name` VARCHAR( 50 ) NOT NULL ,
`form_name` VARCHAR( 50 ) NOT NULL ,
`state` TEXT NOT NULL ,
PRIMARY KEY ( `name` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `rm_form_panels` ADD INDEX ( `form_name` );

ALTER TABLE `rm_form_panels` ADD FOREIGN KEY ( `form_name` ) REFERENCES `rm_forms` (`name`) ON DELETE CASCADE ON UPDATE CASCADE ;