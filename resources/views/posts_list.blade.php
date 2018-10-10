@extends('layouts.app')

@section('content')
    <div class="container posts_wrapper">
        @foreach($posts as $post)
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
                        <a href="/post/view/{{ $post['id'] }}" class="post_view">Посмотреть новость</a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <br><br>
                @if (Auth::check())
                    <form action="/post/save" method="post" id="post_create_form">
                        <div class="form-group">
                            <textarea class="form-control" name="message" cols="30" rows="5" placeholder="Введите текст Вашего сообщения..." style="width: 90%;"></textarea>
                            <div id="error"></div>
                            <button type="submit" class="btn btn-primary">Отправить сообщение</button>
                            {{ csrf_field() }}
                        </div>
                    </form>
                @else
                    <div class="text-center">Создавать записи и оценивать их могут только зарегистрированные пользователи.</div>
                @endif
            </div>
        </div>
    </div>
    <script src="{{ asset('js/post_list.js') }}" defer></script>
@endsection
