<?php

namespace app\admin\controller;

use app\common\controller\Admin;
use app\admin\model\Incubatorinfo as IncubatorinfoModel;
use app\admin\model\Cleanplan as CleanplanModel;
use app\admin\model\Incubrecord as RecordModel;
use app\admin\model\Downloadreport as DownloadreportModel;
use app\admin\model\Reporttemplate as ReporttemplateModel;
use app\admin\model\Reportcontent as ReportcontentModel;

class Incubatorinfo extends Admin {

    public function incublog($incubcode, $action) {
        $this->log($incubcode, $action);
    }

    public function index() {
        $code = $this->request->get('code/s');
        $cleancycle = $this->request->get('cleancycle/a');
        $dailyplan = 0;
        $weeklyplan = 0;
        if (!empty($cleancycle)) {
            for ($j = 0; $j < count($cleancycle); $j++) {
                if ($cleancycle[$j] == '每日检查') {
                    $dailyplan = 1;
                } elseif ($cleancycle[$j] == '每周检查') {
                    $weeklyplan = 1;
                }
            }
        }
        if (!empty($code)) {
            $data = [
                'code' => $code,
                'room' => $this->request->get('room/s'),
                'type' => $this->request->get('type/s'),
                'status' => $this->request->get('status/s'),
                'memo' => $this->request->get('memo/s'),
                'brand' => $this->request->get('brand/s'),
                'seriesno' => $this->request->get('seriesno/s'),
                'available' => $this->request->get('available/s'),
                'dailyplan' => $dailyplan,
                'weeklyplan' => $weeklyplan,
                'registrationdt' => date("Y-m-d h:i:s")
            ];
            IncubatorinfoModel::insert($data);
            $this->incublog($code, "登记");
            $incubatorinfo = IncubatorinfoModel::where('code', $code)->find();
            $this->assign('incubatorinfo', $incubatorinfo);
            $monday = date("Y-m-d h:i:s", strtotime('this week Monday', time()));
            $this->assign('mondy', $monday);
        }
        return $this->fetch();
    }

