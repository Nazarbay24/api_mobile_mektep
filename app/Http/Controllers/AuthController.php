<?php

namespace App\Http\Controllers;

use App\Repositories\TeacherRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;


class AuthController extends Controller
{
    protected $repository;
    protected $lang;

    public function __construct(TeacherRepository $repository, Request $request)
    {
        $this->repository = $repository;
    }


    public function login(Request $request)
    {
        $userAccounts = $this->repository->login($request->input('iin'), $request->input('password'));

        if($userAccounts) {
            return response()->json([
                'token' => $userAccounts[0]->generateAuthToken(),
                'name' => $userAccounts[0]->name,
                'surname' => $userAccounts[0]->surname,
                'lastname' => $userAccounts[0]->lastname,
            ], 200);
        }

        return response()->json(['message' => __('Неправильный ИИН или пароль')], 404);
    }


    public function getSchool()
    {
        $schools = $this->repository->getSchool();

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
