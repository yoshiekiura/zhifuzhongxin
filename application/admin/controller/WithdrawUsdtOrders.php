<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;
use think\Request;

class WithdrawUsdtOrders extends BaseAdmin
{

    /**
     * 提现列表
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取提现列表
     * @return mixed
     */
    public function getWithdrawList(Request $request)
    {
        $where = [];
        $trade_no = $request->param('trade_no');
        $username = $request->param('username');
        $status = $request->param('status', -1);
        $transfer_type = $request->param('transfer_type', -1);
        $trade_no && $where['a.trade_no'] = $trade_no;
        $username && $where['u.username'] = array('like', '%'. $username .'%');
        $status>=0 && $where['a.status'] = $status;
        $transfer_type>=0 && $where['a.transfer_type'] = $transfer_type;

        $join = [
            ['pay_center_user u', 'u.id = a.uid'],
        ];
        $field = 'a.*, u.username';
        $data = $this->logicWithdrawUsdtOrders->getWithdrawList($where, $field, $join);

        $this->result(!$data->isEmpty()?
            [
                'code' => CodeEnum::SUCCESS,
                'msg'=> '',
                'count'=>$data->count(),
                'data'=>$data->items()
            ] : [
                'code' => CodeEnum::ERROR,
                'msg'=> '暂无数据',
                'count'=>$data->count(),
                'data'=>$data->items()
            ]);
    }

    /**
     * 打款
     */
    public function remit(Request $request)
    {
        $this->result($this->logicWithdrawUsdtOrders->remit($request->param()));
    }

    /**
     * 驳回
     */
    public function reject(Request $request)
    {
        $this->result($this->logicWithdrawUsdtOrders->reject($request->param()));
    }

    /**
     * 手动到账
     */
    public function manualRemit(Request $request)
    {
       if ($request->isAjax()) $this->result($this->logicWithdrawUsdtOrders->manualRemit($request->param()));
       return $this->fetch();
    }
}