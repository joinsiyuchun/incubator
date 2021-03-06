<?php
namespace app\common\controller;

use think\Controller;
use think\facade\Request;
use think\facade\Session;

class Apitest extends Controller
{
    protected $checkLoginExclude = [];
    protected $user = [];

    protected function initialize()
    {
        $action = Request::action();
        $this->user = 'admin';
       
    }

    protected function request($url, $method, array $data = [], array $headers = [])
    {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $data);
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ci);
        if (curl_errno($ci)) {
            $response = 'Errno ' . curl_error($ci);
        }
        curl_close($ci);
        return $response;
    }
}
