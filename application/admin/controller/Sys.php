<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Company as CompanyModel;



class Sys extends Admin
{
    public function company()
    {
        $companylist=CompanyModel::all();
         $this->assign('companylist', $companylist);
        return $this->fetch();
    }
    public function user()
    {
      
       $username = $this->request->get('username/s');
       $realname=$this->request->get('realname/s');
       $mobile = $this->request->get('mobile/s');
       $role=$this->request->get('role/s');
       $status=$this->request->get('status/s');
       $action=$this->request->get('action/s');
       
       $search="1=1";
        
       
       if(!empty($username)){
            $tmp = strtr($username, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search=$search." and username like '%".$tmp."%'";
            
        }
        if(!empty($realname)){
            $tmp = strtr($realname, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search=$search." and realname like '%".$tmp."%'";
            
        }
         if(!empty($mobile)){
            $tmp = strtr($mobile, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $search=$search." and mobile like '%".$tmp."%'";
            
        }
         if(!empty($status) and $status!=='全部'){
            $search=$search." and status = '".$status."'";
        }
        if(!empty($role) and $role!=='全部'){
            $search=$search." and role = '".$role."'";
        }
        if($action!=="search"){
            $userinfo = AdminModel::all(); 
        }else{
            $userinfo = AdminModel::where($search)->select(); 
        }
//       $this->alert('info',$search);
        $this->assign('userinfo', $userinfo);
        return $this->fetch();
        
    }
    
   public function createuser()
    {
       $action = $this->request->get('action/s');
       if($action==="add"){
          $data = [
             'username' => $this->request->get('username/s'),
             'realname' => $this->request->get('realname/s'),
             'mobile' => $this->request->get('mobile/s'),
             'status' => $this->request->get('status/s'),
             'role' => $this->request->get('role/s'),
             'password' => 'cb9896ea0634f66d7ae564b0c8b2e397',
             'salt' => '4b0c353cb98ecd2b656a708f271283ea'
         ];
         AdminModel::insert($data);
         return $this->redirect(url('admin/sys/user'));
       }

        return $this->fetch();
   }
   
   public function updateuser()
    {
       $action = $this->request->get('action/s');
       if($action==="add"){
          $data = [
             'id' => $this->request->get('id/d'),
             'username' => $this->request->get('username/s'),
             'realname' => $this->request->get('realname/s'),
             'mobile' => $this->request->get('mobile/s'),
             'status' => $this->request->get('status/s'),
             'role' => $this->request->get('role/s')
         ];
         AdminModel::update($data);  
         $this->alert('success', '修改分类成功。');
       }
       
       $id = $this->request->get('id/d');
       $userinfo=AdminModel::where('id',$id)->find();
      // $this->alert('info',$userinfo);
    
        $this->assign('userinfo', $userinfo);
        return $this->fetch();
   }
    
}
