<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
use app\common\service\Code;
use think\Exception;

class WithdrawUsdtOrders extends BaseLogic
{


    /**
     * 提现列表
     * @param array $where
     * @param bool $field
     * @param null $join
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public function getWithdrawList($where = [], $field = true, $join = null, $order = 'create_time desc', $paginate = 15)
    {
        $this->modelWithdrawUsdtOrders->alias('a');

        if ($join){
            $this->modelWithdrawUsdtOrders->join = $join;
        }

        $this->modelWithdrawUsdtOrders->paginate = !$paginate;

        return $this->modelWithdrawUsdtOrders->getListV2($where, $field, $order, $paginate);
    }

    /**
     * 打款
     */
    public function remit()
    {
        
    }

    /**
     * 驳回
     */
    public function reject($data)
    {
        try {
            $orders = $this->modelWithdrawUsdtOrders->where('id', $data['id'] ?? 0)->find();
            if (empty($orders)){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单不存在'];
            }
            if ($orders->status != 1){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单状态错误' ];
            }
            $orders->status = 0;
            $orders->save();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '操作成功'];
        }catch (Exception $ex) {
            return ['code' => CodeEnum::SUCCESS, 'msg' => $ex->getMessage()];
        }
    }

    /**
     * 手动到账
     */
    public function manualRemit()
    {

    }
}