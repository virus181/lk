INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`)
VALUES
	('/shop/user-list',2,NULL,NULL,NULL,1492451410,1492451410);

INSERT INTO `auth_item_child` (`parent`, `child`)
VALUES
	('shopAdmin','/shop/user-list'),
	('systemAdmin','/shop/user-list'),
	('shopWatcher','/shop/user-list');
