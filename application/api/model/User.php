<?php
namespace app\api\model;

use think\Model;

class User extends Model
{
    public function userCompany()
    {
        return $this->hasMany('UserCompany');
    }
}
