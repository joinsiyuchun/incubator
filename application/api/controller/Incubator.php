<?php
namespace app\api\controller;
use app\admin\library\Auth;
use app\common\controller\Api;
use app\api\model\Incubatorinfo as IncubatorinfoModel;
use app\api\model\IncubQcorder as IncubQcorderModel;
use app\api\model\IncubLoanorder as IncubLoanorderModel;

class Incubator extends Api
{
//    protected $checkLoginExclude = ['find'];
    public function findbyall()
    {
        $data=IncubatorinfoModel::all();
        return json([
            'list' => $data
        ]);
    }

    public function findbyparam()
    {
        $sn = $this->request->get('sn/s', 0);
        $type=$this->request->get('type/d', 0);
        $department=$this->request->get('department/d', 0);
        $prod=$this->request->get('product/d', 0);
        switch($type){
            case 1:
                $status='在用';
                break;
            case 2:
                $status='备机';
                break;
            case 3:
                $status='报修待接修';
                break;
            case 4:
                $status='维修中';
                break;
            default:
                $status=-1;
        }

        switch($department){
            case 1:
                $room='口腔一';
                break;
            case 2:
                $room='口腔二';
                break;
            case 3:
                $room='神经外科';
                break;
            case 4:
                $room='胸科';
                break;
            case 4:
                $room='普外';
                break;
            default:
                $room=-1;
        }
        $condition="1=1";
        if($status!=-1){
            $condition=$condition." and status ='".$status."'";
         }
        if($room!=-1){
            $condition=$condition." and room ='".$room."'";
        }
        if($prod!=0){
            $condition=$condition." and category_id=".$prod;
        }
        if($sn!=null and $sn!='undefined'){
            $condition=$condition." and seriesno like '%".$sn."%'";
        }

       $data = IncubatorinfoModel::where($condition)->select();

        return json([
            'list' => $data
        ]);
    }

    public function findbytype()
    {
        $id = $this->request->get('type/d', 0);
        switch($id){
            case 1: $type='在用';
            break;
            case 2: $type='备机';
            break;
            case 3: $type='报修待接修';
            break;
            case 4: $type='维修中';
            break;
        }

        $data = IncubatorinfoModel::where(['status'=>$type,'org_id' => $this->org['id']])->select();

        return json([
            'list' => $data
        ]);
    }
    
    public function find()
    {
         $id = $this->request->get('id/d', 0);
         $currenttab = $this->request->get('type/d', 0);
         switch($currenttab){
             case 1:
                 $type='每日检查';
                 break;
             case 2:
                 $type='每周检查';
                 break;
             case 3:
                 $type='预防性维护';
                 break;
             default :
                 $type='全部';
         }
         $data = IncubatorinfoModel::get($id,"IncubQcorder");
         if($type=='全部'){
             $incublist = IncubQcorderModel::where(['incub_id'=>$id])->order('created_dt desc')->select();
         }else{
             $incublist = IncubQcorderModel::where(['incub_id'=>$id,'type'=>$type])->order('created_dt desc')->select();
         }
        return json([
            'item' => $data,
            'list'=>$incublist
        ]);
    }
     public function enterincub()
    {
        
         $data = [
                 'id' => $this->request->get('id/d', 0),
                  'status' => "报修未接修"
            ];
        IncubatorinfoModel::update($data);  
        
        return json([
            'item' => "报修成功"
        ]);
    }
      public function leaveincub()
    {
        
         $data = [
                 'id' => $this->request->get('id/d', 0),
                  'status' => "维修中"
            ];
        IncubatorinfoModel::update($data);  
        
        return json([
            'item' => "接修成功"
        ]);
    }
      public function cleanincub()
    {
        
         $data = [
                 'id' => $this->request->get('id/d', 0),
                  'status' => "使用中"
            ];
        IncubatorinfoModel::update($data);  
        
        return json([
            'item' => "维修完成"
        ]);
    }
    
      public function login()
    {
        $username = $this->request->get('username/s', '', 'trim');
        $password = $this->request->get('password/s', '');
        $auth = Auth::getInstance();
        if ($auth->login($username, $password)) {
            return json(['isLogin' => true]);
        } else {
            return json(['isLogin' => false]);
        }
    }


