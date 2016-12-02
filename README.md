# Shapeways Code Test

##Installation:
1. Clone repository
2. Add a virtual host and point the doc root to the web folder.
3. Copy `env.example` to `.env` and edit file for your settings.
4. Run `composer install`
5. Run `npm install`
6. Run `gulp`
7. Visit your site at /install to install the DB and test data.
8. Visit your site at / to view products.


####1. Design a MYSQL database table or tables to hold user comments on a product. Explain how it works, and how it’s used.

**Comments Table:**
Holds the comments per product.
```
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
```

**Comments Read Table:**
Lookup table for comments that are marked read.
```
-- Create syntax for TABLE 'comments_read'
CREATE TABLE `comments_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=latin1;
```
**Products Table:**
Holds product information.
```
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
```

**Users Table:**
Holds user information.
```
-- Create syntax for TABLE 'users'
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(30) NOT NULL DEFAULT '',
  `lastname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
```

When a user comments on a product it is entered into the `comments` table.  

I use the jquery `waypoints` plugin to determine when a comment is in view of the user to send an ajax request to mark the comment as read.   
  
When a comment is marked as read it goes into the `comments_read` lookup table.  



####2. How would you get the number of unread comments per product for a user?
```
SELECT 
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
			ORDER BY c.created_at DESC
```

####3. Write a request handler that adds a comment to a product.
```
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
```
####4. Write a javascript function that submits a comment form
```
$(document).on('submit', '#comment-form', function(e) {
        e.preventDefault();

        var url = $(this).attr('action'), comment = $('#comment').val();

        // Reset the comment field
        $('#comment').val('');

        // Submit the comment
        $.post(url, { comment: comment }, function(data, status){
            if ( 'success' === status ) {
                $('#comment-list').prepend(data);
            }
        }, 'html' );
    });
```
####5. Implement a server-side code that renders a page with the form in it. Explain your choices.

*I chose to use Twig. I like the simplicity of it, and it's extendability.*  

***Server side method.*** 
```
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
```
***TWIG Template***
```
	<div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h3>Submit Your Comment {{ user.firstname }}</h3>
                <form action="/product/{{ product.slug }}/comment" method="post" class="form-horizontal" id="comment-form" role="form">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <textarea class="form-control" name="comment" id="comment" rows="5"></textarea>
                        </div>
                    </div>
    
                    <div class="form-group">
                        <div class="col-xs-12">
                            <button class="btn btn-success btn-circle text-uppercase" type="submit" id="submitComment"><span class="glyphicon glyphicon-send"></span> Summit comment</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-xs-12">
                <ul class="comment-list" id="comment-list">
                    {% if comments %}
                        {% for comment in comments %}
                            {% include 'product/comments/comment.twig' with comment %}
                        {% endfor %}
                    {% else %}
                        <h3>Be the first to comment!</h3>
                    {% endif %}
                </ul>
            </div>
        </div>
    </div>
```

####6. How would you test the above?
*I would write unit tests using `phpunit` ( my favorite ).
Below is a quick example. I would have to add, adding a comment so I can retrieve it's ID so I can test marking that comment read*

######Example Test
```
require_once('../src/Shapeways/Comments.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CommentsTest extends PHPUnit_Framework_TestCase {
  public function setUp(){ }
  public function tearDown(){ }

  public function testCanMarkCommentRead(){
    $c = new Comments();
    
    $this->assertTrue( $c->readComment(Request, Response, ['id' => 2]) );
  }
}
```
 