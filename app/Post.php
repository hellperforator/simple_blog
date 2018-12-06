<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function comments() {
        return $this->hasMany('App\Comment');
    }

    public function scores() {
        return $this->hasMany('App\Score');
    }

    public function getPostsData($user_id = null, $post_id = 0) {
        if ($post_id) {
            $post = self::find($post_id);
            $posts = [$post];
        } else {
            $posts = self::all();
        }
        $data = [];

        foreach ($posts as $post) {
            $active_button_flag = 0;

            if (!is_null($user_id)) {
                $post_user_score = $post->score->where('user_id', $user_id)->first();

                if ($post_user_score) {
                    $active_button_flag = $this->getActiveButtonFlag($post_user_score, $user_id);
                }
            }

            $data[] = [
                'id' => $post->id,
                'name' => $post->user->name,
                'created_at' => "$post->created_at",
                'message' => $post->message,
                'score' => $this->getPostScore($post->id),
                'active_button_flag' => $active_button_flag,
            ];
        }
        return $data;
    }

    /**
     * Получить флаг активности кнопок плюс и минус
     * 0  - обе кнопки активны
     * -1 - активен минус
     * 1  - активен плюс
     * @param $score
     * @param null $user_id
     * @return int|null
     */
    private function getActiveButtonFlag($score , $user_id = 0) {
        if (is_null($user_id)) {
            return 0;
        }
        $score_model = Score::where([
            ['post_id','=' ,$score->post->id],
            ['user_id', '=', $user_id]
        ]);

        $active_button_flag = null;

        if ($score_model->count()) {
            $active_button_flag = $score_model->first()->sign === 'plus' ? -1 : 1;
        }
        return $active_button_flag;
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

}
