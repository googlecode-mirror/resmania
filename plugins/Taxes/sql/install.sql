CREATE TABLE `rm_taxes` (
  `id` int(11) NOT NULL auto_increment,
  `en_GB` varchar( 255 ) NOT NULL,
  `fr_FR` VARCHAR( 255 ) NOT NULL,
  `ru_RU` VARCHAR( 255 ) NOT NULL,
  `global`  TINYINT( 1 ) NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL,
  `type` enum('percentage','amount') NOT NULL,
  `enabled` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB CHARACTER SET 'utf8' COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `rm_unit_taxes` (
  `id` int(11) NOT NULL auto_increment,
  `unit_id` int(11) NOT NULL,
  `tax_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tax_id` (`tax_id`),
  KEY `unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE `rm_unit_taxes`
  ADD CONSTRAINT `rm_unit_taxes_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `rm_units` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rm_unit_taxes_ibfk_1` FOREIGN KEY (`tax_id`) REFERENCES `rm_taxes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