    public function confirmmtn() {

        $type = $this->request->post('type/d');
        $result = $this->request->post('result/s');
        $memo = $this->request->post('memo/s');
        $id = $this->request->post('id/');
        switch($type) {
            case 1 :
                $data = [
                    'id' => $id,
                    'cleandt' => date("Y-m-d h:i:s"),
                    'filterflag' => $result,
                    'pm_memo' => $memo
                ];
                $qcorder = [
                    'incub_id' => $id,
                    'created_dt' => date("Y-m-d h:i:s"),
                    'type' => '预防性维护',
                    'memo' => $memo,
                    'result' => $result,
                    'operator_id' =>  $this->user['id'],
                    'operator_name' =>  $this->user['realname']
                ];
                break;
            case 2 :
                $data = [
                    'id' => $id,
                    'dailycleandt' => date("Y-m-d h:i:s"),
                    'filterflag' => $result,
                    'pm_memo' => $memo
                ];
                $qcorder = [
                    'incub_id' => $id,
                    'created_dt' => date("Y-m-d h:i:s"),
                    'type' => '每日检查',
                    'memo' => $memo,
                    'result' => $result,
                    'operator_id' =>  $this->user['id'],
                    'operator_name' =>  $this->user['realname']
                ];
                break;
            case 3 :
                $data = [
                    'id' => $id,
                    'weeklycleandt' => date("Y-m-d h:i:s"),
                    'filterflag' => $result,
                    'pm_memo' => $memo
                ];
                $qcorder = [
                    'incub_id' => $id,
                    'created_dt' => date("Y-m-d h:i:s"),
                    'type' => '每周检查',
                    'memo' => $memo,
                    'result' => $result,
                    'operator_id' =>  $this->user['id'],
                    'operator_name' =>  $this->user['realname']
                ];
                $cleantype='每周检查';
                break;
        }
            IncubatorinfoModel::update($data);
            IncubQcorderModel::create($qcorder);
            return json([
                'item' => "检查完成"
            ]);
    }

    public function borrow() {
        $loaner = $this->request->post('loaner/s');
        $health = $this->request->post('health/d');
        $memo = $this->request->post('memo/s');
        $id = $this->request->post('id/d');
        $data = [
            'id' => $id,
            'loan_status' => '已借出'
        ];
        $loanorder = [
            'incub_id' => $id,
            'created_dt' => date("Y-m-d h:i:s"),
            'type' => '借出',
            'memo' => $memo,
            'loaner' => $loaner,
            'health' => $health,
            'operator_id' =>  $this->user['id'],
            'operator_name' =>  $this->user['realname']
        ];
        IncubatorinfoModel::update($data);
        IncubLoanorderModel::create($loanorder);
        return json([
            'item' => "外借成功"
        ]);
    }

    public function loanreturn() {
        $loaner = $this->request->post('loaner/s');
        $health = $this->request->post('health/d');
        $memo = $this->request->post('memo/s');
        $id = $this->request->post('id/d');
        $data = [
            'id' => $id,
            'loan_status' => '在库'
        ];
        $loanorder = [
            'incub_id' => $id,
            'created_dt' => date("Y-m-d h:i:s"),
            'type' => '归还',
            'memo' => $memo,
            'loaner' => $loaner,
            'health' => $health,
            'operator_id' =>  $this->user['id'],
            'operator_name' =>  $this->user['realname']
        ];
        IncubatorinfoModel::update($data);
        IncubLoanorderModel::create($loanorder);
        return json([
            'item' => "归还成功"
        ]);
    }

    public function findloanorder()
    {
        $id = $this->request->get('id/d', 0);
        $currenttab = $this->request->get('type/d', 0);
        switch($currenttab){
            case 1:
                $type='借出';
                break;
            case 2:
                $type='归还';
                break;
            default :
                $type='全部';
        }
        $data = IncubatorinfoModel::get($id,"IncubLoanorder");
        if($type=='全部'){
            $incublist = IncubLoanorderModel::where(['incub_id'=>$id])->order('created_dt desc')->select();
        }else{
            $incublist = IncubLoanorderModel::where(['incub_id'=>$id,'type'=>$type])->order('created_dt desc')->select();
        }
        return json([
            'item' => $data,
            'list'=>$incublist
        ]);
    }
}
