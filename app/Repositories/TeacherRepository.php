<?php

namespace App\Repositories;

use App\Models\Teacher as Model;
use App\Models\User;
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
            ->select('mektep_teacher.id as id',
                    'id_mektep',
                    'name',
                    'surname',
                    'lastname',
                    'iin',
                    'parol',
                    'birthday',
                    'pol',
                    'specialty',
                    'mektepter.bin as bin',
                    'mektepter.name_kaz as mektep_name_kaz',
                    'mektepter.name_rus as mektep_name_rus',
                    'edu_punkt.oblast_kaz as oblast_kaz',
                    'edu_punkt.oblast_rus as oblast_rus',
                    'edu_punkt.punkt_kaz as punkt_kaz',
                    'edu_punkt.punkt_rus as punkt_rus')
            ->join('mektepter', $this->model->table.'.id_mektep', '=', 'mektepter.id')
            ->join('edu_punkt', 'mektepter.edu_punkt', '=', 'edu_punkt.id')
            ->where('iin', $iin)
            ->get()->all();


        foreach ($userAccounts as $user) {
            if ($user->parol == $password) return $userAccounts;
        }

        return false;
    }

    public function accountChoice($iin, $id_mektep)
    {
        return $this->model
            ->select('mektep_teacher.id as id',
            'id_mektep',
            'name',
            'surname',
            'lastname',
            'iin',
            'parol',
            'birthday',
            'pol',
            'specialty',
            'mektepter.bin as bin',
            'mektepter.name_kaz as mektep_name_kaz',
            'mektepter.name_rus as mektep_name_rus',
            'edu_punkt.oblast_kaz as oblast_kaz',
            'edu_punkt.oblast_rus as oblast_rus',
            'edu_punkt.punkt_kaz as punkt_kaz',
            'edu_punkt.punkt_rus as punkt_rus')
            ->join('mektepter', $this->model->table.'.id_mektep', '=', 'mektepter.id')
            ->join('edu_punkt', 'mektepter.edu_punkt', '=', 'edu_punkt.id')
            ->where('iin', $iin)
            ->where('id_mektep', $id_mektep)
            ->first();
    }
}
