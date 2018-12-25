INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`)
VALUES
	('/user/operator-update',2,NULL,NULL,NULL,1492451410,1492451410);

INSERT INTO `auth_item_child` (`parent`, `child`)
VALUES
	('root','/user/operator-update'),
	('systemAdmin','/user/operator-update');
