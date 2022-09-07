<?php


namespace app\usercenter\controller;


use think\Request;

class PayCenterUserAccount extends Base
{

    /**
     * 账号列表
     */
    public function index(Request $request)
    {

        $id = $request->param('id');
        $name = $request->param('name');
        $channel_name = $request->param('channel_name');
        $user_name = $request->param('user_name');
        $where = [];
        $id && $where['a.id'] = $id;
        $name && $where['a.name'] = ['like', '%'. $name .'%'];
        $channel_name && $where['c.name'] = ['like', '%'. $channel_name.'%'];
        $user_name && $where['u.username'] = ['like', '%'. $user_name.'%'];
        $where['a.pay_center_uid'] = $this->user['id'];
        $where['a.status'] = ['in' , [0,1]];
        $lists  = $this->modelPayCenterUserAccount
            ->alias('a')
            ->join('cm_pay_channel c', 'c.id = a.channel_id')
            ->join('cm_user u', 'u.uid = a.user_id')
            ->where($where)
            ->field('a.*, c.name as channel_name, u.username as user_name')
            ->order('a.create_time desc')
            ->paginate($request->param('limit'));
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /**
     * 对接信息
     */
    public function catchInfo()
    {
        $id = $this->request->param('id');
        $where = [
            'a.id' => $id,
            'a.pay_center_uid' => $this->user['id']
        ];
        $userinfo = $this->modelPayCenterUserAccount->where($where)
            ->alias('a')
            ->join('cm_user u', 'a.user_id = u.uid')
            ->join('cm_api ap', 'a.user_id = ap.uid')
            ->field('u.*, ap.key')
            ->find();
        $userinfo['pay_address']= $this->modelConfig->where(['name' => 'pay_address'])->value('value');
        $userinfo['query_address'] = $this->modelConfig->where(['name' => 'query_address'])->value('value');
        $userinfo['callback_ip'] = $this->modelConfig->where(['name' => 'notify_ip'])->value('value');
        if (!$userinfo){
            $this->error('数据错误');
        }
        $this->assign('userinfo', $userinfo);
        return $this->fetch();
    }
}