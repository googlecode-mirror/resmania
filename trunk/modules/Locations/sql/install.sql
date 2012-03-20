--
-- Table structure for table `rm_locations`
--

CREATE TABLE `rm_locations` (
  `id` int(11) NOT NULL auto_increment,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `postcode` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `directions` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 CHARACTER SET 'utf8';

--
-- Table structure for table `rm_unit_locations`
--

CREATE TABLE `rm_unit_locations` (
  `unit_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY  (`unit_id`,`location_id`),
  KEY `rm_unit_locations_ibfk_2` (`location_id`)
) ENGINE=InnoDB CHARACTER SET 'utf8';

--
-- Constraints for table `rm_unit_locations`
--
ALTER TABLE `rm_unit_locations`
  ADD CONSTRAINT `rm_unit_locations_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `rm_units` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rm_unit_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `rm_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;