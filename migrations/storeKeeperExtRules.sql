INSERT INTO `auth_item_child` (`parent`, `child`)
VALUES
	('storeKeeper','/shop/index'),
	('storeKeeper','/shop/view'),
	('storeKeeper','/shop/update'),
	('storeKeeper','/shop/*'),
	('storeKeeper','/order/get-courier-orders'),
	('storeKeeper','/courier/index'),
	('storeKeeper','/courier/download'),
	('storeKeeper','/courier/order-list');
