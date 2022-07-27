<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table;

    public function __construct()
    {
        $this->table = 'mektep_displan_'.auth()->user()->id_mektep.'_2021';
    }
}
