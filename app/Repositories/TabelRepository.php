<?php

namespace App\Repositories;

use App\Models\Chetvert;
use App\Models\ClassSubgroup;
use App\Models\CriterialMark;
use App\Models\Journal;
use App\Models\Predmet;
use App\Models\PredmetCriterial;
use App\Models\Student;

class TabelRepository
{
    protected $chetvertModel;
    protected $predmetModel;
    protected $criterialMarkModel;
    protected $journalModel;
    protected $lang;

    public function __construct(Chetvert $chetvertModel,
                                Predmet $predmetModel,
                                CriterialMark $criterialMarkModel,
                                Journal $journalModel)
    {
        $this->chetvertModel = $chetvertModel;
        $this->predmetModel = $predmetModel;
        $this->criterialMarkModel = $criterialMarkModel;
        $this->journalModel = $journalModel;

        if (app()->getLocale() == 'ru') $this->lang = 'rus';
        else if (app()->getLocale() == 'kk') $this->lang = 'kaz';
    }

    public function init(int $id_mektep)
    {
        $this->journalModel->init($id_mektep);
        $this->chetvertModel->init($id_mektep);
        $this->criterialMarkModel->init($id_mektep);
    }


    public function chetvertTabel($id_predmet, $id_teacher) {
        $predmet = $this->getPredmet($id_predmet, $id_teacher);
        $studentsList = $this->getStudentsList($predmet['id_class'], $predmet['subgroup'], $predmet['id_subgroup']);

        $chetvertMarks = $this->chetvertModel
            ->where('id_class', '=', $predmet['id_class'])
            ->where('id_predmet', '=', $predmet['id_predmet'])
            ->orderBy('chetvert_nomer')
            ->get()->all();

        foreach ($chetvertMarks as $mark) {
            $key = array_search($mark['id_student'], array_column($studentsList, 'id'));

            $studentsList[$key][$mark['chetvert_nomer']] = $mark['mark'];
        }

        return [
            'predmet_name' => $predmet['predmet_name'],
            'class' => $predmet['class'],
            'students_count' => count($studentsList),
            'students_list' => $studentsList,
        ];
    }


    public function criterialTabel($id_predmet, $id_teacher, $chetvert) {
        $predmet = $this->getPredmet($id_predmet, $id_teacher);
        $studentsList = $this->getStudentsList($predmet['id_class'], $predmet['subgroup'], $predmet['id_subgroup']);
        $formativeMarks = $this->getFormativeMarks($predmet['id_class'], $predmet['id_predmet'], $chetvert);
        $criterialMarks = $this->getCriterialMarks($predmet['id_class'], $predmet['id_predmet'], $id_teacher);
        $predmetCriterial = $this->getPredmetCriterial($predmet['class_num'], $predmet['predmet'], $predmet['edu_language']);
        if (!$predmetCriterial) throw new \Exception('Not found',404);

        if($predmet['class_num'] == 1){
            $mark2 = range(0,20);
            $mark3 = range(21,50);
            $mark4 = range(51,80);
            $mark5 = range(81,100);
        }else{
            $mark2 = range(0,39);
            $mark3 = range(40,64);
            $mark4 = range(65,84);
            $mark5 = range(85,100);
        }

        $criterialMax[1] = json_decode($predmet['max_ch_1']);
        $criterialMax[2] = json_decode($predmet['max_ch_2']);
        $criterialMax[3] = json_decode($predmet['max_ch_3']);
        $criterialMax[4] = json_decode($predmet['max_ch_4']);

        $sorCount[1] = $predmetCriterial['num_sor_1'];
        $sorCount[2] = $predmetCriterial['num_sor_2'];
        $sorCount[3] = $predmetCriterial['num_sor_3'];
        $sorCount[4] = $predmetCriterial['num_sor_4'];

        $sochCount[1] = $predmetCriterial['num_soch_1'];
        $sochCount[2] = $predmetCriterial['num_soch_2'];
        $sochCount[3] = $predmetCriterial['num_soch_3'];
        $sochCount[4] = $predmetCriterial['num_soch_4'];

        $sochMax = $criterialMax[$chetvert][0];
        $sorMaxAll = abs(array_sum($criterialMax[$chetvert]) - $sochMax);

        foreach ($studentsList as $student_key => $student) {
            $sorTotal = 0;
            $soch = 0;
            for ($i = 0; $i < $sorCount[$chetvert]; $i++) {
                if (isset($criterialMarks[$student['id']][$chetvert][$i+1])) {
                    $sorTotal = $sorTotal + $criterialMarks[$student['id']][$chetvert][$i+1];
                }
            }
            if (isset($criterialMarks[$student['id']][$chetvert][0])) {
                $soch = $criterialMarks[$student['id']][$chetvert][0];
            }

            $formativeProc = round(number_format((($formativeMarks[$student['id']]/10) * 100 * ($sochCount[$chetvert] > 0 ? 0.25 : 0.5)), 1, '.', ''));

            $sorProc = round(number_format((($sorTotal/$sorMaxAll)*100 * ($sochCount[$chetvert] > 0 ? 0.25 : 0.5)), 1, '.', ''));

            $sochProc = round(number_format((($soch/$sochMax)*100 * 0.5), 1, '.', ''));

            $sumProc = round(number_format(($formativeProc + $sorProc + $sochProc), 1, '.', ''));

            if ($sumProc && (($sochCount[$chetvert] > 0 && is_numeric($soch)) || $sochCount[$i] === 0)) {
                if     (in_array($sumProc, $mark2)) $mark = 2;
                elseif (in_array($sumProc, $mark3)) $mark = 3;
                elseif (in_array($sumProc, $mark4)) $mark = 4;
                elseif (in_array($sumProc, $mark5)) $mark = 5;
            }




        }

        return $criterialMarks;
    }






