INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`)
VALUES
	('/label/pdf',2,NULL,NULL,NULL,1492451410,1492451410),
	('storeKeeper',1,'Кладовщик',NULL,NULL,1492451393,1492451393);

INSERT INTO `auth_item_child` (`parent`, `child`)
VALUES
	('storeKeeper','/label/download'),
	('storeKeeper','/label/index'),
	('storeKeeper','/label/pdf'),
	('shopAdmin','storeKeeper');
