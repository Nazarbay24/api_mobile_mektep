<?php

namespace App\Http\Controllers;


use App\Repositories\TeacherRepository;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    protected $repository;

    public function __construct(TeacherRepository $repository)
    {
        $this->repository = $repository;
    }


    public function login(Request $request)
    {
        $request->validate([
            "iin" => "required|size:12",
            "password" => "required"
        ]);

        $userAccounts = $this->repository->login(trim($request->input('iin')), trim($request->input('password')));

        if($userAccounts) {
            return response()->json([
                'token' => $userAccounts[0]->generateAuthToken(),
                'name' => $userAccounts[0]->name,
                'surname' => $userAccounts[0]->surname,
                'lastname' => $userAccounts[0]->lastname,
                'pol' => $userAccounts[0]->pol,
            ], 200);
        }

        return response()->json(['message' => __('Неправильный ИИН или пароль')], 404);
    }


    public function getSchools()
    {
        $schools = $this->repository->getSchools();

        if ($schools) {
            return response()->json($schools, 200);
        }
        else {
            return response()->json(['message' => __('Школа не найдена')], 404);
        }
    }


    public function choiceSchool($loacle, $id)
    {
        $user = $this->repository->choiceSchool($id);

        if ($user) return response()->json($user->generateAuthToken(), 200);
        else       return response()->json(['message' => __('Школа не найдена')], 404);
    }
}
