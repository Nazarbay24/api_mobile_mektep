<?php

namespace App\Repositories;

use App\Models\Diary as Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiaryRepository
{
    use Notifiable;

    protected $model;
    protected $lang;

    public function __construct(Model $model)
    {
        $this->model = $model;

        if(app()->getLocale() == 'ru') $this->lang = 'rus';
        else if(app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->model->init($id_mektep);
    }


    public function todayDiary() {
        $diary = $this->model
            ->select(
                $this->model->getTable().'.date',
                $this->model->getTable().'.number',
                $this->model->getTable().'.id_predmet',
                $this->model->getTable().'.tema',
                'mektep_class.class as class',
                'mektep_class.group as group',
                'mektep_class.smena as smena',
                'mektep_predmet.subgroup as subgroup',
                'edu_predmet_name.predmet_'.$this->lang.' as predmet_name')
            ->leftJoin('mektep_class', $this->model->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('mektep_predmet', $this->model->getTable().'.id_predmet', '=', 'mektep_predmet.id')
            ->leftJoin('edu_predmet_name', 'mektep_predmet.predmet', '=', 'edu_predmet_name.id')
            ->where($this->model->getTable().'.id_teacher', auth()->user()->id)
            ->where($this->model->getTable().'.date', '2021-10-07')//заменить на текущую дату date("Y-m-d")
            ->orderBy($this->model->getTable().'.number', 'asc')
            ->get()->all();

        foreach ($diary as $key => $item) {
            $prev_tema = $this->model
                ->select('tema')
                ->where('id_teacher', '=', auth()->user()->id)
                ->where('id_predmet', '=', $item['id_predmet'])
                ->where('date', '<', $item['date'])
                ->where('submitted', '=', 1)
                ->orderBy('date', 'desc')
                ->first();

            $diary[$key]['prev_tema'] = $prev_tema['tema'] != null ? $prev_tema['tema'] : __("Не задано");
            $diary[$key]['tema'] = $item['tema'] != null ? $item['tema'] : __("Не задано");
        }

        return $this->setDiaryTimeAndClass($diary);
    }


    public function diary($week) {
        $monday = date("Y-m-d", strtotime('monday '.$week.' week'));
        $saturday = date("Y-m-d", strtotime('saturday '.($week+1).' week'));

        $smenaQuery = DB::table('mektep_smena')
            ->where('id_mektep', '=', auth()->user()->id_mektep)
            ->get()->all();
        $smenaQuery = json_decode(json_encode($smenaQuery), true);

        $smenaTime = [];
        foreach ($smenaQuery as $item) {
            for ($i = 1; $i <= 10; $i++) {
                $smenaTime[$item['smena']][$i] = $item['z'.$i.'_start'].' - '.$item['z'.$i.'_end'];
            }
        }

        $weekDiary = $this->model
            ->select('date',
                    'number',
                    'edu_predmet_name.predmet_'.$this->lang.' as predmet_name',
                    'mektep_class.class as class',
                    'mektep_class.group as group',
                    'mektep_class.smena as smena',
                    'mektep_predmet.subgroup as subgroup',
                    'mektep_predmet.id as predmet_id',)
            ->leftJoin('mektep_predmet', $this->model->getTable().'.id_predmet', '=', 'mektep_predmet.id')
            ->leftJoin('edu_predmet_name', 'mektep_predmet.predmet', '=', 'edu_predmet_name.id')
            ->leftJoin('mektep_class', $this->model->getTable().'.id_class', '=', 'mektep_class.id')
            ->where($this->model->getTable().'.id_teacher', '=', auth()->user()->id)
            ->where($this->model->getTable().'.date', '>=', $monday)
            ->where($this->model->getTable().'.date', '<=', $saturday)
            ->orderBy($this->model->getTable().'.date')
            ->orderBy('mektep_class.smena')
            ->orderBy($this->model->getTable().'.number')
            ->orderBy($this->model->getTable().'.id')
            ->get()->all();

        return $this->setDiaryTimeAndClass($weekDiary);
    }


    public function setDiaryTimeAndClass($diaryArray) {
        $smenaQuery = DB::table('mektep_smena')
            ->where('id_mektep', '=', auth()->user()->id_mektep)
            ->get()->all();
        $smenaQuery = json_decode(json_encode($smenaQuery), true);

        $smenaTime = [];
        foreach ($smenaQuery as $item) {
            for ($i = 1; $i <= 10; $i++) {
                $smenaTime[$item['smena']][$i] = $item['z'.$i.'_start'].' - '.$item['z'.$i.'_end'];
            }
        }

        foreach ($diaryArray as $key => $item) {
            $diaryArray[$key]['class'] = $item['class'].'«'.$item['group'].'»';
            unset($diaryArray[$key]['group']);

            $diaryArray[$key]['time'] = $smenaTime[$item['smena']][$item['number']];

            $day = date('w', strtotime($item['date']));
            $diaryArray[$key]['day_number'] = $day;
            switch ($day) {
                case 1:  $diaryArray[$key]['day'] = __('Понедельник');   break;
                case 2:  $diaryArray[$key]['day'] = __('Вторник');       break;
                case 3:  $diaryArray[$key]['day'] = __('Среда');         break;
                case 4:  $diaryArray[$key]['day'] = __('Четверг');       break;
                case 5:  $diaryArray[$key]['day'] = __('Пятница');       break;
                case 6:  $diaryArray[$key]['day'] = __('Суббота');       break;
                case 0:  $diaryArray[$key]['day'] = __('Воскресенье');   break;
            }
        }

        return $diaryArray;
    }
}
