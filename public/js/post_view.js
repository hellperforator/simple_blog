$(function () {
   var $add_comment_form = $('#add_comment_form');
   var $comments_wrapper = $('.comments_wrapper');
   var $posts_wrapper    = $('.posts_wrapper');

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

    $posts_wrapper.on('click', '[data-score-form-id] button', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $th = $(this);
        var $form = $th.closest('form');
        var sign = $th.data('sign');
        var data = {
            sign: sign,
            post_id: $form.data('score-form-id'),
        }

        $.post({
            url: '/post/score/',
            data: data,
            success: function (resp) {
                var $score = $form.find('[data-score]');
                var old_score =  Number($score.text())
                var new_score = old_score;

                if (!resp.change) {
                    return;
                }
                if (new_score == 1 || new_score == -1) {
                    new_score = 0;
                }
                if (sign === 'plus') {
                    new_score++;
                } else {
                    new_score--;
                }
                $('[data-sign][disabled]').removeAttr('disabled');
                $th.attr('disabled', true);
                $score.text(new_score);
            },
        });
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