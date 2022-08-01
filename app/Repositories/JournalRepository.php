<?php

namespace App\Repositories;

use App\Models\Chetvert;
use App\Models\ClassSubgroup;
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
    protected $chetvertModel;
    protected $journalModel;
    protected $lang;

    public function __construct(Model $model,
                                Diary $diaryModel,
                                Predmet $predmetModel,
                                Journal $journalModel,
                                Chetvert $chetvertModel)
    {
        $this->model = $model;
        $this->diaryModel = $diaryModel;
        $this->predmetModel = $predmetModel;
        $this->journalModel = $journalModel;
        $this->chetvertModel = $chetvertModel;

        if (app()->getLocale() == 'ru') $this->lang = 'rus';
        else if (app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->model->init($id_mektep);
        $this->diaryModel->init($id_mektep);
        $this->journalModel->init($id_mektep);
        $this->chetvertModel->init($id_mektep);
    }


    public function journalView($id_predmet, $id_teacher, $chetvert, $isCurrentChetvert, $canMark) {
        $predmet = $this->getPredmet($id_predmet, $id_teacher);
        $studentsList = $this->getStudentsList($predmet['id_mektep'], $predmet['id_class'], $predmet['subgroup'], $predmet['id_subgroup']);
        $datesMarksFormative = $this->getDatesMarksFormative($chetvert, $isCurrentChetvert, $predmet['id_predmet'], $predmet['id_class']);



        return [
            'chetvert' => $chetvert,
            'current_date' => $datesMarksFormative['currentDate'],
            'can_mark' => $canMark,
            'predmet' => $predmet,
            'dates' => $datesMarksFormative['journalDates'],
            'marks' => $datesMarksFormative['journalMarks'],
            'formative_marks' => $datesMarksFormative['formativeMarks'],
            'students_list' => $studentsList,
        ];
    }


    public function journalEdit($id_predmet, $id_teacher, $chetvert, $date, $isCurrentChetvert, $canMark) {
        $predmet = $this->getPredmet($id_predmet, $id_teacher);
        $studentsList = $this->getStudentsList($predmet['id_mektep'], $predmet['id_class'], $predmet['subgroup'], $predmet['id_subgroup']);
        $datesMarksFormative = $this->getDatesMarksFormative($chetvert, $isCurrentChetvert, $predmet['id_predmet'], $predmet['id_class']);

        $chetvertDates = config('mektep_config.chetvert');
        $holidays = config('mektep_config.holidays');
        if (!$date) {
            $diary = $this->diaryModel
                ->where('id_class', '=', $predmet['id_class'])
                ->where('id_predmet', '=', $predmet['id_predmet'])
                ->where('date', '>=', $chetvertDates[$chetvert]['start'])
                ->where('date', '<=', $chetvertDates[$chetvert]['end'])
                ->where('date', '<=', "2021-10-07") // заменить на текущую дату date("Y-m-d")
                ->orderBy('date', 'desc')
                ->first();
        }
        else {
            $diary = $this->diaryModel
                ->where('id_class', '=', $predmet['id_class'])
                ->where('id_predmet', '=', $predmet['id_predmet'])
                ->where('date', '=', $date)
                ->first();
        }

        $studentsChetvertMarksQuery = $this->chetvertModel
            ->select('id_student')
            ->where('id_predmet', '=', $predmet['id_predmet'])
            ->where('id_class', '=', $predmet['id_class'])
            ->get()->all();

        $studentsChetvertMarks = [];
        foreach ($studentsChetvertMarksQuery as $item) {
            $studentsChetvertMarks[$item['id_student']] = true;
        }

        foreach ($studentsList as $key => $student) {
            $studentsList[$key]['mark'] = $datesMarksFormative[]
        }



        return [
            'chetvert' => $chetvert,
            'predmet' => $predmet
        ];
    }



    public function getPredmet($id_predmet, $id_teacher) {
        $predmet = $this->predmetModel
            ->select($this->predmetModel->getTable().'.sagat as sagat',
                $this->predmetModel->getTable().'.id_class as id_class',
                $this->predmetModel->getTable().'.id as id_predmet',
                $this->predmetModel->getTable().'.id_mektep as id_mektep',
                $this->predmetModel->getTable().'.subgroup as subgroup',
                'mektep_class.class as class',
                'mektep_class.group as group',
                'edu_predmet_name.predmet_'.$this->lang.' as predmet_name')
            ->leftJoin('mektep_class', $this->predmetModel->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('edu_predmet_name', 'mektep_predmet.predmet', '=', 'edu_predmet_name.id')
            ->where($this->predmetModel->getTable().'.id', '=', $id_predmet)
            ->where($this->predmetModel->getTable().'.id_teacher', '=', $id_teacher)
            ->first();
        if (!$predmet) throw new \Exception('Not found',404);

        $predmet['class'] = $predmet['class'].'«'.$predmet['group'].'»';
        unset($predmet['group']);
        return $predmet;
    }


    public function getStudentsList($id_mektep, $id_class, $subgroup, $id_subgroup) {
        $studentsList = Student::
            select('id',
                'name',
                'surname',
                'lastname')
            ->where('id_mektep', '=', $id_mektep)
            ->where('id_class', '=', $id_class)
            ->get()->all();
        if (!$studentsList) throw new \Exception('Not found',404);

        $studentsListWithFIO = [];
        foreach ($studentsList as $key => $item) {
            $studentsListWithFIO[] = [
                "id" => $item['id'],
                "fio" => $item['surname'].' '.$item['name'],
            ];
        }

        if ($id_subgroup > 0) {
            $subgroup = ClassSubgroup::select('group_students_'.$subgroup.' as ids')->where('id', '=', $id_subgroup);
            $subgroup_students = json_decode($subgroup['ids']);

            foreach ($studentsListWithFIO as $key => $student) {
                if (!in_array($student['id'], $subgroup_students)) {
                    unset($studentsListWithFIO[$key]);
                }
            }
        }

        return $studentsListWithFIO;
    }


    public function getDatesMarksFormative($chetvert, $isCurrentChetvert, $id_predmet, $id_class) {
        $chetvertDates = config('mektep_config.chetvert');
        $holidays = config('mektep_config.holidays');
        $journalDatesQuery = $this->diaryModel
            ->select('date')
            ->where('id_class', '=', $id_class)
            ->where('id_predmet', '=', $id_predmet)
            ->where('date', '>=', $chetvertDates[$chetvert]['start'])
            ->where('date', '<=', $chetvertDates[$chetvert]['end'])
            ->orderBy('date')
            ->get()->all();

        $journalDates = [];
        foreach($journalDatesQuery as $key => $item) {
            if (!in_array($item['date'], $holidays)) {
                $journalDates[$item['date']] = date("d.m", strtotime($item['date']));
            }
            $currentDate = $isCurrentChetvert && $item['date'] <= '2021-10-07'/*date("Y-m-d")*/ ? date("d.m", strtotime($item['date'])) : false; // заменить на текущую дату
        }


        $journalMarksQuery = $this->journalModel
            ->where('jurnal_class_id', '=', $id_class)
            ->where('jurnal_predmet', '=', $id_predmet)
            ->where('jurnal_date', '>=', $chetvertDates[$chetvert]['start'])
            ->where('jurnal_date', '<=', $chetvertDates[$chetvert]['end'])
            ->get()->all();

        $journalMarks = [];
        foreach ($journalMarksQuery as $item) {
            $journalMarks[$item['jurnal_date']][$item['jurnal_lesson']][$item['jurnal_student_id']] = $item['jurnal_mark'];
        }


        $formativeMarks = [];
        $ff = [];
        foreach ($journalMarks as $date) {
            foreach ($date as $lesson) {
                foreach ($lesson as $id_student => $mark) {
                    if ($mark >= 1 && $mark <= 10) {
                        $ff[$id_student]['marks'][] = $mark;
                        $formativeMarks[$id_student] = round(array_sum($ff[$id_student]['marks']) / count($ff[$id_student]['marks']), 1);
                    }
                }
            }
        }

        return [
            'currentDate' => $currentDate,
            'journalDates' => $journalDates,
            'journalMarks' => $journalMarks,
            'formativeMarks' => $formativeMarks,
        ];
    }
}