    public function getCriterialMarks($id_class, $id_predmet, $id_teacher) {
        $criterialMarksQuery = $this->criterialMarkModel
            ->where('id_class', '=', $id_class)
            ->where('id_predmet', '=', $id_predmet)
            ->where('id_teacher', '=', $id_teacher)
            ->get()->all();

        $criterialMarks = [];
        foreach ($criterialMarksQuery as $item) {
            $criterialMarks[$item['id_student']][$item['chetvert']][$item['razdel']] = $item['student_score'];
        }

        return $criterialMarks;
    }

    public function getPredmetCriterial($class_num, $predmet, $edu_language) {
        return PredmetCriterial::
                    where('class', '=', $class_num)
                    ->where('predmet', '=', $predmet)
                    ->where('edu_language', '=', $edu_language)
                    ->first();
    }

    public function getPredmet($id_predmet, $id_teacher) {
        $predmet = $this->predmetModel
            ->select(
                $this->predmetModel->getTable().'.id_class as id_class',
                $this->predmetModel->getTable().'.id as id_predmet',
                $this->predmetModel->getTable().'.predmet as predmet',
                $this->predmetModel->getTable().'.subgroup as subgroup',
                $this->predmetModel->getTable().'.id_subgroup as id_subgroup',
                $this->predmetModel->getTable().'.max_ch_1 as max_ch_1',
                $this->predmetModel->getTable().'.max_ch_2 as max_ch_2',
                $this->predmetModel->getTable().'.max_ch_3 as max_ch_3',
                $this->predmetModel->getTable().'.max_ch_4 as max_ch_4',
                'mektep_class.class as class',
                'mektep_class.group as group',
                'mektep_class.edu_language as edu_language',
                'edu_predmet_name.predmet_'.$this->lang.' as predmet_name')
            ->leftJoin('mektep_class', $this->predmetModel->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('edu_predmet_name', 'mektep_predmet.predmet', '=', 'edu_predmet_name.id')
            ->where($this->predmetModel->getTable().'.id', '=', $id_predmet)
            ->where($this->predmetModel->getTable().'.id_teacher', '=', $id_teacher)
            ->first();
        if (!$predmet) throw new \Exception('Not found',404);

        $predmet['class_num'] = $predmet['class'];
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

    public function getFormativeMarks($id_class, $id_predmet, $chetvert) {
        $chetvertDates = config('mektep_config.chetvert');
        $allMarksQuery = $this->journalModel
            ->where('jurnal_class_id', '=', $id_class)
            ->where('jurnal_predmet', '=', $id_predmet)
            ->where('jurnal_date', '>=', $chetvertDates[$chetvert]['start'])
            ->where('jurnal_date', '<=', $chetvertDates[$chetvert]['end'])
            ->get()->all();

        $allMarks = [];
        foreach ($allMarksQuery as $item) {
            if ($item['jurnal_mark'] >= 1 && $item['jurnal_mark'] <= 10) {
                $allMarks[$item['jurnal_student_id']]['marks'][] = $item['jurnal_mark'];
            }
        }
        foreach ($allMarks as $id => $marks) {
            $allMarks[$id]['formative_mark'] = round(array_sum($marks['marks']) / count($marks['marks']), 1);
            unset($allMarks[$id]['marks']);
        }

        return $allMarks;
    }
}
