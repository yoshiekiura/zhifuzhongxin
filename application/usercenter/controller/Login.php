<?php


namespace app\usercenter\controller;


use think\Controller;

class Login extends Controller
{

    /**
     * 支付中心用户登录
     */
    public function login()
    {
       return $this->fetch('login');
    }
}