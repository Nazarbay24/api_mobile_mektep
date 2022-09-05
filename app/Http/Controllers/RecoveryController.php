<?php

namespace App\Http\Controllers;

use App\Mail\SendPasswordMail;
use App\Repositories\RecoveryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RecoveryController extends Controller
{
    protected $repository;

    public function __construct(RecoveryRepository $repository)
    {
        $this->repository = $repository;
    }


    public function findUser(Request $request) {
        $surname = $request->input('surname');
        $iin = $request->input('iin');
        $email = $request->input('email');

        $res = $this->repository->findUser($surname, $iin, $email);

        if     ($res['code'] == 404) return response()->json([$res['message']], 404);
        elseif ($res['code'] == 200) return response()->json($res['message'], 200);
    }

    public function sendPasswordMail(Request $request) {
        $surname = $request->input('surname');
        $iin = $request->input('iin');
        $email = $request->input('email');

        $res = $this->repository->findUser($surname, $iin, $email);

        if ($res['code'] == 200) {
            Mail::to('uzen57@gmail.com')->send(new SendPasswordMail('userasd', 'pasasd'));

            return response()->json('На вашу почту отправлено сведение
									   о восстановлении доступа, пожалуйста, проверьте указанную почту
									   (обратите внимание, в некоторых случаях письмо может оказаться в Спам-папке).
									   В целях безопасности, повторный запрос на эту почту вы можете
									   сделать через 30 мин.', 200);
        }
    }
}
