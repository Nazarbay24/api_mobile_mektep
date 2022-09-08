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
        $teacher = auth()->user();
        $this->repository->init((int) $teacher->id_mektep);

        $diary = $this->repository->todayDiary($teacher);

        return response()->json($diary, 200);
    }


    public function diary($locale, $week = -1) {
        $this->repository->init((int) auth()->user()->id_mektep);
        $week += 13;

        $monday = date("Y-m-d", strtotime('monday '.$week.' week'));
        $saturday = date("Y-m-d", strtotime('monday '.$week.' week'));
        date_add(date_create($saturday), date_interval_create_from_date_string("6 day"));

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
