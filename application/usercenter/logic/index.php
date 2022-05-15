<?php


namespace app\usercenter\logic;


use app\common\library\enum\CodeEnum;
use app\common\logic\BaseLogic;
use think\captcha\Captcha;

class index extends BaseLogic
{

    public function login($params)
    {
        //去除前后空格
        $username = trim($params['username'] ?? '');
        $password = trim($params['password'] ?? '');
        $vercode = trim($params['vercode'] ?? '');
        $google_code = trim($params['google_code'] ?? '');

        if (empty($username)) {
            return ['code' => CodeEnum::ERROR, 'msg' => '用户名不能为空'];
        }
        if (!ctype_alnum($username)) {
            return ['code' => CodeEnum::ERROR, 'msg' => '账号输入不合法'];
        }
        if (empty($password)) {
            return ['code' => CodeEnum::ERROR, 'msg' => '密码不能为空'];
        }
        //检测图形验证码
        $captcha = new Captcha();

        if ($captcha->check($vercode, 'usercenter_login') == false) {
            return ['code' => CodeEnum::ERROR, 'msg' => '图形验证码错误'];
        }
        $map['username'] = $username;
        $user_info = $this->modelPayCenterUser->where($map)->find();
        if (!$user_info) {
            return ['code' => CodeEnum::ERROR, 'msg' => '账号不存在'];
        } elseif ($user_info['status'] == 0) {
            return ['code' => CodeEnum::ERROR, 'msg' => '您的账号已冻结，请联系管理员!'];
        }else {
            //验证码是否开启
            if ($user_info['google_secret_key']) {
                if (!$google_code) {
                    return ['code' =>  CodeEnum::ERROR, 'msg' => '请输入google验证码'];
                }
                //验证code
                if ($this->logicGoogleAuth->checkGoogleCode($user_info['google_secret_key'], $google_code) == false) {
                    return ['code' => CodeEnum::ERROR , 'msg' => 'google验证码不确定'];
                }
            }
            if (pwdMd52($password) != $user_info['password']) {
                return ['code' => CodeEnum::ERROR, 'msg' => '密码错误'];
            } else {
                session('usercenter', $user_info['id']);
                return ['code' => CodeEnum::SUCCESS, 'msg' => '登录成功'];
            }
        }
    }


}