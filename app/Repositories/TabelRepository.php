<?php

namespace App\Repositories;

use App\Models\Chetvert;
use App\Models\ClassSubgroup;
use App\Models\Predmet;
use App\Models\Student;

class TabelRepository
{
    protected $chetvertModel;
    protected $predmetModel;
    protected $lang;

    public function __construct(Chetvert $chetvertModel, Predmet $predmetModel)
    {
        $this->chetvertModel = $chetvertModel;
        $this->predmetModel = $predmetModel;

        if (app()->getLocale() == 'ru') $this->lang = 'rus';
        else if (app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->chetvertModel->init($id_mektep);
    }


    public function chetvertTabel($id_predmet, $id_teacher) {
        $predmet = $this->getPredmet($id_predmet, $id_teacher);

        $studentsList = $this->getStudentsList($predmet['id_class'], $predmet['subgroup'], $predmet['id_subgroup']);

        $chetvertMarks = $this->chetvertModel
            ->where('id_class', '=', $predmet['id_class'])
            ->where('id_predmet', '=', $predmet['id_predmet'])
            ->get()->all();

        $marks = [];
        foreach ($chetvertMarks as $mark) {
            $marks[$mark['chetvert_nomer']][$mark['id_student']] = $mark['mark'];
        }

        return [
            'students_list' => $studentsList,
            'marks' => $marks
        ];
    }

    public function getPredmet($id_predmet, $id_teacher) {
        $predmet = $this->predmetModel
            ->select(
                $this->predmetModel->getTable().'.id_class as id_class',
                $this->predmetModel->getTable().'.id as id_predmet',
                $this->predmetModel->getTable().'.subgroup as subgroup',
                $this->predmetModel->getTable().'.id_subgroup as id_subgroup',
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

    public function getStudentsList($id_class, $subgroup, $id_subgroup) {
        $studentsList = Student::
        select('id',
            'name',
            'surname',
            'lastname')
            ->where('id_class', '=', $id_class)
            ->get()->all();
        if (!$studentsList) throw new \Exception('Not found',404);

        $studentsListWithFIO = [];
        foreach ($studentsList as $key => $item) {
            $studentsListWithFIO[] = [
                "id" => (int)$item['id'],
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
}
