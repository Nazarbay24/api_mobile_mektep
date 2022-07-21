<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;


class Teacher extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'mektep_teacher';


    public function mektep() {
        return $this->hasOne(Mektep::class, 'id_mektep', 'id');
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function generateAuthToken($many = false)
    {
        if($many) {
            $customClaims = [
                'iin' => $this->iin,
                'isChoiceToken' => 'true'
            ];
            return JWTAuth::claims($customClaims)->fromUser($this);
        }
        else {
            $customClaims = [
                'iin' => $this->iin,
                'id' => $this->id,
                'id_mektep' => $this->id_mektep,
            ];
            return JWTAuth::claims($customClaims)->fromUser($this);
        }
    }
}
