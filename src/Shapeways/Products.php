<?php

namespace Shapeways;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PDO;

class Products extends Shapeways {

	private $comments;

	public function __construct() {
		parent::__construct();
	}

	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new Products();
		}
		return $inst;
	}

	public function showProducts(Request $request, Response $response, $args) {
		$sql = 'SELECT
 				(SELECT COUNT(id) FROM comments WHERE product_id = products.id) as comment_count, 
				id, 
				name, 
				description, 
				slug, 
				price, 
				created_at 
			FROM 
				products 
			LIMIT ?,?';

		$statement = $this->db->prepare( $sql );
		$statement->execute( [ $this->start, $this->limit ] );
		$products = $statement->fetchAll(PDO::FETCH_ASSOC);

		return $this->view->render($response, 'product/list.twig', [ 'products' => $products, 'user' => $this->current_user ]);
	}

	public function showProduct(Request $request, Response $response, $args) {
		if ( empty( $args['slug'] ) ) {
			return;
		}

		$sql = 'SELECT id, name, description, slug, price, created_at FROM products WHERE slug = ?';
		$statement = $this->db->prepare( $sql );
		$statement->execute( [ $args['slug'] ] );
		$product = $statement->fetch(PDO::FETCH_ASSOC);

		$comments = Comments::getInstance()->getCommentsByProduct($product['id']);
		$unread = Comments::getInstance()->getUnreadPerProduct($comments);

		return $this->view->render($response, 'product/product.twig', ['product' => $product, 'comments' => $comments, 'user' => $this->current_user, 'unread' => $unread ]);
	}

	public function getBySlug($slug) {
		$sql = 'SELECT id, name, description, slug, price, created_at FROM products WHERE slug = ?';
		$statement = $this->db->prepare( $sql );
		$statement->execute( [ $slug ] );
		return $statement->fetch(PDO::FETCH_ASSOC);
	}

}