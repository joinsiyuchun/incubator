<?php

namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\library\facade\Setting;
use app\admin\model\Incubatorinfo as IncubatorinfoModel;

class Index extends Admin {

    protected $checkLoginExclude = ['login'];

    public function index() {
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
//        if ($available == 0) {
//            $dailycleanpercent = 0;
//        } else {
//            $dailycleanpercent = round($dailycleanavailable / $available, 2);
//        }
//        $dailycleanreserve = IncubatorinfoModel::where($reserve_clean)->count();
//        if ($reserve == 0) {
//            $dailycleanpercentreseve = 0;
//        } else {
//            $dailycleanpercentreseve = round($dailycleanreserve / $reserve, 2);
//        }
//        $dailycleaninuse = IncubatorinfoModel::where($inuse_clean)->count();
//        if ($inuse == 0) {
//            $dailycleanpercentinuse = 0;
//        } else {
//            $dailycleanpercentinuse = round($dailycleaninuse / $inuse, 2);
//        }
//        $dailycleanwait = IncubatorinfoModel::where($wait_clean)->count();
//        if ($clean == 0) {
//            $dailycleanpercentwait = 0;
//        } else {
//            $dailycleanpercentwait = round($dailycleanwait / $clean, 2);
//        }
        if ($total == 0) {
            $dailycleanpercent=0;
            $dailycleanpercentreseve=0;
            $dailycleanpercentinuse=0;
            $dailycleanpercentwait=0;
        }else{
            $dailycleanpercent=round($available/$total,2);
            $dailycleanpercentreseve=round($reserve/$total,2);
            $dailycleanpercentinuse=round($inuse/$total,2);
            $dailycleanpercentwait=round($clean/$total,2);
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

    public function dashboard() {
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

    public function login() {
        if ($this->request->isPost()) {
            $username = $this->request->post('username/s', '', 'trim');
            $password = $this->request->post('password/s', '');
            if ($this->auth->login($username, $password)) {
                $this->jump('incubatorinfo/searchbyroom?room=*', '登录成功。');
            } else {
                $this->alert('error', $this->auth->getError());
            }
        }
        return $this->fetch();
    }

    public function logout() {
        $this->auth->logout();
        $this->jump('login', '退出成功。');
    }

    public function setting() {
        if ($this->request->isPost()) {
            $data = [
                'appid' => $this->request->post('appid/s', '', 'trim'),
                'appsecret' => $this->request->post('appsecret/s', '', 'trim'),
                'promotion' => json_encode($this->request->post('promotion/a', [], 'intval')),
                'img_swiper' => json_encode($this->request->post('img_swiper/a', [], 'trim')),
                'img_ad' => $this->request->post('img_ad/s', '', 'trim'),
                'img_category' => json_encode($this->request->post('img_category/a', [], 'trim'))
            ];
            $result = $this->validate($data, 'Setting');
            if ($result === true) {
                Setting::set($data);
                $this->alert('success', '保存成功。');
            } else {
                $this->alert('error', $result);
            }
        }
        $this->assign([
            'appid' => Setting::get('appid'),
            'appsecret' => Setting::get('appsecret'),
            'promotion' => json_decode(Setting::get('promotion'), true),
            'img_swiper' => json_decode(Setting::get('img_swiper'), true),
            'img_ad' => Setting::get('img_ad'),
            'img_category' => json_decode(Setting::get('img_category'), true)
        ]);
        return $this->fetch();
    }

    public function password() {
        $password = $this->request->get('password/s', '', 'trim');
        if ($password != "") {
            $this->auth->changePassword($password);
            $this->alert('success', '密码修改成功。');
        }
        return $this->fetch();
    }

}
