CREATE TABLE  `rm_gmaps` (
`id` INT( 2 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`zoomlevel` INT( 2 ) NOT NULL ,
`maptype` varchar( 50 ) NOT NULL
) ENGINE = INNODB COMMENT =  'ResMania GMaps Configuration Settings';

INSERT INTO `rm_gmaps` VALUES(1, 14, 'ROADMAP');

INSERT INTO `rm_form_panels` (`id` ,`form_id` ,`name`) VALUES('map_advancedsearch', 'advancedsearch', 'GMap');