<?php


namespace app\usercenter\controller;


use think\Request;

class Index extends Base
{
    public function Index(Request $request)
    {
        $where = [];
        $createTime = $this->request->param('createTime', '');
        $username = $request->param('username');
        if ($createTime){
            switch ($createTime){
                case 'w':
                    $where['a.create_time'] = ['>', strtotime('-1 week')];
                    break;
                case 'm':
                    $where['a.create_time'] = ['>', strtotime('-1 month')];
                    break;
                case '3m':
                    $where['a.create_time'] = ['>', strtotime('-3 month')];
                    break;
                case 'd':
                    $where['a.create_time'] = ['>', strtotime('-1 year')];
                    break;
            }
        }

        $lists = [];

        switch ($this->user['user_type']){
            case 1:
                $username && $where['a.username'] = array('LIKE', '%'. $username .'%');
                $join = [
                    ['pay_center_user pu', 'pu.id = a.pay_center_uid'],
                ];
                $field = 'a.*, pu.username as merchant_username';
                $lists =   $this->logicUser->getUserListV2($where, $join, $field, 'create_time desc', 12);
                foreach ($lists as $user){
                    $user->avatar = letter_avatar($user->username);
                }
                break;
            case 2:
                $field = 'a.*, u.username, go.id as guarantee_id';
                $username && $where['a.username'] = array('LIKE', '%'. $username .'%');
                $join = [
                    [ 'guarantee_orders go', 'go.channel_id = a.id and go.merchant_user_id = '.$this->user['id'] . ' and go.status not in (2,4)',  'left'],
                    [ 'pay_center_user u', 'u.id = a.pay_center_uid'],
                ];
                $lists = $this->logicPay->getChannelListV2(['a.status' => 1], $field, $join,'create_time desc', 12);
                foreach ($lists as $channel){
                    $channel->avatar = letter_avatar($channel->name);
                }
                break;
            case 3:
            case 4:
                break;
            default:
                break;
        }

        $this->assign('user_info', $this->user);
        $this->assign('lists', $lists);
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