CREATE TABLE  `rm_paypal` (
`id` INT( 2 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`account` VARCHAR( 50 ) NOT NULL ,
`sandbox` TINYINT( 1 ) NOT NULL,
`defaultplugin` TINYINT( 1 ) NOT NULL
) ENGINE = INNODB COMMENT =  'ResMania PayPal Configuration Settings';

INSERT INTO `rm_paypal` VALUES(1, 'yourpaypal@email.com', 1);