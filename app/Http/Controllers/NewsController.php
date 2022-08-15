<?php

namespace App\Http\Controllers;

use App\Repositories\NewsRepository;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    protected $repository;

    public function __construct(NewsRepository $repository)
    {
        $this->repository = $repository;
    }


    public function newsList() {
        $news = $this->repository->newsList();

        return response()->json($news, 200);
    }


    public function getNew($locale, $id_new) {
        $new = $this->repository->getNewById($id_new);

        return response()->json($new, 200);
    }
}
