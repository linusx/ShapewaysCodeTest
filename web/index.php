<?php

require __DIR__ . '/../vendor/autoload.php';

use \Slim\Middleware\HttpBasicAuthentication\PdoAuthenticator;

// Load env
$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

// Setup DB managment
$pdo = new \PDO(
	'mysql:host=' . getenv('DBHOST') . ';dbname=' . getenv('DBNAME'),
	getenv('DBUSER'),
	getenv('DBPASS')
);

// Set config variables for slim
$config = [
	'settings' => [
		'displayErrorDetails' => true,
	],
];

// Setup slim router
$app = new Slim\App($config);

// Setup authentication middleware
$app->add(new \Slim\Middleware\HttpBasicAuthentication([
	"secure" => false,
	"path" => "/",
	"passthrough" => ["/install"],
	"realm" => "Shapeways",
	"authenticator" => new PdoAuthenticator([
		"pdo" => $pdo,
		"table" => "users",
		"user" => "email",
		"hash" => "password"
	]),
	"error" => function ($request, $response, $arguments) {
		$data = [];
		$data["status"] = "error";
		$data["message"] = $arguments["message"];
		return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES));
	},
	"callback" => function ($request, $response, $arguments) use ($app) {
		return $response->withRedirect('/products');
	}
]));

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($c) {
	$view = new \Slim\Views\Twig( dirname( __FILE__) . '/../views', [
		'cache' => false
	]);

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
	return $view;
};

// Require routes file
require_once( dirname( __FILE__ ) . '/../routes.php' );

$app->run();

