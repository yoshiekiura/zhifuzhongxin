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
    public function getWithdrawList()
    {
        $where = [];
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
        $this->result($this->logicWithdrawUsdtOrders->manualRemit($request->param()));
    }
}