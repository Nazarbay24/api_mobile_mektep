<?php

namespace App\Http\Controllers;

use App\Repositories\PlanRepository;
use App\Repositories\SubjectRepository;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    protected $subjectRepository;
    protected $planRepository;

    public function __construct(SubjectRepository $subjectRepository, PlanRepository $planRepository)
    {
        $this->subjectRepository = $subjectRepository;
        $this->planRepository = $planRepository;
    }


    public function subject($locale, $id) {
        $subject = $this->subjectRepository->getSubject($id);
        $plan = $this->planRepository->getPlan($id);

        $data = [
            'subject' => $subject,
            'plan' => $plan
        ];

        return response()->json($data, 200);
    }
}
