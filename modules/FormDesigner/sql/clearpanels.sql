TRUNCATE TABLE `rm_forms`;

INSERT INTO `rm_forms` (`id`, `state`, `name`, `columns`, `max_columns`, `column1width`, `column2width`, `column3width`, `view_path`, `global`) VALUES('advancedsearch', '', 'Advanced Search', 2, 1, 0, 0, 0, 'Search_advanced', 1);
INSERT INTO `rm_forms` (`id`, `state`, `name`, `columns`, `max_columns`, `column1width`, `column2width`, `column3width`, `view_path`, `global`) VALUES('cart', '', 'Shopping cart', 2, 1, 0, 0, 0, 'Reservations_cart', 1);
INSERT INTO `rm_forms` (`id`, `state`, `name`, `columns`, `max_columns`, `column1width`, `column2width`, `column3width`, `view_path`, `global`) VALUES('login', '', 'Login', 2, 2, 0, 0, 0, 'User_login', 1);
INSERT INTO `rm_forms` (`id`, `state`, `name`, `columns`, `max_columns`, `column1width`, `column2width`, `column3width`, `view_path`, `global`) VALUES('summary', '', 'Summary', 2, 1, 0, 0, 0, 'Reservations_summary', 1);
INSERT INTO `rm_forms` (`id`, `state`, `name`, `columns`, `max_columns`, `column1width`, `column2width`, `column3width`, `view_path`, `global`) VALUES('unitdetails', '', 'Unit Details', 2, 2, 0, 0, 0, 'Unit_details', 0);
INSERT INTO `rm_forms` (`id`, `state`, `name`, `columns`, `max_columns`, `column1width`, `column2width`, `column3width`, `view_path`, `global`) VALUES('unitlist', '', 'Unit List', 2, 3, 0, 0, 0, 'Unit_list', 0);
INSERT INTO `rm_forms` (`id`, `state`, `name`, `columns`, `max_columns`, `column1width`, `column2width`, `column3width`, `view_path`, `global`) VALUES('userdetails', '', 'User Details', 2, 1, 0, 0, 0, 'User_details', 1);

TRUNCATE TABLE `rm_unit_type_forms`;

INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(1, 1, 'advancedsearch', '[[]]', 1, 0, 0, 0);
INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(2, 1, 'cart', '[[]]', 1, 0, 0, 0);
INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(3, 1, 'login', '[[]]', 1, 0, 0, 0);
INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(4, 1, 'summary', '[[]]', 1, 0, 0, 0);
INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(5, 1, 'unitdetails', '[[],[]]', 2, 0, 0, 0);
INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(6, 1, 'unitlist', '[[],[],[]]', 3, 0, 0, 0);
INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(7, 1, 'userdetails', '[[]]', 1, 0, 0, 0);
INSERT INTO `rm_unit_type_forms` (`id`, `unit_type_id`, `form_id`, `state`, `columns`, `column1width`, `column2width`, `column3width`) VALUES(8, 2, 'unitdetails', '[[],[]]', 2, 0, 0, 0);
