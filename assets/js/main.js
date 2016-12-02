(function($) {

    $(document).on('click', '#add-to-cart', function() {
        alert("DISABLED FOR SHAPEWAYS TEST");
    });

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

    $(document).ready(function() {

        $('.comment_unread').waypoint(function() {
            if ( this.element.id ) {
                markAsRead('#' + this.element.id);
            }
        }, {
            offset: '70%'
        });
    });


    function markAsRead(sel) {
        var unread = $('.unread-count').text();
        var comment = $(sel);
        var comment_id = comment.data('id');
        var product = comment.data('product');

        if ( comment.hasClass('comment_unread') ) {
            comment.removeClass('comment_unread').removeClass('panel-warning').addClass('comment_read').addClass('panel-default');

            // Mark as read
            $.ajax({
                method: 'PUT',
                url: '/product/' + product + '/comment/' + comment_id
            })
            .done(function(){
                if ( unread ) {
                    var new_count = parseInt(unread) - 1;

                    // If we have anything greater than zero show it, otherwise don't put anything there.
                    if ( new_count > 0 ) {
                        $('.unread-count').text(new_count);
                    } else {
                        $('.unread-count').text('');
                    }

                }
            });
        }
    }

})(jQuery);