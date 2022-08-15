<?php

namespace App\Http\Controllers;

use App\Repositories\MessangerRepository;
use Illuminate\Http\Request;

class MessangerController extends Controller
{
    protected $repository;

    public function __construct(MessangerRepository $repository)
    {
        $this->repository = $repository;
    }


    public function classList() {
        $teacher = auth()->user();

        $classList = $this->repository->classList($teacher);

        return response()->json($classList, 200);
    }


    public function studentsList($locale, $id_class) {
        $teacher = auth()->user();

        $studentsList = $this->repository->studentsList($id_class);

        return response()->json($studentsList, 200);
    }
}
