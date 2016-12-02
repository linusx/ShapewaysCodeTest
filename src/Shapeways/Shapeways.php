<?php

namespace Shapeways;

class Shapeways {

	public $db;
	public $view;
	public $page = 1;
	public $limit = 20;
	public $start = 0;
	public $current_user;
	public $current_product;

	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new Shapeways();
		}
		return $inst;
	}

	public function __construct() {

		$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../');
		$dotenv->load();

		$this->db = new \PDO(
			'mysql:host=' . getenv('DBHOST') . ';dbname=' . getenv('DBNAME'),
			getenv('DBUSER'),
			getenv('DBPASS')
		);

		$this->current_user = $this->getUserByEmail( $_SERVER['PHP_AUTH_USER'] );

		$this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);

		$this->page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT, [ 'options' => [ 'default' => 1, 'min_range' => 0 ] ]);
		$this->limit = filter_input( INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT, [ 'options' => [ 'default' => 20, 'min_range' => 0 ] ]);
		$this->start = ($this->page - 1) * $this->limit;

		$this->view = new \Slim\Views\Twig( dirname( __FILE__) . '/../../views', []);
	}

	public function sendFailure( $message ) {
		echo json_encode( ['success' => false, 'message' => $message] );
	}

	public function sendSuccess( $message, $data ) {
		echo json_encode( ['success' => true, 'data' => $data, 'message' => $message] );
	}

	public function getUserByEmail($email) {

		$sql = 'SELECT id, firstname, lastname, email, created_at FROM users WHERE email = ?';
		$statement = $this->db->prepare( $sql );
		$statement->execute( [ $email ] );
	    return $statement->fetch();
	}
}
