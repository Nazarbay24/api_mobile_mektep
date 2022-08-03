<?php

namespace App\Http\Controllers;

use App\Repositories\TabelRepository;
use Illuminate\Http\Request;

class TabelController extends Controller
{
    protected $repository;

    public function __construct(TabelRepository $repository)
    {
        $this->repository = $repository;
    }


    public function chetvertTabel($locale, $id_predmet) {
        $user = auth()->user();
        $this->repository->init((int) $user->id_mektep);

        $tabel = $this->repository->chetvertTabel($id_predmet, $user->id);

        return response()->json($tabel, 200);
    }


    public function criterialTabel($locale, $id_predmet) {
        $user = auth()->user();
        $this->repository->init((int) $user->id_mektep);
    }
}
