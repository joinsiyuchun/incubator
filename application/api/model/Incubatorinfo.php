<?php
namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Incubatorinfo extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    public function incubQcorder()
    {
        return $this->hasMany("IncubQcorder","incub_id","id");
    }
    public function incubLoanorder()
    {
        return $this->hasMany("IncubLoanorder","incub_id","id");
    }
}

