<?php
namespace app\api\model;

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
    public function orderFood()
    {
        return $this->hasMany('OrderFood');
    }
}
