<?php

/**
 *  +----------------------------------------------------------------------
 *  | 中通支付系统 [ WE CAN DO IT JUST THINK ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2018 http://www.iredcap.cn All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed ( https://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: Brian Waring <BrianWaring98@gmail.com>
 *  +----------------------------------------------------------------------
 */

namespace app\common\logic;


class UsdtTopupOrders extends BaseLogic
{
    /**
     * 充值列表
     * @param array $where
     * @param bool $field
     * @param null $join
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public function getTopupList($where = [], $field = true, $join = null, $order = 'create_time desc', $paginate = 15)
    {
        $this->modelUsdtTopupOrders->alias('a');

        if ($join){
            $this->modelUsdtTopupOrders->join = $join;
        }

        $this->modelUsdtTopupOrders->paginate = !$paginate;

        return $this->modelUsdtTopupOrders->getListV2($where, $field, $order, $paginate);
    }

}