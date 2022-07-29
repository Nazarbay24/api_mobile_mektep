<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Journal extends BaseModel
{
    use HasFactory;

    public $table_name = "mektep_jurnal_?_?";
}
