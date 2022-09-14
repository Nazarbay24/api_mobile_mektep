<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function test() {
        Redis::set('name', 'alan');

        return Redis::get('name');
    }
}
