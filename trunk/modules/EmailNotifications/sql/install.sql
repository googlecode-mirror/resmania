CREATE TABLE IF NOT EXISTS `rm_email_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(50) NOT NULL,
  `destination` tinyint(1) NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `en_GB` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_name` (`event_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `rm_email_notifications` (`id`, `event_name`, `destination`, `enabled`, `en_GB`) VALUES(1, 'ReservationCompleteSuccessful', 0, 1, 'Reservation completed successfully.');
