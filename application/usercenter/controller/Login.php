<?php


namespace app\usercenter\controller;



use app\common\library\enum\CodeEnum;
use app\usercenter\logic\index;
use think\captcha\Captcha;
use think\Config;
use think\Controller;
use think\Request;

class Login extends Controller
{

    /**
     * 支付中心用户登录
     */
    public function index()
    {
        if ($this->request->isPost()){
            $parmas = $this->request->param();
            $indexLogic = new index();
            $ret = $indexLogic->login($parmas);
            if ($ret['code'] != CodeEnum::SUCCESS) {
               return   json_encode(['code' => 0, 'msg' => $ret['msg']]);
            }
            return   json_encode(['code' => 1, 'msg' => '登录成功']);
        }
        return $this->fetch();
    }

    /**
     * 生成验证码
     */
    public function verfy_img()
    {
        $captcha = new Captcha();
        $captcha->fontSize = 20;
        $captcha->length = 3;
        $captcha->useNoise = false;
        return $captcha->entry('usercenter_login');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        clear_user_login_session();
        return $this->redirect('usercenter/login/index');
    }
}