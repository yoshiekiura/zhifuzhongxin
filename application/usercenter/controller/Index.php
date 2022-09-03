<?php


namespace app\usercenter\controller;


use think\Request;

class Index extends Base
{
    public function Index(Request $request)
    {
        $where = [];
        $username = $request->param('username');

        switch ($this->user['user_type']){
            case '1':
                $where['user_type'] = 2;
                break;
            case '2':
                $where['user_type'] = 1;
                break;
            default:
                $where['user_type'] = 10000000;
                break;
        }

        $createTime = $this->request->param('createTime', '');
        $username && $where['username'] = ['like', '%'.$username.'%'];
        if ($createTime){
            switch ($createTime){
                case 'w':
                    $where['create_time'] = ['>', strtotime('-1 week')];
                    break;
                case 'm':
                    $where['create_time'] = ['>', strtotime('-1 month')];
                    break;
                case '3m':
                    $where['create_time'] = ['>', strtotime('-3 month')];
                    break;
                case 'd':
                    $where['create_time'] = ['>', strtotime('-1 year')];
                    break;
            }
        }
        $users = $this->modelPayCenterUser->where(['status' => 1])->where($where)->order('create_time desc')->paginate($this->request->param('limit', 12));;
        foreach ($users as $user){
            $user->avatar = letter_avatar($user->username);
        }
        $this->assign('user_info', $this->user);
        $this->assign('users', $users);
        $this->assign('username', $username);
        $this->assign('createTime', $createTime);
        return $this->fetch('index');
    }

    /**
     * 获取支付中心用户列表
     * @throws \think\exception\DbException
     */
    public function getUsersLists()
    {
        $where = [];
        $username = $this->request->param('username');
        $createTime = $this->request->param('createTime');
        $userType = $this->request->param('userType');
        $username && $where['username'] = ['like', '%'.$username.'%'];
        if ($createTime){
            switch ($createTime){
                case 'w':
                    $where['create_time'] = ['>', strtotime('-1 week')];
                    break;
                case 'm':
                    $where['create_time'] = ['>', strtotime('-1 month')];
                    break;
                case '3m':
                    $where['create_time'] = ['>', strtotime('-3 month')];
                    break;
                case 'd':
                    $where['create_time'] = ['>', strtotime('-1 year')];
                    break;
            }
        }
        $userType && $where['user_type'] = $userType;
        $users = $this->modelPayCenterUser->where(['status' => 1])->where($where)->order('create_time desc')->paginate($this->request->param('limit', 10));;

        foreach ($users as $user){
            $user->avatar = letter_avatar($user->username);
        }
        return $this->success('success','', $users);
    }

    /**
     * 获取渠道列表
     * @throws \think\exception\DbException
     */
    public function getChannelLists()
    {
        $where = [];
        $name = $this->request->param('name');
        $createTime = $this->request->param('createTime');
        $name && $where['name'] = ['like', '%'.$name.'%'];
        if ($createTime){
            switch ($createTime){
                case 'w':
                    $where['create_time'] = ['>', strtotime('-1 week')];
                    break;
                case 'm':
                    $where['create_time'] = ['>', strtotime('-1 month')];
                    break;
                case '3m':
                    $where['create_time'] = ['>', strtotime('-3 month')];
                    break;
                case 'd':
                    $where['create_time'] = ['>', strtotime('-1 year')];
                    break;
            }
        }
        $channels =  $this->modelPayChannel->where(['status' => 1])->where($where)->order('create_time desc')->paginate($this->request->param('limit', 10));;
        foreach ($channels as $channel){
            $channel->avatar = letter_avatar($channel->name);
        }
        return $this->success('success','', $channels);
    }

    /**
     * 在线文档
     */
    public function apiDoc()
    {
        //返回所有对接编码
        $codes = $this->modelPayCode->where('status',1)->select();
        $codes = collection($codes)->isEmpty() ? $codes: collection($codes)->toArray();
        $this->assign('codes',$codes);
        return $this->fetch();
    }

    /**
     * 用户绑定google验证器
     * @return mixed
     */
    public function bindGoogle()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post('i/a');
            if (empty($data['google_secret_key'])) {
                $this->result(0, '参数错误');
            }
            if (empty($data['google_code'])) {
                $this->result(0, '请输入GOOGLE验证码');
            }
            $ret = $this->logicGoogleAuth->checkGoogleCode($data['google_secret_key'], $data['google_code']);
            if ($ret == false) {
                $this->result(0, '绑定GOOGLE失败,请扫码重试');
            }
            unset($data['google_code']);
            $data['is_need_google_verify'] = 1;
            $ret = $this->modelPayCenterUser->where(['id' => $this->user['id']])->update($data);
            if ($ret !== false) {
                $this->result(1, '绑定成功');
            }
            $this->result(0, '绑定失败');
        }
        //获取商户详细信息
        $userInfo = $this->modelPayCenterUser->get(['id' =>  $this->user['id']]);
        $this->assign('user', $userInfo);
        if ($userInfo['is_need_google_verify'] == 0) {
            $google['google_secret'] = $this->logicGoogleAuth->createSecretkey();
            $google['google_qr'] = $this->logicGoogleAuth->getQRCodeGoogleUrl($google['google_secret']);
            $this->assign('google', $google);
        }
        return $this->fetch();
    }
}