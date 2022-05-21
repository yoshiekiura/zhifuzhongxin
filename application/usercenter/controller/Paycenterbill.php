<?php


namespace app\usercenter\controller;


class Paycenterbill extends Base
{
    /**
     * 账单列表
     */
    public function index()
    {
        $map['uid'] = $this->user['id'];
        !empty($this->request->get('jl_class')) && $map['jl_class']
            = $this->request->get('jl_class');

        $list = $this->modelPayCenterBill->alias('a')->where($map)->order('id desc')->paginate($this->request->get('limit') ?? 10);

        $this->assign('money', $this->user['money']);
        $this->assign('list', $list);
        return $this->fetch();
    }
}