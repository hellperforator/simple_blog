$(function () {
    $add_comment_form = $('#add_comment_form');
    $comments_wrapper = $('.comments_wrapper');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $add_comment_form.on('submit', function(e) {
        e.preventDefault();

        var data = {
            text: $('[name="comment"]').val(),
            post_id: $(this).find('[name="post_id"]').val()
        }

        $.post({
            url: '/post/comment/',
            data: data,
            success: function (resp) {
                $comments_wrapper.append(createNewComment(resp));
                $('[name="comment"]').val('')
            },

        }).fail(function (resp) {
            var error = resp.responseJSON.errors.text[0];
            $('#error').text(error);
        })
    });

    function createNewComment(data) {
        var new_post =
            '<div class="row justify-content-center comment_wrapper">\n' +
            '     <div class="col-md-12">\n' +
            '        <div class="comment_date">' + data.comment_date +'</div>\n' +
            '        <div>'+ data.text +'</div>\n' +
            '        <hr>\n' +
            '        <div>' + data.author_name + '</div>\n' +
            '     </div>\n' +
            '</div>'
        return new_post;
    }
})