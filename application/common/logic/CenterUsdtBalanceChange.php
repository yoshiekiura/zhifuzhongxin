<?php


namespace app\common\logic;


class CenterUsdtBalanceChange extends BaseLogic
{

    /**
     * 资金统计
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getBalanceChangeInfo($where = [], $field = 'COALESCE(sum(`increase`),0) as total_increase,COALESCE(sum(`reduce`),0) as total_reduce')
    {
        return $this->modelCenterUsdtBalanceChange->getInfo($where, $field);
    }

    /**
     * 资金变动记录
     * @param array $where
     * @param bool $field
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public function getBalanceChangeList($where = [], $field = 'a.*', $order = 'a.create_time desc', $paginate = 15)
    {
        $this->modelCenterUsdtBalanceChange->alias('a');
        $this->modelCenterUsdtBalanceChange->limit = !$paginate;
        $join = [
            ['pay_center_user u', 'u.id = a.uid']
        ];

        $this->modelCenterUsdtBalanceChange->join = $join;

        return $this->modelCenterUsdtBalanceChange->getList($where, $field, $order, $paginate);
    }

}