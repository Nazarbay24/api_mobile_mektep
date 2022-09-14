<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function test() {
        Redis::set('teacher_token:42', 'asd5f4as621va8df5sssssssssss');
        Redis::set('teacher_token:45', 'dddddddddddddddddddddddddddd');

        return Redis::get('teacher_token:42');
    }
}
