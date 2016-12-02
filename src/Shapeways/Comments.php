<?php

namespace Shapeways;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use PDO;

class Comments extends Shapeways {

	private $product;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Singleton
	 *
	 * @return null|Comments
	 */
	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new Comments();
		}
		return $inst;
	}

	/**
	 * Mark a comment as read.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 *
	 * @return bool|void
	 */
	public function readComment(Request $request, Response $response, $args) {
		if ( empty( $args['id'] ) ) {
			return;
		}

		$comment_id = $args['id'];

		try {
			$sql       = 'INSERT IGNORE INTO comments_read SET comment_id = ?, user_id = ?, created_at = NOW()';
			$statement = $this->db->prepare( $sql );
			$statement->execute( [
				$comment_id,
				$this->current_user['id']
			] );
		} catch ( \PDOException $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Add a comment to a product.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 *
	 * @return html
	 */
	public function addComment(Request $request, Response $response, $args) {
		if ( empty( $args['slug'] ) ) {
			return;
		}

		$comment = filter_input( INPUT_POST, 'comment', FILTER_SANITIZE_STRING );

		if ( empty( $comment ) ) {
			return $this->sendFailure('No comment');
		}

		$product = Products::getInstance()->getBySlug($args['slug']);

		$sql = 'INSERT INTO comments (user_id, product_id, comment, status, created_at) VALUES (?, ?, ?, ?, NOW())';
		$statement = $this->db->prepare( $sql );
		$statement->execute( [
			$this->current_user['id'],
			$product['id'],
			$comment,
			1
		] );
		$new_comment_id = $this->db->lastInsertId();

		$sql = 'INSERT INTO comments_read (comment_id, user_id, created_at) VALUES (?, ?, NOW())';
		$statement = $this->db->prepare( $sql );
		$statement->execute( [
			$new_comment_id,
			$this->current_user['id']
		] );

		$comment = $this->getCommentById( $new_comment_id );

		$comment_html = $this->view->render($response, 'product/comments/comment.twig', $comment);

		return $comment_html;
	}

	/**
	 * Retrieve a comment by it's ID.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getCommentById( $id ) {
		try {
			$sql       = 'SELECT 
			IF((SELECT id FROM comments_read WHERE comment_id = c.id AND user_id = ' . $this->current_user['id'] . '), \'true\', \'false\') as is_read,
			p.name as product_name,
			p.price,
			p.description,
			p.slug,
			p.created_at as product_created_at,
			u.id as user_id, 
			u.firstname, 
			u.lastname, 
			u.email, 
			c.id, 
			c.user_id, 
			c.product_id, 
			c.comment as the_comment, 
			c.status, 
			c.created_at as comment_created_at 
			FROM comments c 
			LEFT OUTER JOIN users u ON c.user_id = u.id
		 	LEFT OUTER JOIN products p ON c.product_id = p.id
			WHERE c.id = ?
			ORDER BY c.created_at DESC';
			$statement = $this->db->prepare( $sql );
			$statement->execute( [ $id ] );
			$response = $statement->fetch(PDO::FETCH_ASSOC);
		} catch( \PDOException $e ) {
			echo $e->getMessage();
			die();
		}

		return $response;
	}

	/**
	 * Retrieve comments by a product ID
	 *
	 * @param int $product_id
	 * @param int $status
	 *
	 * @return array
	 */
	public function getCommentsByProduct($product_id, $status = 1) {
		$sql = 'SELECT 
			IF((SELECT id FROM comments_read WHERE comment_id = c.id AND user_id = ' . $this->current_user['id'] . '), \'true\', \'false\') as is_read,
			p.name as product_name,
			p.price,
			p.description,
			p.slug,
			p.created_at as product_created_at,
			u.id as user_id, 
			u.firstname, 
			u.lastname, 
			u.email, 
			c.id, 
			c.user_id, 
			c.product_id, 
			c.comment as the_comment, 
			c.status, 
			c.created_at as comment_created_at 
			FROM comments c 
			LEFT OUTER JOIN users u ON c.user_id = u.id
		 	LEFT OUTER JOIN products p ON c.product_id = p.id
			WHERE c.status = ? AND c.product_id = ?
			ORDER BY c.created_at DESC';
		$statement = $this->db->prepare( $sql );
		$statement->execute( [
			$status,
			$product_id
		] );

		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Get unread comments per product
	 *
	 * @param array $comments
	 *
	 * @return int
	 */
	public function getUnreadPerProduct($comments = []) {
		$count = 0;
		foreach($comments as $comment) {
			if ( 'false' ===  $comment['is_read'] ) {
				$count++;
			}
		}
		return $count;
	}

}