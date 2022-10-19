<?php

namespace App\Repositories;

use App\Models\News;

class NewsRepository
{
    protected $model;
    protected $lang;

    public function __construct(News $model)
    {
        $this->model = $model;

        if (app()->getLocale() == 'ru') $this->lang = 'rus';
        else if (app()->getLocale() == 'kk') $this->lang = 'kaz';
    }


    public function newsList()
    {
        $news = $this->model
            ->select('id', 'title', 'datetime', 'filename as image_url')
            ->where('lang', '=', $this->lang)
            ->orderBy('datetime','desc')
            ->get()->take(20);

        return $news;
    }

    public function getNewById($id_new)
    {
        $item = $this->model
            ->select('date', 'title', 'text', 'filename as image_url')
            ->where('id', '=', $id_new)
            ->first();

        if ($item) {
            $this->model->where('id', '=', $id_new)->increment('views');
        }
        return $item;
    }
}
