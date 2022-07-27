<?php

namespace App\Repositories;

use App\Models\Teacher as Model;
use Illuminate\Notifications\Notifiable;

class TeacherRepository {
    use Notifiable;

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }


    public function login($iin, $password) {
        $userAccounts = $this->model
            ->where('iin', $iin)
            ->where('status', 1)
            ->where('blocked', 0)
            ->where('id_mektep', '>', 0)
            ->get()->all();

        foreach ($userAccounts as $user) {
            if ($user->parol == $password) return $userAccounts;
        }

        return false;
    }


    public function getSchools()
    {
        return $this->model
            ->select('id_mektep as id',
                'specialty',
                'pol',
                'mektepter.name_kaz as name_kk',
                'mektepter.name_rus as name_ru',
                'edu_punkt.oblast_kaz as oblast_kk',
                'edu_punkt.oblast_rus as oblast_ru',
                'edu_punkt.punkt_kaz as punkt_kk',
                'edu_punkt.punkt_rus as punkt_ru')
            ->join('mektepter', $this->model->table.'.id_mektep', '=', 'mektepter.id')
            ->join('edu_punkt', 'mektepter.edu_punkt', '=', 'edu_punkt.id')
            ->where('iin', auth()->user()->iin)
            ->where('status', 1)
            ->where('blocked', 0)
            ->get()->all();
    }


    public function choiceSchool($id) {
        return $this->model
            ->where('iin', auth()->user()->iin)
            ->where('id_mektep', $id)
            ->where('status', 1)
            ->where('blocked', 0)
            ->first();
    }
}
