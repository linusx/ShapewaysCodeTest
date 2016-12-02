<?php

$app->get('/', 'Shapeways\Products:showProducts')->setName('products');

$app->get('/products', 'Shapeways\Products:showProducts')->setName('products');
$app->get('/product/{slug}', 'Shapeways\Products:showProduct')->setName('product');


$app->post('/product/{slug}/comment', 'Shapeways\Comments:addComment')->setName('add-comment');

$app->put('/product/{slug}/comment/{id}', 'Shapeways\Comments:readComment')->setName('mark-comment-read');


// Initial etup
$app->get('/install', function () use ($pdo) {

	$create_sql = "
		-- Create syntax for TABLE 'comments'
		CREATE TABLE `comments` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` int(11) DEFAULT NULL,
		`product_id` int(11) DEFAULT NULL,
		`comment` text,
		`status` int(11) DEFAULT NULL,
		`created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;
			
		-- Create syntax for TABLE 'comments_read'
		CREATE TABLE `comments_read` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`comment_id` int(11) NOT NULL,
		`user_id` int(11) NOT NULL,
		`created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=latin1;
		
		-- Create syntax for TABLE 'products'
		CREATE TABLE `products` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`name` varchar(255) DEFAULT NULL,
		`description` text,
		`slug` varchar(255) DEFAULT NULL,
		`price` decimal(4,2) DEFAULT NULL,
		`created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;
		
		-- Create syntax for TABLE 'users'
		CREATE TABLE `users` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`firstname` varchar(30) NOT NULL DEFAULT '',
		`lastname` varchar(30) NOT NULL DEFAULT '',
		`email` varchar(255) NOT NULL DEFAULT '',
		`password` varchar(255) NOT NULL,
		`created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;";
	$pdo->exec( $create_sql );

	echo 'Adding users...<br/>';
	$hash = password_hash("password", PASSWORD_DEFAULT);
	$pdo->exec( "INSERT INTO users (firstname, lastname, email, password, created_at) VALUES ('Bill', 'Van Pelt', 'linusx@gmail.com', '" . $hash . "', NOW())" );
	$pdo->exec( "INSERT INTO users (firstname, lastname, email, password, created_at) VALUES ('Mike', 'Auteri', 'linus.x@gmail.com', '" . $hash . "', NOW())" );
	$pdo->exec( "INSERT INTO users (firstname, lastname, email, password, created_at) VALUES ('Jared', 'Kazimir', 'linu.sx@gmail.com', '" . $hash . "', NOW())" );
	$pdo->exec( "INSERT INTO users (firstname, lastname, email, password, created_at) VALUES ('Joe', 'Choi', 'lin.usx@gmail.com', '" . $hash . "', NOW())" );

	echo 'Users added succesfully!<br/><br/><br/>';


	echo 'Addin products...<br/>';
	$pdo->exec( "INSERT INTO products (name, price, description, slug, created_at) VALUES ('Test Product 1', 19.99, 'Maecenas sed diam eget risus varius blandit sit amet non magna. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Curabitur blandit tempus porttitor. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Maecenas sed diam eget risus varius blandit sit amet non magna.', 'test-product-1', NOW())" );
	$pdo->exec( "INSERT INTO products (name, price, description, slug, created_at) VALUES ('Test Product 2', 9.99, 'Maecenas sed diam eget risus varius blandit sit amet non magna. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Curabitur blandit tempus porttitor. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Maecenas sed diam eget risus varius blandit sit amet non magna.', 'test-product-2', NOW())" );
	$pdo->exec( "INSERT INTO products (name, price, description, slug, created_at) VALUES ('Test Product 3', 4.99, 'Maecenas sed diam eget risus varius blandit sit amet non magna. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Curabitur blandit tempus porttitor. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Maecenas sed diam eget risus varius blandit sit amet non magna.', 'test-product-3', NOW())" );
	$pdo->exec( "INSERT INTO products (name, price, description, slug, created_at) VALUES ('Test Product 4', 5.99, 'Maecenas sed diam eget risus varius blandit sit amet non magna. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Curabitur blandit tempus porttitor. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Maecenas sed diam eget risus varius blandit sit amet non magna.', 'test-product-4', NOW())" );
	$pdo->exec( "INSERT INTO products (name, price, description, slug, created_at) VALUES ('Test Product 5', 12.99, 'Maecenas sed diam eget risus varius blandit sit amet non magna. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Curabitur blandit tempus porttitor. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Maecenas sed diam eget risus varius blandit sit amet non magna.', 'test-product-5', NOW())" );

	echo 'Products added succesfully!';

})->setName('init');