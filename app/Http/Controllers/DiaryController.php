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


    public function diary($locale, $week = -1) {
        $this->repository->init((int) auth()->user()->id_mektep);

        $monday = date("Y-m-d", strtotime('monday '.$week.' week'));
        $saturday = date("Y-m-d", strtotime('saturday '.($week).' week'));
        $diary = $this->repository->diary($monday, $saturday);

        $monday = date("d.m", strtotime($monday));
        $saturday = date("d.m", strtotime($saturday));

        return response()->json([
            "week" => $week,
            "week_date" => $monday.' - '.$saturday,
            "diary" => $diary
        ], 200);
    }
}
