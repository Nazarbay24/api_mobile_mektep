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
        $newsList = [];

        foreach ($news as $item) {
            if ($item['image_url'] == '') {
                $item['image_url'] = 'https://mobile.mektep.edu.kz/uploads/images/default_background.jpg';
            }
            $newsList[] = $item;
        }

        return response()->json($newsList, 200);
    }


    public function getNew($locale, $id_new) {
        $new = $this->repository->getNewById($id_new);

        if ($new['image_url'] == '') {
            $new['image_url'] = 'https://mobile.mektep.edu.kz/uploads/images/default_background.jpg';
        }

        return response()->json($new, 200);
    }
}
