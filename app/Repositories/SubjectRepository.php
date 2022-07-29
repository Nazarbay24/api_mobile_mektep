<?php

namespace App\Repositories;

use App\Models\Diary;
use App\Models\Predmet as Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class SubjectRepository
{
    use Notifiable;

    protected $model;
    protected $diaryModel;
    protected $lang;

    public function __construct(Model $model, Diary $diaryModel)
    {
        $this->model = $model;
        $this->diaryModel = $diaryModel;

        if(app()->getLocale() == 'ru') $this->lang = 'rus';
        else if(app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->diaryModel->init($id_mektep);
    }


    public function getSubject($id) {
        $subject = $this->model
            ->select($this->model->getTable().'.id as id',
                    'sagat',
                    DB::raw('count('.$this->diaryModel->getTable().'.id) as sagat_passed'),
                    'subgroup',
                    'edu_predmet_name.predmet_'.$this->lang.' as predmet_name',
                    'mektep_class.class as class',
                    'mektep_class.group as group',
                    'mektep_class.edu_language as lang')
            ->leftJoin('edu_predmet_name', $this->model->getTable().'.predmet', '=', 'edu_predmet_name.id')
            ->leftJoin('mektep_class', $this->model->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('mektep_diary_'.auth()->user()->id_mektep.'_'.config('mektep_config.year'), function($join)
            {
                $join->on($this->model->getTable().'.id', '=', $this->diaryModel->getTable().'.id_predmet');
                $join->on($this->model->getTable().'.id_teacher', '=', $this->diaryModel->getTable().'.id_teacher');
                $join->where('submitted', '=', '1');
            })
            ->where($this->model->getTable().'.id', '=', $id)
            ->where($this->model->getTable().'.id_teacher', '=', auth()->user()->id)
            ->groupBy($this->model->getTable().'.id')
            ->first();

        $subject['lang'] = $subject['lang'] == 1 ? __('Казахский') : __('Русский');
        $subject['progress'] = round(($subject['sagat_passed'] / $subject['sagat']) * 100).'%';
        $subject['left'] = $subject['sagat'] - $subject['sagat_passed'];
        $subject['class'] = $subject['class'].'«'.$subject['group'].'»';
        unset($subject['group']);

        return $subject;
    }


    public function mySubjects() {
        $mySubjects = $this->model
            ->select($this->model->getTable().'.id as id',
                'sagat',
                DB::raw('count('.$this->diaryModel->getTable().'.id) as sagat_passed'),
                'subgroup',
                'edu_predmet_name.predmet_'.$this->lang.' as predmet_name',
                'mektep_class.class as class',
                'mektep_class.group as group')
            ->leftJoin('edu_predmet_name', $this->model->getTable().'.predmet', '=', 'edu_predmet_name.id')
            ->leftJoin('mektep_class', $this->model->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin($this->diaryModel->getTable(), function($join)
            {
                $join->on($this->model->getTable().'.id', '=', $this->diaryModel->getTable().'.id_predmet');
                $join->on($this->model->getTable().'.id_teacher', '=', $this->diaryModel->getTable().'.id_teacher');
                $join->where('submitted', '=', '1');
            })
            ->where($this->model->getTable().'.id_teacher', '=', auth()->user()->id)
            ->groupBy($this->model->getTable().'.id')
            ->get()->all();

        foreach ($mySubjects as $key => $item) {
            $prev_tema = $this->diaryModel
                ->select('tema')
                ->where('id_teacher', '=', auth()->user()->id)
                ->where('id_predmet', '=', $item['id'])
                ->where('submitted', '=', 1)
                ->orderBy('date', 'desc')
                ->first();

            $mySubjects[$key]['prev_tema'] = $prev_tema->tema;
            $mySubjects[$key]['progress'] = round(($item['sagat_passed'] / $item['sagat']) * 100).'%';
            $mySubjects[$key]['class'] = $item['class'].'«'.$item['group'].'»';
            unset($mySubjects[$key]['group']);
            unset($mySubjects[$key]['sagat']);
            unset($mySubjects[$key]['sagat_passed']);
        }

        return $mySubjects;
    }
}
