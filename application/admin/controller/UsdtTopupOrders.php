<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;
use think\Request;

class UsdtTopupOrders extends BaseAdmin
{

    public function index()
    {
        return $this->fetch();
    }

    /**
     * 充值列表
     * @param Request $request
     */
    public function getTopupList(Request $request)
    {
        $where = [];
        $trade_no = $request->param('trade_no');
        $username = $request->param('username');
        $status = $request->param('status', -1);
        $complete_type = $request->param('complete_type', -1);
        $trade_no && $where['a.trade_no'] = $trade_no;
        $username && $where['u.username'] = array('like', '%'. $username .'%');
        $status >= 0 && $where['a.status'] = $status;
        $complete_type >= 0 && $where['a.complete_type'] = $complete_type;
        $join = [
            ['pay_center_user u', 'u.id = a.uid']
        ];

        $field = 'a.*, u.username';

        $data =  $this->logicUsdtTopupOrders->getTopupList($where, $field, $join);

        $this->result(!$data->isEmpty()?
            [
                'code' => CodeEnum::SUCCESS,
                'msg'=> '',
                'count'=>$data->total(),
                'data'=>$data->items()
            ] : [
                'code' => CodeEnum::ERROR,
                'msg'=> '暂无数据',
                'count'=>$data->total(),
                'data'=>$data->items()
            ]);
    }

    public function manualFinish(Request $request)
    {
        if ($request->isAjax()) $this->result($this->logicUsdtTopupOrders->manualFinish($request->param()));
        return $this->fetch();
    }
}