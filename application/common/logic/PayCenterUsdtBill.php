<?php


namespace app\common\logic;


class PayCenterUsdtBill extends BaseLogic
{

    /**
     * 账单列表
     * @param array $where
     * @param bool $field
     * @param null $join
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public function getBillList($where = [], $field = true, $join = null, $order = 'create_time desc', $paginate = 15)
    {
        $this->modelPayCenterUsdtBill->alias('a');

        if ($join){
            $this->modelPayCenterUsdtBill->join = $join;
        }

        $this->modelPayCenterUsdtBill->paginate = !$paginate;

        return $this->modelPayCenterUsdtBill->getListV2($where, $field, $order, $paginate);
    }
}