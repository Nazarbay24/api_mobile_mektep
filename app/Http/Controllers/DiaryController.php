<?php

namespace App\Http\Controllers;

use App\Repositories\DiaryRepository;
use Illuminate\Http\Request;

class DiaryController extends Controller
{
    protected $repository;

    public function __construct(DiaryRepository $repository)
    {
        $this->repository = $repository;
    }


    public function todayDiary() {
        $diary = $this->repository->todayDiary();

        return response()->json($diary, 200);
    }
}
