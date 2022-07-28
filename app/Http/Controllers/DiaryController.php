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
        $this->repository->init((int) auth()->user()->id_mektep);

        $diary = $this->repository->todayDiary();

        return response()->json($diary, 200);
    }


    public function diary($locale, $week) {
        $this->repository->init((int) auth()->user()->id_mektep);

        $diary = $this->repository->diary($week);



        return $diary;
    }
}
