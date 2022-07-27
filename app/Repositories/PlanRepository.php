<?php

namespace App\Repositories;

use App\Models\Plan as Model;
use Illuminate\Notifications\Notifiable;

class PlanRepository
{
    use Notifiable;

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }


    public function getPlan($predmetId) {
        return $this->model
            ->select()
            ->where('mektep_predmet_id', '=', $predmetId)
            ->where('teacher_id', '=', auth()->user()->id)
            ->orderBy('id', 'asc')
            ->get()->all();
    }

}
