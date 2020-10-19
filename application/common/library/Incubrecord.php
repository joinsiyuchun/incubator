<?php

namespace app\common\library;

use app\common\model\Incubrecord as IncubrecordModel;
use app\admin\model\Incubatorinfo as IncubatorinfoModel;
use app\admin\model\Admin as AdminModel;


class Incubrecord {

    public function set($incubcode, $action,$operatorid) {
        $incubatorinfo = IncubatorinfoModel::where('code', $incubcode)->find();
          $operatorinfo = AdminModel::where('id', $operatorid)->find();
        $data = [
            'incubatorid' => $incubatorinfo['code'],
            'incubtype' => $incubatorinfo['type'],
            'incubbrand' => $incubatorinfo['brand'],
            'incubseries' => $incubatorinfo['seriesno'],
            'patientno' => $incubatorinfo['patientno'],
            'patientname' => $incubatorinfo['patientname'],
            'room' => $incubatorinfo['room'],
            'memo' => $incubatorinfo['memo'],
            'temperature'=> $incubatorinfo['temperature'],
            'filterflag'=> $incubatorinfo['filterflag'],
            'operationtype' => $action,
            'operatorid'=> $operatorinfo['id'],
            'opusername'=> $operatorinfo['username'],
            'operatorname'=> $operatorinfo['realname'],
            'telephone'=> $operatorinfo['mobile']
            
        ];
        if ($data != null) {
            IncubrecordModel::insert($data);
        }
    }

}
