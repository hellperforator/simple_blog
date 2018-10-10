@extends('layouts.app')

@section('content')
    <div class="container posts_wrapper">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="post_wrapper">
                    <div class="post_header">
                        <span class="author_name">{{ $post['name'] }}</span>
                        <span class="post_date">{{ $post['created_at'] }}</span>
                    </div>
                    <div class="post_body">{{ $post['message'] }}</div>
                    <div class="post_footer">
                        <div class="score_panel">
                            <form action="/post/score" method="post" data-score-form-id="{{ $post['id'] }}">
                                <button data-sign="plus"@if ($post['active_button_flag'] === -1 && $is_authorized) disabled @endif><span class="oi oi-arrow-circle-top"></span></button>
                                <span data-score="1"> {{ $post['score'] }}</span>
                                <button data-sign="minus"@if ($post['active_button_flag'] === 1 && $is_authorized) disabled @endif> <span class="oi oi-arrow-circle-bottom"></span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <br><br>
                @if (Auth::check())
                    <h2>Оставьте комментарий</h2>
                    <form action="/post/save" method="post" id="add_comment_form">
                        <div class="form-group">
                            <textarea class="form-control comment_ta" name="comment" cols="30" rows="5" placeholder="Введите текст Вашего сообщения..."></textarea>
                            <div id="error"></div>
                            <button type="submit" class="btn btn-primary">Оставить комментарий</button>
                            {{ csrf_field() }}
                            <input type="hidden" name="post_id" value="{{$post['id']}}">
                        </div>
                    </form>
                @else
                    Вы не можете оставлять комментарии и оценивать. Это могут делать только зарегистрированные пользователи.
                @endif
            </div>
        </div>
    </div>
    <div class="container comments_wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center">Комментарии</h2>
            </div>
        </div>
       @if (isset($comments))
           @foreach($comments as $comment)
                <div class="row justify-content-center comment_wrapper">
                    <div class="col-md-12">
                        <div class="comment_date">{{ $comment['comment_date'] }}</div>
                        <div> {{ $comment['text'] }}</div>
                        <div class="comment_author">{{ $comment['author_name'] }}</div>
                        <hr>
                    </div>
                </div>
           @endforeach
        @else
            <div class="row justify-content-center">
                <div class="col-md-12 text-center">
                    <br>
                    Комментариев ещё нет. Вы будете первым.
                </div>
            </div>
       @endif
    </div>
    <script src="{{ asset('js/post_view.js') }}" defer></script>
@endsection
