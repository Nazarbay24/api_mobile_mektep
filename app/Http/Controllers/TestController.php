<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;


class TestController extends Controller
{
    public function test(Request $request) {
//        Redis::set('teacher_token:42', 'asd5f4as621va8df5sssssssssss', 'EX', 1);
//        Redis::set('teacher_token:45', 'dddddddddddddddddddddddddddd');

//        $claims = JWTAuth::getJWTProvider()->decode($request->bearerToken());
//        return auth()->refresh();

        $user_id = JWTAuth::getJWTProvider()->decode($request->bearerToken())['sub'];
        $token = Redis::get('teacher_token:'.$user_id);

        if ($request->bearerToken() != $token) {
            return response()->json(['message' => 'Token is Expired'],401);
        }

        if ($new_token = auth()->refresh()) {
            Redis::set('teacher_token:'.$user_id, $new_token, 'EX', 60*60*24*30);

            return response()->json(['token' => $new_token],402);
        }
        else {
            return response()->json(['message' => 'Token is Expired'],401);
        }
    }
}
