<?php

namespace App\Repositories;

use App\Models\Diary;
use App\Models\Journal as Model;
use App\Models\Predmet;


class JournalRepository
{
    protected $model;
    protected $diaryModel;
    protected $predmetModel;
    protected $lang;

    public function __construct(Model $model, Diary $diaryModel, Predmet $predmetModel)
    {
        $this->model = $model;
        $this->diaryModel = $diaryModel;
        $this->predmetModel = $predmetModel;

        if (app()->getLocale() == 'ru') $this->lang = 'rus';
        else if (app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->model->init($id_mektep);
        $this->diaryModel->init($id_mektep);
    }


    public function journalView($id_predmet, $id_teacher, $chetvert) {
        $predmet = $this->predmetModel
            ->select($this->predmetModel->getTable().'.sagat as sagat',
                    $this->predmetModel->getTable().'.id_class as id_class',
                    'mektep_class.class as class',
                    'mektep_class.group as group',
                    'edu_predmet_name.predmet_'.$this->lang.' as predmet_name')
            ->leftJoin('mektep_class', $this->predmetModel->getTable().'.id_class', '=', 'mektep_class.id')
            //->leftJoin('mektep_predmet', $this->model->getTable().'.id_predmet', '=', 'mektep_predmet.id')
            ->leftJoin('edu_predmet_name', 'mektep_predmet.predmet', '=', 'edu_predmet_name.id')
            ->where($this->predmetModel->getTable().'.id', '=', $id_predmet)
            ->where($this->predmetModel->getTable().'.id_teacher', '=', $id_teacher)
            ->first();

        $predmet =

        return $predmet;
    }
}
