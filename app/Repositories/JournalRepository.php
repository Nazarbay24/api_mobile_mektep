<?php

namespace App\Repositories;

use App\Models\Diary;
use App\Models\Journal;
use App\Models\Journal as Model;
use App\Models\Predmet;
use App\Models\Student;


class JournalRepository
{
    protected $model;
    protected $diaryModel;
    protected $predmetModel;
    protected $studentModel;
    protected $journalModel;
    protected $lang;

    public function __construct(Model $model, Diary $diaryModel, Predmet $predmetModel, Student $studentModel, Journal $journalModel)
    {
        $this->model = $model;
        $this->diaryModel = $diaryModel;
        $this->predmetModel = $predmetModel;
        $this->studentModel = $studentModel;
        $this->journalModel = $journalModel;

        if (app()->getLocale() == 'ru') $this->lang = 'rus';
        else if (app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->model->init($id_mektep);
        $this->diaryModel->init($id_mektep);
        $this->journalModel->init($id_mektep);
    }


    public function journalView($id_predmet, $id_teacher, $chetvert) {
        $predmet = $this->predmetModel
            ->select($this->predmetModel->getTable().'.sagat as sagat',
                    $this->predmetModel->getTable().'.id_class as id_class',
                    $this->predmetModel->getTable().'.id as id_predmet',
                    $this->predmetModel->getTable().'.id_mektep as id_mektep',
                    'mektep_class.class as class',
                    'mektep_class.group as group',
                    'edu_predmet_name.predmet_'.$this->lang.' as predmet_name')
            ->leftJoin('mektep_class', $this->predmetModel->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('edu_predmet_name', 'mektep_predmet.predmet', '=', 'edu_predmet_name.id')
            ->where($this->predmetModel->getTable().'.id', '=', $id_predmet)
            ->where($this->predmetModel->getTable().'.id_teacher', '=', $id_teacher)
            ->first();

        $predmet['class'] = $predmet['class'].'«'.$predmet['group'].'»';
        unset($predmet['group']);

        $studentsList = $this->studentModel
            ->select('id',
                    'name',
                    'surname',
                    'lastname')
            ->where('id_mektep', '=', $predmet['id_mektep'])
            ->where('id_class', '=', $predmet['id_class'])
            ->get()->all();

        $studentsListWithFIO = [];
        foreach ($studentsList as $key => $item) {
            $studentsListWithFIO[] = [
                "id" => $item['id'],
                "fio" => $item['surname'].' '.$item['name'],
                "fio_full" => $item['surname'].' '.$item['name'].' '.$item['lastname']
            ];
        }

        $chetvertDates = config('mektep_config.chetvert');
        $holidays = config('mektep_config.holidays');
        $journalDates = $this->diaryModel
            ->select('date')
            ->where('id_class', '=', $predmet['id_class'])
            ->where('id_predmet', '=', $predmet['id_predmet'])
            ->where('date', '>=', $chetvertDates[$chetvert]['start'])
            ->where('date', '<=', $chetvertDates[$chetvert]['end'])
            ->get()->all();

        foreach($journalDates as $key => $item) {
            if (in_array($item['date'], $holidays)) {
                unset($journalDates[$key]);
            }
        }


        $journalMarksQuery = $this->journalModel
            ->where('jurnal_class_id', '=', $predmet['id_class'])
            ->where('jurnal_predmet', '=', $predmet['id_predmet'])
            ->where('date', '>=', $chetvertDates[$chetvert]['start'])
            ->where('date', '<=', $chetvertDates[$chetvert]['end'])
            ->get()->all();

        $journalMarks = [];
        foreach ($journalMarksQuery as $item) {
            $journalMarks[$item['jurnal_date']][$item['jurnal_lesson']]
        }


        return $journalDates;
    }
}
