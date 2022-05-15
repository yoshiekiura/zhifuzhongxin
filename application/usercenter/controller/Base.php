<?php


namespace app\usercenter\controller;


use app\common\controller\Common;
use app\common\model\PayCenterUser;
use think\Controller;
use think\Session;

class Base extends Common
{

    protected $logined = false; //登录状态
    public $user = null; //用户信息

    public function _initialize()
    {
        //检测是否登录
        if (!$this->isLogin()) {
            $this->redirect('usercenter/login/index');
            exit;
        }
        $this->assign('user_info', $this->user);
    }
    /**
     * 检测是否登录
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->logined) {
            return true;
        }
        $usercenter = Session::get('usercenter');
        if (!$usercenter) {
            return false;
        }
        $userInfo = PayCenterUser::get($usercenter);
        if (!$userInfo or $userInfo['status'] !==1 ){
            return false;
        }
        $this->user = $userInfo;
        $this->logined = true;
        return true;
    }

    public function isAgent()
    {
        if ($this->user['user_type'] != 4 )
            return false;
        return true;
    }
}