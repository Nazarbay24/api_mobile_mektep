<?php

namespace App\Repositories;

use App\Models\Diary as Model;
use Illuminate\Notifications\Notifiable;

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


    public function todayDiary() {
        $diary = $this->model
            ->select($this->model->getTable().'.id as id',
                $this->model->getTable().'.id_class as id_class',
                $this->model->getTable().'.date',
                $this->model->getTable().'.number',
                $this->model->getTable().'.id_predmet',
                $this->model->getTable().'.tema',
                $this->model->getTable().'.submitted',
                $this->model->getTable().'.opened',
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
                ->where('id_class', '=', $item['id_class'])
                ->where('id_predmet', '=', $item['id_predmet'])
                ->where('date', '<', $item['date'])
                ->orderBy('date', 'desc')
                ->first();

            $diary[$key]['prev_tema'] = $prev_tema['tema'];
        }

        return $diary;
    }
}