    public function search() {
        $code = $this->request->get('code/s');
        $status = $this->request->get('status/s');
        $brand = $this->request->get('brand/s');
        $seriesno = $this->request->get('seriesno/s');
        $action = $this->request->get('action/s');
        $type = $this->request->get('type/a');
        $room = $this->request->get('room/a');
        $available = $this->request->get('available/a');
        $search = "isincub=1";
        if (!empty($type)) {
            for ($i = 0; $i < count($type); $i++) {
                if ($i == 0) {
                    $search = $search . " and type = '" . $type[$i] . "'";
                } else {
                    $search = $search . " or type = '" . $type[$i] . "'";
                }
            }
        }
        if (!empty($status)) {
            if ($status == "全部") {
                $search = $search . " and status like '%'";
            } else {
                $search = $search . " and status = '" . $status . "'";
            }
        }
        if (!empty($room)) {
            for ($j = 0; $j < count($room); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( room = '" . $room[$j] . "'";
                } else {
                    $search = $search . " or room = '" . $room[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($available)) {
            for ($j = 0; $j < count($available); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( available = '" . $available[$j] . "'";
                } else {
                    $search = $search . " or available = '" . $available[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and code like '%" . $tmp . "%'";
        }
        if (!empty($brand)) {
            $tmp = strtr($brand, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and brand like '%" . $tmp . "%'";
        }
        if (!empty($seriesno)) {
            $tmp = strtr($seriesno, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and seriesno like '%" . $tmp . "%'";
        }
        if ($action !== "add") {
            $incubatorinfo = IncubatorinfoModel::where("isincub", 1)->select();
        } else {
            $incubatorinfo = IncubatorinfoModel::where($search)->select();
        }
        //  $this->alert('info',$search);
        $this->assign('incubatorinfo', $incubatorinfo);
        return $this->fetch();
    }

    public function reserve() {

        $code = $this->request->get('code/s');
        $status = $this->request->get('status/s');
        $brand = $this->request->get('brand/s');
        $seriesno = $this->request->get('seriesno/s');
        $action = $this->request->get('action/s');
        $type = $this->request->get('type/a');
        $room = $this->request->get('room/a');
        $available = $this->request->get('available/a');
        $search = "isincub=1 and status='在用' and available='启用' ";
        if (!empty($type)) {
            for ($i = 0; $i < count($type); $i++) {
                if ($i == 0) {
                    $search = $search . " and type = '" . $type[$i] . "'";
                } else {
                    $search = $search . " or type = '" . $type[$i] . "'";
                }
            }
        }

        if (!empty($room)) {
            for ($j = 0; $j < count($room); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( room = '" . $room[$j] . "'";
                } else {
                    $search = $search . " or room = '" . $room[$j] . "'";
                }
            }
            $search = $search . " )";
        }

        if (!empty($available)) {
            for ($j = 0; $j < count($available); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( available = '" . $available[$j] . "'";
                } else {
                    $search = $search . " or available = '" . $available[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and code like '%" . $tmp . "%'";
        }
        if (!empty($brand)) {
            $tmp = strtr($brand, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and brand like '%" . $tmp . "%'";
        }
        if (!empty($seriesno)) {
            $tmp = strtr($seriesno, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and seriesno like '%" . $tmp . "%'";
        }
        if ($action !== "add") {
            $incubatorinfo = IncubatorinfoModel::where('status', '在用')->select();
        } else {
            $incubatorinfo = IncubatorinfoModel::where($search)->select();
        }
        //     $this->alert('info',$search);
        $this->assign('incubatorinfo', $incubatorinfo);
        return $this->fetch();
    }

    public function planclean() {

        $code = $this->request->get('code/s');

        $action = $this->request->get('action/s');
        $type = $this->request->get('type/a');
        $room = $this->request->get('room/a');
        $memo = $this->request->get('memo/s');
        $cycle = $this->request->get('plancycle/s');
        $search = "1=1";
        if (!empty($type)) {
            for ($i = 0; $i < count($type); $i++) {
                if ($i == 0) {
                    $search = $search . " and type = '" . $type[$i] . "'";
                } else {
                    $search = $search . " or type = '" . $type[$i] . "'";
                }
            }
        }
        if (!empty($cycle)) {
            if ($cycle == "全部") {
                $search = $search . " and plancycle like '%'";
            } else {
                $search = $search . " and plancycle = '" . $cycle . "'";
            }
        }
        if (!empty($room)) {
            for ($j = 0; $j < count($room); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( room = '" . $room[$j] . "'";
                } else {
                    $search = $search . " or room = '" . $room[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and code like '%" . $tmp . "%'";
        }
        if (!empty($memo)) {
            $tmp = strtr($memo, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and memo like '%" . $tmp . "%'";
        }



        if ($action !== "add") {
            $cleanplan = CleanplanModel::all();
        } else {
            $cleanplan = CleanplanModel::where($search)->select();
        }
        //     $this->alert('info',$search);
        $this->assign('cleanplan', $cleanplan);
        return $this->fetch();
    }

    public function controlreport() {

        $code = $this->request->get('code/s');
        $action = $this->request->get('action/s');
        $startdt = $this->request->get('startdt/s');
        $patientid = $this->request->get('patientid/s');
        $patientname = $this->request->get('patientname/s');
        $type = $this->request->get('type/a');
        $room = $this->request->get('room/a');
        $search = "(patientno <> '-' or patientname <> '-') ";
        $searchstring = $this->request->get('searchstring/s');
        if (!empty($type)) {
            for ($i = 0; $i < count($type); $i++) {
                if ($i == 0) {
                    $search = $search . " and incubtype = '" . $type[$i] . "'";
                } else {
                    $search = $search . " or incubtype = '" . $type[$i] . "'";
                }
            }
        }

        if (!empty($room)) {
            for ($j = 0; $j < count($room); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( room = '" . $room[$j] . "'";
                } else {
                    $search = $search . " or room = '" . $room[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and incubatorid like '%" . $tmp . "%'";
        }
        if (!empty($patientid)) {
            $tmp = strtr($patientid, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and patientno like '%" . $tmp . "%'";
        }
        if (!empty($patientname)) {
            $tmp = strtr($patientname, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and patientname like '%" . $tmp . "%'";
        }
        if (!empty($startdt)) {
            $dt = explode('到', $startdt);
            $search = $search . " and   operationdt between STR_TO_DATE('" . $dt[0] . " ', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('" . $dt[1] . "', '%Y-%m-%d %H:%i:%s')";
        }
        if (!empty($searchstring)) {
            $dailyrecord = RecordModel::where($searchstring)->select();
            $this->insertreport('R02', '操作追溯', $dailyrecord);
        } else {
            $dailyrecord = RecordModel::where($search)->order("incubatorid")->select();
        }
//       $this->alert('info', $search);
        $this->assign('dailyrecord', $dailyrecord);
        $this->assign('searchstring', $search);

        return $this->fetch();
    }

    public function insertreport($code, $name, $list) {
        $templatedata = [
            'code' => $code,
            'user' => 'admin',
            'reportname' => $name,
            'downloaddt' => date("Y-m-d h:i:s")
        ];
        DownloadreportModel::insert($templatedata);
        $id = DownloadreportModel::getLastInsID();
        foreach ($list as $row) {
            if ($code == 'R02') {
                $data = [
                    'reportid' => $id,
                    'var1' => $row['incubatorid'],
                    'var2' => $row['incubtype'],
                    'var3' => $row['room'],
                    'var4' => $row['operationtype'],
                    'var5' => $row['operationdt'],
                    'var6' => $row['patientno'],
                    'var7' => $row['patientname']
                ];
            }
            if ($code == 'R01') {
                $data = [
                    'reportid' => $id,
                    'var1' => $row["incubcount"],
                    'var2' => $row["patientcount"],
                    'var3' => $row["plancount"],
                    'var4' => $row["cleancount"],
                    'var5' => $row["todaycleancount"],
                    'var6' => $row["gapcount"],
                    'var7' => $row["yesgapcount"]
                ];
            }
            
            if ($data != null) {
                ReportcontentModel::insert($data);
            }
        }
    }

    public function controlpath() {

        $code = $this->request->get('code/s');
        $action = $this->request->get('action/s');
        $startdt = $this->request->get('startdt/s');
        $patientid = $this->request->get('patientid/s');
        $patientname = $this->request->get('patientname/s');
        $type = $this->request->get('type/a');
        $room = $this->request->get('room/a');
        $search = "(patientno <> '-') ";
        if (!empty($type)) {
            for ($i = 0; $i < count($type); $i++) {
                if ($i == 0) {
                    $search = $search . " and incubtype = '" . $type[$i] . "'";
                } else {
                    $search = $search . " or incubtype = '" . $type[$i] . "'";
                }
            }
        }

        if (!empty($room)) {
            for ($j = 0; $j < count($room); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( room = '" . $room[$j] . "'";
                } else {
                    $search = $search . " or room = '" . $room[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and incubatorid like '%" . $tmp . "%'";
        }
        if (!empty($patientid)) {
            $tmp = strtr($patientid, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and patientno like '%" . $tmp . "%'";
        }
        if (!empty($patientname)) {
            $tmp = strtr($patientname, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and patientname like '%" . $tmp . "%'";
        }
        if (!empty($startdt)) {
            $dt = explode('到', $startdt);
            $search = $search . " and   operationdt between STR_TO_DATE('" . $dt[0] . " ', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('" . $dt[1] . "', '%Y-%m-%d %H:%i:%s')";
        }
        $dailyrecord = RecordModel::where($search)->select();
//       $this->alert('info', $search);
        $this->assign('dailyrecord', $dailyrecord);
        $this->assign('searchperiod', $startdt);
        return $this->fetch();
    }

    public function createplan() {

        $code = $this->request->get('code/s');
        $memo = $this->request->get('memo/s');
        $cycle = $this->request->get('cycle/s');
        $type = $this->request->get('type/s');
        $room = $this->request->get('room/s');
        $startdt = $this->request->get('startdt/s');
        $period = explode('到', $startdt);
        if (!empty($code)) {
            $data = [
                'code' => $code,
                'memo' => $memo,
                'startdt' => $period[0],
                'enddt' => $period[1],
                'plancycle' => $cycle,
                'type' => $type,
                'room' => $room
            ];
            CleanplanModel::insert($data);

            if ($cycle == "每天检查") {
                $incubdata = [
                    'dailyplan' => 1
                ];
            } elseif ($cycle == "每周检查") {
                $incubdata = [
                    'weeklyplan' => 1
                ];
            }
            $filterdata = [
                'type' => $type,
                'room' => $room
            ];
            IncubatorinfoModel::where($filterdata)->update($incubdata);
            return $this->redirect(url('admin/incubatorinfo/planclean'));
        }
        return $this->fetch();
    }

    public function updateincub() {
        $action = $this->request->get('action/s');
        if ($action === "add") {
            $status = $this->request->get('status/s');
            switch ($status) {
                case '备机':
                    $data = [
                        'id' => $this->request->get('id/d'),
                        'code' => $this->request->get('code/s'),
                        'room' => $this->request->get('room/s'),
                        'type' => $this->request->get('type/s'),
                        'status' => $status,
                        'reservedt' => date("Y-m-d h:i:s"),
                        'memo' => $this->request->get('memo/s'),
                        'brand' => $this->request->get('brand/s'),
                        'seriesno' => $this->request->get('seriesno/s'),
                        'available' => $this->request->get('available/s')
                    ];
                    break;
                case '报修待接修':
                    $data = [
                        'id' => $this->request->get('id/d'),
                        'code' => $this->request->get('code/s'),
                        'room' => $this->request->get('room/s'),
                        'type' => $this->request->get('type/s'),
                        'status' => $status,
                        'checkindt' => date("Y-m-d h:i:s"),
                        'memo' => $this->request->get('memo/s'),
                        'brand' => $this->request->get('brand/s'),
                        'seriesno' => $this->request->get('seriesno/s'),
                        'available' => $this->request->get('available/s')
                    ];
                    break;
                case '维修中':
                    $data = [
                        'id' => $this->request->get('id/d'),
                        'code' => $this->request->get('code/s'),
                        'room' => $this->request->get('room/s'),
                        'type' => $this->request->get('type/s'),
                        'status' => $status,
                        'checkoutdt' => date("Y-m-d h:i:s"),
                        'memo' => $this->request->get('memo/s'),
                        'brand' => $this->request->get('brand/s'),
                        'seriesno' => $this->request->get('seriesno/s'),
                        'available' => $this->request->get('available/s')
                    ];
                    break;
                case '在用':
                    $data = [
                        'id' => $this->request->get('id/d'),
                        'code' => $this->request->get('code/s'),
                        'room' => $this->request->get('room/s'),
                        'type' => $this->request->get('type/s'),
                        'status' => $status,
                        'cleandt' => date("Y-m-d h:i:s"),
                        'memo' => $this->request->get('memo/s'),
                        'brand' => $this->request->get('brand/s'),
                        'seriesno' => $this->request->get('seriesno/s'),
                        'available' => $this->request->get('available/s')
                    ];
            }
            IncubatorinfoModel::update($data);
            return $this->redirect(url('admin/incubatorinfo/search'));
        }

        $id = $this->request->get('id/d');
        $incubinfo = IncubatorinfoModel::where('id', $id)->find();
        //   $this->alert('info',$incubinfo);

        $this->assign('incubinfo', $incubinfo);
        return $this->fetch();
    }

    public function updateplan() {
        $action = $this->request->get('action/s');
        if ($action === "add") {
            $data = [
                'id' => $this->request->get('id/d'),
                'code' => $this->request->get('code/s'),
                'room' => $this->request->get('room/s'),
                'type' => $this->request->get('type/s'),
                'status' => $this->request->get('status/s'),
                'memo' => $this->request->get('memo/s'),
                'startdt' => $this->request->get('startdt/s'),
                'enddt' => $this->request->get('enddt/s'),
                'plancycle' => $this->request->get('plancycle/s')
            ];
            CleanplanModel::update($data);
            $this->alert('success', '计划信息修改成功。');
        }

        $id = $this->request->get('id/d');
        $planinfo = CleanplanModel::where('id', $id)->find();
        //     $this->alert('info',$planinfo);

        $this->assign('planinfo', $planinfo);
        return $this->fetch();
    }

    public function reserveincubator() {
        $action = $this->request->get('action/s');
        $id = $this->request->get('id/d');
        $code = $this->request->get('code/s');
        if ($action == "add") {
            $data = [
                'id' => $id,
                'patientno' => $this->request->get('patientno/s'),
                'patientname' => $this->request->get('patientname/s'),
                'contact' => $this->request->get('contact/s'),
                'status' => '备机',
                'memo' => $this->request->get('memo/s'),
                'reservedt' => $this->request->get('reservedt/s'),
                'contactinfo' => $this->request->get('contactinfo/s')
            ];

            IncubatorinfoModel::update($data);
            $this->alert('success', '备机成功。');
            $this->incublog($code, "备机");
            return $this->redirect(url('admin/Index/index', ['room' => '*']));
        }
        $incubinfo = IncubatorinfoModel::where('id', $id)->find();
        //    $this->alert('info',$incubinfo);
        $this->assign('incubinfo', $incubinfo);

        return $this->fetch();
    }

    public function cleanincubator() {
        $action = $this->request->get('action/s');
        $id = $this->request->get('id/d');
        if ($action === "add") {
            $data = [
                'id' => $id,
                'contact' => $this->request->get('contact/s'),
                'status' => '维修中',
                'memo' => $this->request->get('memo/s'),
                'plancleandt' => $this->request->get('reservedt/s'),
                'contactinfo' => $this->request->get('contactinfo/s')
            ];
            IncubatorinfoModel::update($data);
            $this->alert('success', '检查成功。');
            $this->incublog($code, "检查");
            return $this->redirect(url('admin/Index/index'));
        }
        $incubinfo = IncubatorinfoModel::where('id', $id)->find();
        //      $this->alert('info',$incubinfo);
        $this->assign('incubinfo', $incubinfo);

        return $this->fetch();
    }

    public function userecord() {

        $code = $this->request->get('code/s');
        $status = $this->request->get('status/s');
        $brand = $this->request->get('brand/s');
        $seriesno = $this->request->get('seriesno/s');
        $action = $this->request->get('action/s');
        $type = $this->request->get('type/a');
        $room = $this->request->get('room/a');
        $available = $this->request->get('available/a');
        $search = "isincub=1 ";
        if (!empty($type)) {
            for ($i = 0; $i < count($type); $i++) {
                if ($i == 0) {
                    $search = $search . " and type = '" . $type[$i] . "'";
                } else {
                    $search = $search . " or type = '" . $type[$i] . "'";
                }
            }
        }
        if (!empty($status)) {
            if ($status == "全部") {
                $search = $search . " and status like '%'";
            } else {
                $search = $search . " and status = '" . $status . "'";
            }
        }
        if (!empty($room)) {
            for ($j = 0; $j < count($room); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( room = '" . $room[$j] . "'";
                } else {
                    $search = $search . " or room = '" . $room[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($available)) {
            for ($j = 0; $j < count($available); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( available = '" . $available[$j] . "'";
                } else {
                    $search = $search . " or available = '" . $available[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and code like '%" . $tmp . "%'";
        }
        if (!empty($brand)) {
            $tmp = strtr($brand, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and brand like '%" . $tmp . "%'";
        }
        if (!empty($seriesno)) {
            $tmp = strtr($seriesno, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and seriesno like '%" . $tmp . "%'";
        }
        if ($action !== "add") {
            $incubatorinfo = IncubatorinfoModel::where("isincub", '1')->select();
        } else {
            $incubatorinfo = IncubatorinfoModel::where($search)->select();
        }
        //      $this->alert('info',$search);
        $this->assign('incubatorinfo', $incubatorinfo);
        return $this->fetch();
    }

    public function enterincubator() {
        $action = $this->request->get('action/s');
        $id = $this->request->get('id/d');
        if ($action === "add") {
            $data = [
                'id' => $id,
                'patientno' => $this->request->get('patientno/s'),
                'patientname' => $this->request->get('patientname/s'),
                'contact' => $this->request->get('contact/s'),
                'status' => '报修待接修',
                'memo' => $this->request->get('memo/s'),
                'reservedt' => date("Y-m-d h:i:s"),
                'contactinfo' => $this->request->get('contactinfo/s'),
                'expiredt' => null,
                'cleandt' => null,
                'checkindt' => date("Y-m-d h:i:s")
            ];
            IncubatorinfoModel::update($data);
            $this->alert('success', '入箱成功。');
            $code = $this->request->get('code/s');
            $this->incublog($code, "入箱");
            return $this->redirect(url('admin/Index/index', ['room' => '*']));
        }
        $incubinfo = IncubatorinfoModel::where('id', $id)->find();
        $this->assign('incubinfo', $incubinfo);
        return $this->fetch();
    }

    public function leaveincubator() {
        $action = $this->request->get('action/s');
        $id = $this->request->get('id/d');
        if ($action === "add") {
            $data = [
                'id' => $this->request->get('id/d'),
                'patientno' => '',
                'patientname' => '',
                'contact' => '',
                'status' => '维修中',
                'memo' => '',
                'contactinfo' => ''
            ];

            $this->alert('success', '离箱成功。');
            $code = $this->request->get('code/s');
            $this->incublog($code, "离箱");
            IncubatorinfoModel::update($data);
            return $this->redirect(url('admin/Index/index', ['room' => '*']));
        }
        $incubinfo = IncubatorinfoModel::where('id', $id)->find();
        $this->assign('incubinfo', $incubinfo);
        return $this->fetch();
    }

    public function confirmclean() {
        $action = $this->request->get('action/s');
        $cleantype = $this->request->get('cleantype/s');
        $filterflag = $this->request->get('filterflag/s');
        $temperature = $this->request->get('temperature/f');
        $id = $this->request->get('id/d');
        if ($action === "add") {
            if ($cleantype === "每日检查") {
                $data = [
                    'id' => $id,
                    'dailycleandt' => date("Y-m-d h:i:s"),
                    'filterflag' => $filterflag,
                    'temperature' => $temperature
                ];
            } elseif ($cleantype === "每周检查") {
                $data = [
                    'id' => $id,
                    'weeklycleandt' => date("Y-m-d h:i:s"),
                    'filterflag' => $filterflag,
                    'temperature' => $temperature
                ];
            } else {
                $d = strtotime("+14 Days");
                $expiredt = date("Y-m-d h:i:s", $d);
                $data = [
                    'id' => $id,
                    'status' => '在用',
                    'cleandt' => date("Y-m-d h:i:s"),
                    'filterflag' => $filterflag,
                    'expiredt' => $expiredt,
                    'temperature' => $temperature
                ];
            }

            $code = $this->request->get('code/s');
            $this->incublog($code, $cleantype);
            IncubatorinfoModel::update($data);
            $this->alert('success', '检查成功');
            return $this->redirect(url('admin/Index/index', ['room' => '*']));
        }
        $incubinfo = IncubatorinfoModel::where('id', $id)->find();
        $monday = date("Y-m-d h:i:s", strtotime('this week Monday', time()));
        $this->assign('mondy', $monday);
        $this->assign('cleantype', $cleantype);
        $this->assign('incubinfo', $incubinfo);
        $this->assign('cleantype', $cleantype);
        return $this->fetch();
    }

    public function searchbyroom() {
        $room = $this->request->get('room/s');

        $data = ['available' => '启用', 'isincub' => 1];
        if ($room == '*') {
            $data1 = ['status' => "在用", 'available' => '启用', 'isincub' => 1];
            $data2 = ['status' => "报修待接修", 'available' => '启用', 'isincub' => 1];
            $data3 = ['status' => "备机", 'available' => '启用', 'isincub' => 1];
            $data4 = ['status' => "维修中", 'available' => '启用', 'isincub' => 1];
            $data5 = ['available' => '启用', 'isincub' => 1];
            $room = '*';
            $roomname = '全部病区';
            $available_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='在用'";
            $reserve_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='备机'";
            $inuse_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='报修待接修'";
            $wait_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='维修中'";
        } else {
            $data1 = ['status' => "在用", 'available' => '启用', 'room' => $room, 'isincub' => 1];
            $data2 = ['status' => "报修待接修", 'available' => '启用', 'room' => $room, 'isincub' => 1];
            $data3 = ['status' => "备机", 'available' => '启用', 'room' => $room, 'isincub' => 1];
            $data4 = ['status' => "维修中", 'available' => '启用', 'room' => $room, 'isincub' => 1];
            $data5 = ['available' => '启用', 'room' => $room, 'isincub' => 1];
            $available_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='在用' and room='" . $room . "'";
            $reserve_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='备机' and room='" . $room . "'";
            $inuse_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='报修待接修' and room='" . $room . "'";
            $wait_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and status='维修中' and room='" . $room . "'";
            $roomname = $room;
        }

        $incubavailable = IncubatorinfoModel::where($data1)->select();
        $incubinuse = IncubatorinfoModel::where($data2)->select();
        $incubreserv = IncubatorinfoModel::where($data3)->select();
        $incubclean = IncubatorinfoModel::where($data4)->select();
        $incubtotal = IncubatorinfoModel::where($data5)->select();
        $available = IncubatorinfoModel::where($data1)->count();
        $inuse = IncubatorinfoModel::where($data2)->count();
        $reserve = IncubatorinfoModel::where($data3)->count();
        $clean = IncubatorinfoModel::where($data4)->count();
        $total = IncubatorinfoModel::where($data5)->count();
        $location = IncubatorinfoModel::where($data)->field(array("count(id)" => "countid", "room"))->group('room')->select();
        $dailycleanavailable = IncubatorinfoModel::where($available_clean)->count();
        if ($available == 0) {
            $dailycleanpercent = 0;
        } else {
            $dailycleanpercent = round($dailycleanavailable / $available, 2);
        }
        $dailycleanreserve = IncubatorinfoModel::where($reserve_clean)->count();
        if ($reserve == 0) {
            $dailycleanpercentreseve = 0;
        } else {
            $dailycleanpercentreseve = round($dailycleanreserve / $reserve, 2);
        }
        $dailycleaninuse = IncubatorinfoModel::where($inuse_clean)->count();
        if ($inuse == 0) {
            $dailycleanpercentinuse = 0;
        } else {
            $dailycleanpercentinuse = round($dailycleaninuse / $inuse, 2);
        }
        $dailycleanwait = IncubatorinfoModel::where($wait_clean)->count();
        if ($clean == 0) {
            $dailycleanpercentwait = 0;
        } else {
            $dailycleanpercentwait = round($dailycleanwait / $clean, 2);
        }


        $this->assign('total_inuse', $inuse);
        $this->assign('total_reserve', $reserve);
        $this->assign('total_available', $available);
        $this->assign('total_inuse', $inuse);
        $this->assign('total_reserve', $reserve);
        $this->assign('total_waitingclean', $clean);
        $this->assign('incubtotal', $incubtotal);
        $this->assign('incubavailable', $incubavailable);
        $this->assign('incubinuse', $incubinuse);
        $this->assign('incubreserv', $incubreserv);
        $this->assign('incubclean', $incubclean);
        $this->assign('total', $total);
        $this->assign('location', $location);
        $this->assign('room', $room);
        $this->assign('roomname', $roomname);
        $monday = date("Y-m-d h:i:s", strtotime('this week Monday', time()));
        $this->assign('mondy', $monday);
        $this->assign('dailycleanpercent', $dailycleanpercent);
        $this->assign('dailycleanpercentreseve', $dailycleanpercentreseve);
        $this->assign('dailycleanpercentinuse', $dailycleanpercentinuse);
        $this->assign('dailycleanpercentwait', $dailycleanpercentwait);
        return $this->fetch();
    }

    public function patientpath() {

        $id = $this->request->get('id/s');
        $room = RecordModel::where('patientno', $id)->field("room")->select();
        $searchperiod = $this->request->get('searchperiod/s');
        $dt = explode('到', $searchperiod);
        if ($searchperiod == null) {
            $search = "1=1";
        } else {
            $search = "operationdt between STR_TO_DATE('" . $dt[0] . " ', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('" . $dt[1] . "', '%Y-%m-%d %H:%i:%s')";
        }
//        $room="1病房";
        //      $this->alert('info',$incubinfo);

        if ($room == null) {
            $path = null;
        } else {
            $str = '';
            foreach ($room as $key => $value) {
                $room_id = $value['room'];
                //进行字符拼接
                $str .= ",'" . $room_id . "'";
            }
            //拼接后的结果前面会有一个逗号，我们处理掉。
            $str = substr($str, 1);
            $search = $search . " and room in (" . $str . ")";
            $path = RecordModel::where($search)->order('operationdt', 'desc')->select();
        }

        $this->assign('path', $path);
        return $this->fetch();
    }

    public function operatorpath() {

        $id = $this->request->get('id/d');
        $room = RecordModel::where('operatorid', $id)->field("room")->select();
        $searchperiod = $this->request->get('searchperiod/s');
        $dt = explode('到', $searchperiod);
        if ($searchperiod == null) {
            $search = "1=1";
        } else {
            $search = "operationdt between STR_TO_DATE('" . $dt[0] . " ', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('" . $dt[1] . "', '%Y-%m-%d %H:%i:%s')";
        }
//        $room="1病房";
        //      $this->alert('info',$incubinfo);

        if ($room == null) {
            $path = null;
        } else {
            $str = '';
            foreach ($room as $key => $value) {
                $room_id = $value['room'];
                //进行字符拼接
                $str .= ",'" . $room_id . "'";
            }
            //拼接后的结果前面会有一个逗号，我们处理掉。
            $str = substr($str, 1);
            $search = $search . " and room in (" . $str . ")";
            $path = RecordModel::where($search)->order('operationdt', 'desc')->select();
        }

        $this->assign('path', $path);
        return $this->fetch();
    }

    public function reportcenter() {
        $export = $this->request->get('export/s');
        $incubcount = IncubatorinfoModel::count();
        $search = "operationtype='入箱' and PERIOD_DIFF( date_format( now( ) , '%Y%m' ) , date_format( operationdt, '%Y%m' ) ) =0 ";
        $patientcount = RecordModel::where($search)->count();
        $search2 = "operationtype='离箱' and PERIOD_DIFF( date_format( now( ) , '%Y%m' ) , date_format( operationdt, '%Y%m' ) ) =0 ";
        $patientcount = RecordModel::where($search)->count();
        $leavecount = RecordModel::where($search2)->count();
        $plancount = $incubcount * (30 + 4) + $leavecount;
        $search3 = "operationtype like '%检查%' and PERIOD_DIFF( date_format( now( ) , '%Y%m' ) , date_format( operationdt, '%Y%m' ) ) =0 ";
        $cleancount = RecordModel::where($search3)->count();
        $search4 = "operationtype like '%检查%' and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =0 ";
        $todaycleancount = RecordModel::where($search4)->count();
        $search5 = "operationtype='离箱' and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =0 ";
        $todayleavecount = RecordModel::where($search5)->count();
        $gapcount = $incubcount + $leavecount - $todaycleancount;
        $search6 = "operationtype='离箱' and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =1 ";
        $yesterdayleavecount = RecordModel::where($search6)->count();
        $search7 = "operationtype like '%检查%' and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =1 ";
        $yesterdaycleancount = RecordModel::where($search7)->count();
        $yesgapcount = $incubcount + $yesterdayleavecount - $yesterdaycleancount;
        //本月完成率
        $search8 = "operationtype like '%检查%' and PERIOD_DIFF( date_format( now( ) , '%Y%m' ) , date_format( operationdt, '%Y%m' ) ) =0 ";
        $curmonthcleancount = RecordModel::where($search8)->count();
        $search9 = "operationtype='离箱' and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =0 ";
        $curmonthleavecount = RecordModel::where($search9)->count();
        $curmonthpercent = round($curmonthcleancount / ($incubcount * (30 + 4) + $curmonthleavecount) * 100, 2);
        //上月完成率
        $search10 = "operationtype like '%检查%' and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =1 ";
        $lastmonthcleancount = RecordModel::where($search10)->count();
        $search11 = "operationtype='离箱' and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =1 ";
        $lastmonthleavecount = RecordModel::where($search11)->count();
        $lastmonthpercent = round($lastmonthcleancount / ($incubcount * (30 + 4) + $lastmonthleavecount) * 100, 2);
        //本年完成率
        $search12 = "operationtype like '%检查%' and year(now())=year(operationdt)";
        $yearcleancount = RecordModel::where($search12)->count();
        $search13 = "operationtype='离箱' and year(now())=year(operationdt)";
        $yearleavecount = RecordModel::where($search13)->count();
        $yearpercent = round($yearcleancount / ($incubcount * (30 + 4) + $yearleavecount) * 100, 2);
        //本月医护工作量统计
        $search14 = "operationtype like '%检查%' and PERIOD_DIFF( date_format( now( ) , '%Y%m' ) , date_format( operationdt, '%Y%m' ) ) =0 ";
        $nursecleancount = RecordModel::where($search14)->field(array("count(id)" => "countid", "operatorname"))->group('operatorname')->select();
        //本月遗漏检查
        $query = new \think\db\Query();
        $str1 = "select a.code,a.type,ifnull(gap,day(now())) total  from (select * from wxshop_incubatorinfo where isincub=1)a left join (";
        $str1 = $str1 . " SELECT incubatorid,date_format( operationdt, '%Y-%m' ) dt,day(now())-count(1) gap FROM wxshop_incubrecord ";
        $str1 = $str1 . " where operationtype = '每日检查'";
        $str1 = $str1 . " and PERIOD_DIFF( date_format( now( ) , '%Y%m%d' ) , date_format( operationdt, '%Y%m%d' ) ) =0";
        $str1 = $str1 . " group by incubatorid,date_format( operationdt, '%Y-%m' ))b";
        $str1 = $str1 . " on a.code=b.incubatorid and b.gap>0";
        $missclean = $query->query($str1);
        $this->assign('incubcount', $incubcount);
        $this->assign('patientcount', $patientcount);
        $this->assign('plancount', $plancount);
        $this->assign('cleancount', $cleancount);
        $this->assign('gapcount', $gapcount);
        $this->assign('todaycleancount', $todaycleancount);
        $this->assign('yesgapcount', $yesgapcount);
        $this->assign('curmonthpercent', $curmonthpercent);
        $this->assign('lastmonthpercent', $lastmonthpercent);
        $this->assign('yearpercent', $yearpercent);
        $this->assign('nursecleancount', $nursecleancount);
        $this->assign('missclean', $missclean);
        if(!empty($export)){
            $data=[["incubcount"=>$incubcount,
                "patientcount"=>$patientcount,
                "plancount"=>$plancount,
                "cleancount"=>$cleancount,
                "todaycleancount"=>$todaycleancount,
                "gapcount"=>$gapcount,
                "yesgapcount"=>$yesgapcount]];
             $this->insertreport('R01', '数据概览', $data);
         }
        return $this->fetch();
    }

    public function reportcenterapi() {
        $data = array("1", "2");
        $data1 = array(1, 2);
        $data2 = array(2, 1);
        $str1 = "select date_key,count(1) num,count(1)*34 total from(";
        $str1 = $str1 . " SELECT * FROM wxshop_dim_dt a ";
        $str1 = $str1 . " left join (select date_format(registrationdt,'%y-%m-%d') dt";
        $str1 = $str1 . " from wxshop_incubatorinfo where isincub=1) b";
        $str1 = $str1 . " on a.full_date>=b.dt) detail";
        $str1 = $str1 . " group by detail.date_key";
        $query = new \think\db\Query();
        $countclean = $query->query($str1);
        if (!empty($countclean)) {
            for ($j = 0; $j < count($countclean); $j++) {
                $data[$j] = $countclean[$j]["date_key"];
                $data1[$j] = $countclean[$j]["num"];
                $data2[$j] = $countclean[$j]["total"];
            }
        }
        return json([
            'list' => $data,
            'plancount' => $data2,
            'cleancount' => $data1
        ]);
    }

    public function searchbycode() {
        $code = $this->request->get('code/s');
        $room = $this->request->get('room/s');
        $status = $this->request->get('status/s');
        $search = "isincub=1 ";


//
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and code like '%" . $tmp . "%'";
        }else{
            if($room!='全部病区'){
                $search = $search . " and room = '" . $room . "'";
            }
            $search = $search . " and status = '" . $status . "'";
        }
        $incubatorinfo = IncubatorinfoModel::where($search)->select();
        $this->assign('incubatorinfo', $incubatorinfo);
//        $this->alert('info', $search);
        return $this->fetch();
    }

    public function warningmessage() {
        $code = $this->request->get('code/s');

        $search = "isincub=1 ";
//
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and code like '%" . $tmp . "%'";
        }
        $incubatorinfo = IncubatorinfoModel::where($search)->select();
        $this->assign('incubatorinfo', $incubatorinfo);
//        $this->alert('info', $search);
        return $this->fetch();
    }

    public function downloadreport() {

        $code = $this->request->get('code/s');
        $downloaduser = $this->request->get('downloaduser/s');
        $action = $this->request->get('action/s');
        $startdt = $this->request->get('startdt/s');
        $report = $this->request->get('report/a');
        $search = "1=1 ";


        if (!empty($report)) {
            for ($j = 0; $j < count($report); $j++) {
                if ($j == 0) {
                    $search = $search . " and ( reportname = '" . $report[$j] . "'";
                } else {
                    $search = $search . " or reportname = '" . $report[$j] . "'";
                }
            }
            $search = $search . " )";
        }
        if (!empty($code)) {
            $tmp = strtr($code, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and code like '%" . $tmp . "%'";
        }
        if (!empty($downloaduser)) {
            $tmp = strtr($downloaduser, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search = $search . " and user like '%" . $tmp . "%'";
        }

        if (!empty($startdt)) {
            $dt = explode('到', $startdt);
            $search = $search . " and   downloaddt between STR_TO_DATE('" . $dt[0] . " ', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('" . $dt[1] . "', '%Y-%m-%d %H:%i:%s')";
        }
        $reportlist = DownloadreportModel::where($search)->select();
//       $this->alert('info', $search);
        $this->assign('reportlist', $reportlist);
        return $this->fetch();
    }

    public function downloadcsv() {

        $code = $this->request->get('code/s');
        $id = $this->request->get('id/d');
        $list = ReportcontentModel::where('reportid', $id)->select();
        $csv_title = ReporttemplateModel::where('reportcode', $code)->field("column")->select();
        $this->put_csv($list, $csv_title);
    }

    public function put_csv($list, $title) {
        $file_name = "CSV" . date("mdHis", time()) . ".csv";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name);
        header('Cache-Control: max-age=0');
        $file = fopen('php://output', "a");
        $limit = 1000;
        $calc = 0;
        if (!empty($title)) {
            foreach ($title as $v) {
                $tit[] = iconv('UTF-8', 'GB2312//IGNORE', $v["column"]);
            }
            fputcsv($file, $tit);
            if (!empty($list)) {
                foreach ($list as $row) {
                    $row1 = iconv('UTF-8', 'GB2312//IGNORE', $row['var1']);
                    $row2 = iconv('UTF-8', 'GB2312//IGNORE', $row['var2']);
                    $row3 = iconv('UTF-8', 'GB2312//IGNORE', $row['var3']);
                    $row4 = iconv('UTF-8', 'GB2312//IGNORE', $row['var4']);
                    $row5 = iconv('UTF-8', 'GB2312//IGNORE', $row['var5']);
                    $row6 = iconv('UTF-8', 'GB2312//IGNORE', $row['var6']);
                    $row7 = iconv('UTF-8', 'GB2312//IGNORE', $row['var7']);
                    $row8 = iconv('UTF-8', 'GB2312//IGNORE', $row['var8']);
                    $row9 = iconv('UTF-8', 'GB2312//IGNORE', $row['var9']);
                    $row10 = iconv('UTF-8', 'GB2312//IGNORE', $row['var10']);
                    $row11 = iconv('UTF-8', 'GB2312//IGNORE', $row['var11']);
                    $row12 = iconv('UTF-8', 'GB2312//IGNORE', $row['var12']);
                    $row13 = iconv('UTF-8', 'GB2312//IGNORE', $row['var13']);
                    $row14 = iconv('UTF-8', 'GB2312//IGNORE', $row['var14']);
                    $row15 = iconv('UTF-8', 'GB2312//IGNORE', $row['var15']);
                    $row16 = iconv('UTF-8', 'GB2312//IGNORE', $row['var16']);
                    $row17 = iconv('UTF-8', 'GB2312//IGNORE', $row['var17']);
                    $row18 = iconv('UTF-8', 'GB2312//IGNORE', $row['var18']);
                    $row19 = iconv('UTF-8', 'GB2312//IGNORE', $row['var19']);
                    $row20 = iconv('UTF-8', 'GB2312//IGNORE', $row['var20']);
                    $line = [$row1, $row2, $row3, $row4,$row5, $row6, $row7, $row8,
                        $row9, $row10, $row11, $row12,$row13, $row14, $row15, $row16,
                        $row17, $row18, $row19, $row20];
                    fputcsv($file, $line);
                }
            }
            fclose($file);
            exit();
        }
    }

}
