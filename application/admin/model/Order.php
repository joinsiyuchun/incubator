<?php
namespace app\admin\model;

use think\Model;

class Order extends Model
{
    public function orderItem()
    {
        return $this->hasMany('OrderItem');
    }

    public function orderIncub()
    {
        return $this->hasMany('OrderIncub');
    }
}
