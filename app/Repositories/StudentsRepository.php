<?php

namespace App\Repositories;

use App\Models\Chetvert;
use App\Models\Predmet;
use App\Models\Student;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;


class StudentsRepository
{
    use Notifiable;

    protected $model;
    protected $predmetModel;
    protected $chetvertModel;
    protected $lang;

    public function __construct(Student $model, Predmet $predmetModel, Chetvert $chetvertModel)
    {
        $this->model = $model;
        $this->predmetModel = $predmetModel;
        $this->chetvertModel = $chetvertModel;

        if (app()->getLocale() == 'ru') $this->lang = 'rus';
        else if (app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->model->init($id_mektep);
        $this->chetvertModel->init($id_mektep);
    }

    public function classList($teacher) {
        $classList = $this->predmetModel
            ->select('mektep_class.id as class_id',
		        'mektep_class.class as class',
		        'mektep_class.group as group',
		        'mektep_class.edu_language as lang',
                'mektep_teacher.id as kurator_id',
		        'mektep_teacher.surname as kurator_surname',
		        'mektep_teacher.name as kurator_name')
            ->join('mektep_class', $this->predmetModel->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('mektep_teacher', 'mektep_class.kurator', '=', 'mektep_teacher.id')
            ->where($this->predmetModel->getTable().'.id_teacher', '=', $teacher->id)
            ->where($this->predmetModel->getTable().'.id_mektep', '=', $teacher->id_mektep)
            ->where($this->predmetModel->getTable().'.id_mektep', '=', $teacher->id_mektep)
            ->orderBy('class')
            ->orderBy('group')
            ->get()->all();

        foreach ($classList as $key => $item) {
            $classList[$key]['class'] = $item['class'].'«'.$item['group'].'»';
            unset($classList[$key]['group']);
        }

        return $classList;
    }


    public function studentsList($id_class) {
        $classInfo = $this->predmetModel
            ->select('mektep_class.id as class_id',
                'mektep_class.class as class',
                'mektep_class.group as group',
                'mektep_class.edu_language as lang',
                'mektep_teacher.id as kurator_id',
                'mektep_teacher.surname as kurator_surname',
                'mektep_teacher.name as kurator_name')
            ->join('mektep_class', $this->predmetModel->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('mektep_teacher', 'mektep_class.kurator', '=', 'mektep_teacher.id')
            ->where($this->predmetModel->getTable().'.id_class', '=', $id_class)
            ->first();

        $classInfo['class'] = $classInfo['class'].'«'.$classInfo['group'].'»';
        unset($classInfo['group']);


        $chetvertMarks = $this->chetvertModel
            ->select('id_student',
                DB::raw('count(id_chet)'),
                DB::raw('sum(mark)'),
                DB::raw('sum(mark)/count(id_chet) as mark'))
            ->where('id_class', '=', $id_class)
            ->where('mark', '>=', 1)
            ->where('mark', '<=', 5)
            ->groupBy('id_student')
            ->get()->all();

        $ulgerim = [];
        foreach ($chetvertMarks as $item) {
            $ulgerim[$item['id_student']] = number_format($item['mark'], 1);;
        }

        $students = Student::
            select('id',
                'name',
                'surname',
                'lastname')
                ->where('id_class', '=', $id_class)
                ->orderBy('surname')
                ->get()->all();

        $studentsList = [];
        foreach ($students as $key => $item) {
            $studentsList[] = [
                "id" => (int)$item['id'],
                "fio" => $item['surname'].' '.$item['name'],
                "avg_grade" => $ulgerim[$item['id']] ? $ulgerim[$item['id']] : '0',
            ];
        }

        return [
            'class_info' => $classInfo,
            'students' => $studentsList
        ];
    }


    public function studentTabel($id_student) {
        $student = Student::where('id', '=', $id_student)->first();

        $chetvertMarksQuery = $this->chetvertModel
            ->where('id_student', '=', $id_student)
            ->get()->all();

        $chetvertMarks = [];
        foreach ($chetvertMarksQuery as $item) {
            $chetvertMarks[$item['id_predmet']][$item['chetvert_nomer']] = strval($item['mark']);
        }

        $predmets = $this->predmetModel
            ->select($this->predmetModel->getTable().'.id as id',
                'mektep_teacher.name',
                'mektep_teacher.surname',
                'edu_predmet_name.predmet_'.$this->lang.' as predmet_name')
            ->leftJoin('edu_predmet_name', $this->predmetModel->getTable().'.predmet', '=', 'edu_predmet_name.id')
            ->leftJoin('mektep_teacher', $this->predmetModel->getTable().'.id_teacher', '=', 'mektep_teacher.id')
            ->where($this->predmetModel->getTable().'.id_class', '=', $student['id_class'])
            ->where($this->predmetModel->getTable().'.id_mektep', '=', $student['id_mektep'])
            ->orderBy($this->predmetModel->getTable().'.predmet')
            ->get()->all();

        foreach ($predmets as $key => $predmet) {
            $predmets[$key]['teacher'] = $predmet['surname'].' '.$predmet['name'];
            unset($predmet['name']);
            unset($predmet['surname']);

            if (array_key_exists($predmet['id'], $chetvertMarks)) {
                foreach ($chetvertMarks[$predmet['id']] as $chetvert => $mark) {
                    $predmets[$key][$chetvert] = $mark;
                }
            }
            unset($predmet['id']);
        }

        return $predmets;
    }
}
