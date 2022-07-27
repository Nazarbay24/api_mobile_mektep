<?php

namespace App\Repositories;

use App\Models\Predmet as Model;
use Illuminate\Notifications\Notifiable;

class SubjectRepository
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


    public function getSubject($id) {
        $subject = $this->model
            ->select($this->model->getTable().'.id as id',
                    'sagat',
                    'subgroup',
                    'edu_predmet_name.predmet_'.$this->lang.' as predmet_name',
                    'mektep_class.class as class',
                    'mektep_class.group as group',
                    'mektep_class.edu_language as lang')
            ->leftJoin('edu_predmet_name', $this->model->getTable().'.predmet', '=', 'edu_predmet_name.id')
            ->leftJoin('mektep_class', $this->model->getTable().'.id_class', '=', 'mektep_class.id')
            ->where($this->model->getTable().'.id', '=', $id)
            ->where($this->model->getTable().'.id_teacher', '=', auth()->user()->id)
            ->first();

        $subject['lang'] = $subject['lang'] == 1 ? __('Казахский') : __('Русский');

        return $subject;
    }


    public function mySubjects() {
        $subject = $this->model
            ->select($this->model->getTable().'.id as id',
                'sagat',
                'subgroup',
                'edu_predmet_name.predmet_'.$this->lang.' as predmet_name',
                'mektep_class.class as class',
                'mektep_class.group as group')
            ->leftJoin('edu_predmet_name', $this->model->getTable().'.predmet', '=', 'edu_predmet_name.id')
            ->leftJoin('mektep_class', $this->model->getTable().'.id_class', '=', 'mektep_class.id')
            ->leftJoin('bookings', function($join)
            {
                $join->on('rooms.id', '=', 'bookings.room_type_id');
                $join->on('arrival','>=',DB::raw("'2012-05-01'"));
                $join->on('arrival','<=',DB::raw("'2012-05-10'"));
                $join->on('departure','>=',DB::raw("'2012-05-01'"));
                $join->on('departure','<=',DB::raw("'2012-05-10'"));
            })
            ->where($this->model->getTable().'.id_teacher', '=', auth()->user()->id)
            ->get()->all();
    }

}
