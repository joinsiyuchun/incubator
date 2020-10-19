<?php
namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Item extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
}
