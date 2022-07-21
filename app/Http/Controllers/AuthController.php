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
        if (app()->getLocale() == 'kk') $this->lang = 'kaz';
        if (app()->getLocale() == 'ru') $this->lang = 'rus';
    }


    public function login(Request $request)
    {
        $userAccounts = $this->repository->login($request->input('iin'), $request->input('password'));

        $data = [];
        if($userAccounts) {
            if(count($userAccounts) > 1) {

                $token = $userAccounts[0]->generateAuthToken(true);

                $schools = [];

                foreach ($userAccounts as $user) {
                    $schools[] = [
                        'id_mektep' => $user['id_mektep'],
                        'oblast_kaz' => $user['oblast_'.$this->lang],
                        'punkt' => $user['punkt_'.$this->lang],
                        'mektep_name' => $user['mektep_name_'.$this->lang],
                        'specialty' => $user->specialty,
                    ];
                }

                $data = [
                    'token' => $token,
                    'many' => 'true',
                    'user' => [
                        'name' => $userAccounts[0]->name,
                        'surname' => $userAccounts[0]->surname,
                        'lastname' => $userAccounts[0]->lastname,
                    ],
                    'schools' => $schools
                ];
            }
            else {
                $token = $userAccounts[0]->generateAuthToken();

                $data = [
                    'token' => $token,
                    'user' => [
                        'name' => $userAccounts[0]->name,
                        'surname' => $userAccounts[0]->surname,
                        'lastname' => $userAccounts[0]->lastname,
                        'id_mektep' => $userAccounts[0]->id_mektep,
                        'oblast_kaz' => $userAccounts[0]['oblast_'.$this->lang],
                        'punkt' => $userAccounts[0]['punkt_'.$this->lang],
                        'mektep_name' => $userAccounts[0]['mektep_name_'.$this->lang],
                        'specialty' => $userAccounts[0]->specialty,
                    ],
                ];
            }
        }

        if ($data) return response()->json($data);
        else       return response()->json(['message' => __('Неправильный ИИН или пароль')], 404);
    }


    public function accountChoice(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload();

        $user = $this->repository->accountChoice($payload->get('iin'), $request->input('id_mektep'));

        if ($user) {
            $token = $user->generateAuthToken();

            $data = [
                'token' => $token,
                'user' => [
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'lastname' => $user->lastname,
                    'id_mektep' => $user->id_mektep,
                    'oblast_kaz' => $user['oblast_'.$this->lang],
                    'punkt' => $user['punkt_'.$this->lang],
                    'mektep_name' => $user['mektep_name_'.$this->lang],
                    'specialty' => $user->specialty,
                ],
            ];

            return response()->json($data);
        }
        else {
            return response()->json(['message' => __('Неправильный ИИН или пароль')], 404);
        }
    }
}
