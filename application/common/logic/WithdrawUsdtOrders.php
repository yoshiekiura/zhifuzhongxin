<?php


namespace app\common\logic;


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

}