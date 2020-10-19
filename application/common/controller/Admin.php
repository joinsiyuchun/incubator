<?php

namespace app\common\controller;

use app\admin\library\Auth;
use think\Controller;
use think\facade\Validate;
use think\facade\Url;
use app\common\library\facade\Incubrecord;
use app\admin\model\Incubatorinfo as IncubatorinfoModel;

class Admin extends Controller {

    protected $auth;
    protected $checkLoginExclude = [];
    protected $uploadPath = './static/uploads/';

    protected function initialize() {

        $this->auth = Auth::getInstance();
        $controller = $this->request->controller();
        $action = $this->request->action();

        $this->assign('_path', $controller . '/' . $action);

        if (!in_array($action, $this->checkLoginExclude)) {
            if (!$this->auth->isLogin()) {
                $this->error('您还没有登录。', 'Index/login');
            }
            $this->assign('_admin', $this->auth->getLoginUser());
        }

        if ($this->request->isPost()) {
            if (!Validate::token(null, null, ['__token__' => $this->request->post('__token__/s')])) {
                $this->error('表单已过期，请重新提交。', '');
            }
        }
//        warning message
        $already_clean = "to_days(dailycleandt)=to_days(now()) and isincub=1 and dailyplan=1";
        $planed_clean = ['available' => '启用', 'isincub' => 1, 'dailyplan' => 1];
        $already_clean_count = IncubatorinfoModel::where($already_clean)->count();
        $planed_clean_count = IncubatorinfoModel::where($planed_clean)->count();
        $gap_clean_count = $planed_clean_count - $already_clean_count;
        $this->assign('already_clean_count', $already_clean_count);
        $this->assign('planed_clean_count', $planed_clean_count);
        $this->assign('gap_clean_count', $gap_clean_count);
    }

    protected function alert($type, $msg = '') {
        if ($this->request->isAjax()) {
            if ($type === 'info') {
                $this->success($msg);
            }
            $this->$type($msg);
        }
        $this->assign($type, $msg);
    }

    protected function jump($url = null, $msg = '') {
        if ($this->request->isAjax()) {
            $this->success($msg);
        }
        $this->redirect($url);
    }

    protected function log($incubcode, $action) {
        $operator = $this->auth->getLoginUser();
        Incubrecord::set($incubcode, $action, $operator['id']);
    }



}
