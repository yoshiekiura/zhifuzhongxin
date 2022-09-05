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
        $where = [];
        $lists  = $this->modelPayCenterUserAccount
            ->alias('a')
            ->join('cm_pay_channel c', 'c.id = a.channel_id')
            ->where($where)
            ->field('a.*, c.name')
            ->order('a.create_time desc')
            ->paginate($request->input('limit'));
        halt($lists);
        $this->assign('lists', $lists);
        return $this->fetch();
    }
}