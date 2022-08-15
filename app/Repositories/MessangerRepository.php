<?php

namespace App\Repositories;

use App\Models\Message;
use App\Models\ParentModel;
use App\Models\Predmet;
use App\Models\Student;

class MessangerRepository
{
    protected $messageModel;
    protected $parentModel;
    protected $predmetModel;

    public function __construct(Message $messageModel, ParentModel $parentModel, Predmet $predmetModel)
    {
        $this->messageModel = $messageModel;
        $this->parentModel = $parentModel;
        $this->predmetModel = $predmetModel;
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

        $students = Student::
            select('id',
                'name',
                'surname',
                'parent_ata_id',
                'parent_ana_id',)
            ->where('id_class', '=', $id_class)
            ->orderBy('surname')
            ->get()->all();

        $parentsIds = [];

        foreach ($students as $student) {
            if ($student['parent_ata_id'] > 0) $parentsIds[] = $student['parent_ata_id'];
            if ($student['parent_ana_id'] > 0) $parentsIds[] = $student['parent_ana_id'];
        }

        $parentsQuery = $this->parentModel
            ->select('id',
                    'name',
                    'surname',
                    'metka',
                    'last_visit')
            ->whereIn('id', $parentsIds)
            ->where('login', '!=', " ")
            ->where('status', '=', 1)
            ->where('blocked', '=', 0)
            ->get()->all();


        $studentsList = [];
        foreach ($students as $key => $item) {
            $studentsList[] = [
                "id" => (int)$item['id'],
                "fio" => $item['surname'].' '.$item['name'],
            ];
        }

        return [
            'class_info' => $classInfo,
            'students' => $studentsList
        ];
    }
}
