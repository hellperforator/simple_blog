$(function() {
    $post_create_form = $('#post_create_form');
    $posts_wrapper = $('.posts_wrapper');
    $message_input =  $('[name="message"]');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $post_create_form.on('submit', function (e) {
        e.preventDefault();

        var data = {
            message: $message_input.val(),
        }

        $.post({
            url: '/post/save/',
            data: data,
            success: function (resp) {
                console.log(resp);
                $posts_wrapper.append(createNewPost(resp));
                $message_input.val('')
            },
            
        }).fail(function (resp) {
           var error = resp.responseJSON.errors.message[0];
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
            success: function () {
                var $score = $form.find('[data-score]');
                var old_score =  Number($score.text())
                var new_score = sign === 'plus' ? ++old_score : --old_score;
                $score.text(new_score);
            },
        });
    });
    function createNewPost(data) {
        var new_post =
            '<div class="row justify-content-center">' +
            '   <div class="col-md-12">\n' +
            '       <div class="post_wrapper">\n' +
            '           <div class="post_header">\n' +
            '                <span class="author_name">' + data.author_name +'</span>\n' +
            '                <span class="post_date">' + data.post_date + '</span>\n' +
            '           </div>\n' +
            '           <div class="post_body">' + data.message +'</div>\n' +
            '           <div class="post_footer">\n' +
            '               <form action="/post/score/" method="post" data-score-form-id="' + data.post_id + '">\n' +
            '               <button data-increase-core="1"><span class="oi oi-arrow-circle-top"></span></button>\n' +
            '               <span data-score="1"> 0 </span>\n' +
            '               <button data-decrease-score="1"><span class="oi oi-arrow-circle-bottom"></span></button>\n' +
            '               </form>\n' +
            '           </div>\n' +
            '       </div>\n' +
            '   </div>\n' +
            '</div>';
        return new_post;
    }
})