<?php

namespace App\Http\Controllers;

use App\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Post extends Controller
{

    public function __construct() {
        $this->middleware('auth')->except('index');
        $score = $this->getUserScore() ?? 0;
        session(['score' => $score]);
    }

    public function index(Request $request) {
        $posts = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select([
                'posts.id',
                'posts.user_id',
                'users.name',
                'posts.message',
                'posts.created_at'
            ])->get();
        $user_id = null;
        if (Auth::check()) {
            $user_id = $request->user()->id;
        }
        return view(
            'posts_list',
            [
                'posts' => $this->prepareDataToOutput($posts, $user_id),
                'is_authorized' => Auth::check()
            ]);
   }

    /**
     * Подготавливает данные по постам для рендера
     * @param $input_data
     * @param $user_id
     * @return array
     */
   private function prepareDataToOutput($input_data, $user_id = null) {
       $data = [];
       foreach ($input_data as $post) {
           $data[] = [
               'id' => $post->id,
               'name' => $post->name,
               'created_at' => "$post->created_at",
               'message' => $post->message,
               'score' => $this->getPostScore($post->id),
               'active_button_flag' => $this->getActiveButtonFlag($post->id, $user_id)
           ];
       }
       return $data;
   }

    /**
     * Получить флаг активности кнопок плюс и минус
     * 0  - обе кнопки активны
     * -1 - активен минус
     * 1  - активен плюс
     * @param $post_id
     * @param null $user_id
     * @return int|null
     */
   private function getActiveButtonFlag($post_id, $user_id = null) {
       if (is_null($user_id)) {
           return 0;
       }
       $score_model = Score::where([
           ['post_id','=' ,$post_id],
           ['user_id', '=', $user_id]
       ]);

       $active_button_flag = null;

       if ($score_model->count()) {
           $active_button_flag = $score_model->first()->sign === 'plus' ? -1 : 1;
       }
       return $active_button_flag;
   }


    /**
     * Сохраняет новую запись и возвращает данные по новому посту в JSON
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request) {
       $message = $request->post('message');
       $this->validate($request, ['message' => 'required'],['message.required' => 'Сообщение не может быть пустым!']);
       $model = new \App\Post();
       $model->user_id = $request->user()->id;
       $model->message = $message;
       if ($model->save()) {
           $user_name = $request->user()->name;
           return response()->json([
                'author_name' => $user_name,
                'message' => $model->message,
                'post_date' => $model->created_at->__toString(),
                'post_id' => $model->id,
            ]
           );
       };
    }

    /**
     * Ставит оценку новости от текущего пользователя
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function score(Request $request) {
        $sign = $request->post('sign');
        $user_id = $request->user()->id;
        $post_id = $request->post('post_id');
        $user_score = Score::where([
            ['post_id', '=', $post_id],
            ['user_id', '=', $user_id],
        ])->count();
        if (!$user_score) {
            $score_model = new Score();
            $score_model->post_id = $post_id;
            $score_model->user_id = $user_id;
            $score_model->sign = $sign;
            $score_model->save();
            return response()->json(['change' => true]);
        } else {
            $score_model = Score::where([
                ['post_id', '=', $post_id],
                ['user_id', '=', $user_id],
            ])->first();
            if ($score_model->sign !== $sign) {
                $score_model->sign = $sign;
                $score_model->save();
                return response()->json(['change' => true]);
            }
            return response()->json(['change' => false]);
        }
    }

    /**
     * Список страниц, имеюющих не менее 5 плюсов
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function top(){
        $top_posts = DB::select( "
            select 
                posts.id,
                posts.user_id, 
                users.name,
                posts.message, 
                posts.created_at,
                count(scores.id) as pluses 
            from 
                users, posts
            left join scores on(posts.id = scores.post_id) 
            where 
                scores.sign = 'plus' and posts.user_id = users.id 
            group by posts.id having pluses >= 5;
        ");
        return view('posts_list', ['posts' => $this->prepareDataToOutput($top_posts)]);
    }

    /**
     * Получить текущую репутацию пользователя
     * @return null|int
     */
    private function getUserScore() {
        if (!Auth::user()) {
            return null;
        }
        $user_id = Auth::user()->id;
        $plus_count = DB::select("select count(*) as count
            from scores 
            left join posts on(scores.post_id = posts.id) 
            where posts.user_id = {$user_id} and scores.sign = 'plus'"
        );
        $minus_count = DB::select("select count(*) as count
            from scores 
            left join posts on(scores.post_id = posts.id) 
            where posts.user_id = {$user_id} and scores.sign = 'minus'"
        );
        return reset($plus_count)->count - reset($minus_count)->count;
    }

    /**
     * Получить текущую репутацию поста
     * @param $id
     * @return int
     */
    private function getPostScore($id) {
        $plus_count = Score::where([['post_id','=' ,$id], ['sign', '=', 'plus']])->count();
        $minus_count = Score::where([['post_id','=' ,$id], ['sign', '=', 'minus']])->count();
        return $plus_count - $minus_count;
    }

    /**
     * Просмотр конкретной новости
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($id) {
        $post = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select([
                'posts.id',
                'posts.user_id',
                'users.name',
                'posts.message',
                'posts.created_at'
            ])->where(['posts.id' => (int)$id])
            ->get();
            $post_data = $this->prepareDataToOutput($post);
            return view(
               'post_view',
               [
                   'post' => reset($post_data),
                   'comments' => $this->getCommentsData($id)
               ]);
    }

    /**
     * Комментирует новость
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(Request $request) {
        $text = $request->post('text');
        $post_id = $request->post('post_id');
        $this->validate($request, ['text' => 'required'],['text.required' => 'Нельзя оставлять пустой комментарий!']);
        $model = new \App\Comment();
        $model->user_id = $request->user()->id;
        $model->text = $text;
        $model->post_id = (int) $post_id;

        if ($model->save()) {
            $user_name = $request->user()->name;

            return response()->json([
                    'author_name' => $user_name,
                    'text' => $model->message,
                    'comment_date' => $model->created_at->__toString(),
                    'comment_id' => $model->id,
                ]
            );
        };
    }

    /**
     * Получает комментарии по id поста
     * @param $post_id
     * @return array
     */
    private function getCommentsData($post_id) {
        $comments = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select([
                'comments.id',
                'comments.user_id',
                'comments.created_at',
                'comments.text',
                'users.name'
            ])->where(['comments.post_id' => (int) $post_id])
            ->get();
        $comments_data = [];
        foreach ($comments as $comment) {
            $comments_data[] = [
                'author_name' => $comment->name,
                'text' => $comment->text,
                'comment_date' => $comment->created_at,
                'comment_id' => $comment->id,
            ];
        }
        return $comments_data;
    }
}
