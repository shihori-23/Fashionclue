<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use DB;

use App\User;
use App\Taste;
use App\TasteUser;
use App\Post;
use App\Answer;

class UsersController extends Controller
{   
    //　ログインユーザーのuidを取得
    public function uid()
    {
        $user = Auth::user()->id;
        return response()->json(['uid' => $user], 200);
    }

    // ユーザーのプロフィール取得とテイストの取得
    public function show()
    {
        $user = Auth::user(); 
        $user_gender = Auth::user()->gender;               
        $array = array('name'=>$user->name,'email'=>$user->email,'image'=>$user->image,'bio'=>$user->bio,'age'=>$user->age,'gender'=>$user->gender);
        
        //　性別判断し、存在するテイストを取得
        $taste = Taste::where('taste_type','=',$user_gender)->select('id','taste_name')->get();
        //　性別登録をしているか判定
        if ($user_gender === null) {
            $genderNotEntered = false;
        } else {
            $genderNotEntered = true;
        }

        //　選択済のテイストがあれば取得して配列に入れる
        $selectedTastes = TasteUser::where('user_id','=',Auth::id())->select('taste_id')->get();

        return response()->json(['profile'=>$array,'tastes'=>$taste,'notEntered'=>$genderNotEntered,'selectedTastes'=>$selectedTastes], 200);
    }

    //プロフィール編集
    public function edit(Request $request)
    {   
        // バリデーション
        $validatedData = $request->validate([ 
            'name' => 'required | max:20',
            'email' => 'required | email',
            // 'age' => 'integer | nullable',
            'bio' =>  'max:200 | nullable',
            'image' => 'max:3000000 | nullable'
        ]);

        $user = User::find(Auth::id());

        $result = $user->fill($request->all())->save();

        if ($result) {
            $ary = [];
            foreach (array_keys($request->all()) as $keyName) {
                    $ary[$keyName] = $request[$keyName];
            }
        }

        //もしファイルがあれば更新
        if ($request->file('image')) {   
            //ファイルが保存される先の名前
            $user->image = $request->file('image')->store('public/image/profile');
            //保存される名前を決める
            $user->image = str_replace('public/', 'storage/', $user->image);
            $user->save();
        }
        
        return response()->json(['done'=>true,'profile'=>$ary], 200);
    }
    //　テイストの編集
    public function editTaste(Request $request){

        //　Requestを定義
        $selectedTastes = $request->tastes_id;
        $user_id = Auth::id();
        $dt = now();

        //　一旦ログインユーザーのテイストデータを消す
        $oldTastes = TasteUser::where('user_id','=',$user_id)->get();
        if ($oldTastes->isEmpty()) { 

        } else {
            $delete = TasteUser::where('user_id','=',$user_id)->delete();
        }

        $tastes = [];
        for($i = 0; $i < count($selectedTastes); $i++){
            array_push($tastes,['user_id' => $user_id,'taste_id'=> $selectedTastes[$i],'created_at' => $dt,'updated_at' => $dt]);
        }
        $tastesUser = new TasteUser;
        $tastesUser->insert($tastes);

        return response()->json(['done'=>true], 200);
    }

    // 【UserProfileConponent】ユーザーのプロフィール取得
    public function showProfile()
    {
        $user = Auth::user(); 
        $user_gender = Auth::user()->gender;               
        $array = array('name'=>$user->name,'email'=>$user->email,'image'=>$user->image,'bio'=>$user->bio,'age'=>$user->age,'gender'=>$user->gender);
        
        //　選択済のテイストがあれば取得して配列に入れる
        $selectedTastes = DB::table('taste_users as tu')
        ->join('tastes as t', 't.id', '=', 'tu.taste_id')
        ->where('tu.user_id', '=', Auth::id())
        ->select('taste_name')
        ->get();

        //　ログインユーザーの質問投稿を取得
        $userPostData = DB::table('posts as p')
                            ->where('p.user_id', '=', Auth::id())
                            ->select('p.id as post_id','p.text','p.post_image','p.created_at','p.category')
                            ->get();

        //　ログインユーザーの回答投稿を取得
        $userAnswerData = DB::table('answers as a')
                            ->join('posts as p', 'p.id', '=', 'a.post_id')
                            ->where('a.user_id', '=', Auth::id())
                            ->select('a.id','p.id as post_id','a.text','a.answer_image','a.created_at','p.category')
                            ->get();

        return response()->json(['profile'=>$array,'selectedTastes'=>$selectedTastes,'userPostData'=>$userPostData,'userAnswerData'=>$userAnswerData], 200);
    }
}
