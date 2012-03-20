CREATE TABLE `rm_extras` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM( 'single', 'day', 'percentage' ) NOT NULL,
  `global`  TINYINT( 1 ) NOT NULL DEFAULT '0',
  `min` INT( 11 ) NOT NULL DEFAULT '1',
  `max` INT( 11 ) NOT NULL DEFAULT '1',
  `en_GB` varchar( 255 ) NOT NULL,
  `fr_FR` VARCHAR( 255 ) NOT NULL,
  `ru_RU` VARCHAR( 255 ) NOT NULL,
  `rule` enum('RoundUp', 'RoundDown', 'Hourly') collate utf8_unicode_ci NOT NULL DEFAULT 'RoundUp',
  `value` DECIMAL( 10, 2 ) NOT NULL,
  `enabled` tinyint(4) NOT NULL default '1'
) ENGINE = InnoDB CHARACTER SET 'utf8';

CREATE TABLE `rm_unit_extras` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `unit_id` INT NOT NULL ,
  `extra_id` INT NOT NULL
) ENGINE = innodb CHARACTER SET utf8 COLLATE utf8_unicode_ci;