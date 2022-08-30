<?php


namespace app\usercenter\controller;


use think\Request;

class Index extends Base
{
    public function Index(Request $request)
    {

        $where = [];

        $username = $request->param('username');
        $createTime = $this->request->param('createTime', '');

        $userType = $this->request->param('userType', '');
        $username && $where['username'] = ['like', '%'.$username.'%'];
        $userType && $where['user_type'] = $userType;
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
        $this->assign('userType', $userType);
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
}