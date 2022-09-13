<?php


namespace app\usercenter\controller;


use think\Request;

class Paycenterusdtbill extends Base
{

    /**
     * 账单列表
     * @param Request $request
     */
    public function index(Request $request)
    {
        $jl_class = $request->param('jl_class');

        $jl_class && $where['a.jl_class'] = $jl_class;

        $where['a.uid'] = $this->user['id'];
        $lists = $this->logicPayCenterUsdtBill->getBillList($where);

        $this->assign('lists', $lists);
        return $this->fetch();
    }

}