<?php


namespace app\common\logic;


class CenterBalance extends BaseLogic
{

    /**
     * 用户余额统计
     * @return mixed
     */
    public function  getBalaceStat()
    {
        $fields = "sum(usdt_enable) as usdt_enable,sum(usdt_disable) as usdt_disable ,sum(usdt_enable+usdt_disable) as usdt_total, 
                    sum(pl_enable) as pl_enable,sum(pl_disable) as pl_disable ,sum(pl_enable+pl_disable) as pl_total";
        return $this->modelCenterBalance->getInfo(['status'=>1],$fields);
    }

    /**
     * 获取用户资产列表
     */
    public function getBalanceList($where, $field, $order, $paginate){
        $this->modelCenterBalance->alias('a');
        $join = [
            ['pay_center_user u', 'u.id = a.uid']
        ];
        $this->modelCenterBalance->join = $join;
        return $this->modelCenterBalance->getList($where, $field, $order, $paginate);
    }
}