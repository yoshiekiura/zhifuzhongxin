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


use app\common\library\enum\CodeEnum;
use think\Db;
use think\Exception;

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

        return $this->modelUsdtTopupOrders->getList($where, $field, $order, $paginate);
    }

    /**
     * 手动到账
     */
    public function manualFinish($data)
    {
        Db::startTrans();
        try {
            $usdtTopupOrders = $this->modelUsdtTopupOrders->lock(true)->where(['id'=>$data['id'] ?? 0])->find();
            if (empty($usdtTopupOrders)){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单错误'];
            }
            if ($usdtTopupOrders->status != 0){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单已经支付'];
            }
            $usdtTopupOrders->status = 1;
            $usdtTopupOrders->admin_note = isset($data['admin_note']) ? $data['admin_note'] : 0;
            $usdtTopupOrders->admin_id = is_login();
            $usdtTopupOrders->complete_type = 2;
            $usdtTopupOrders->complete_time = time();
            $usdtTopupOrders->save();

            $this->logicPayusercenter->usdtChangeV2($usdtTopupOrders->uid, 'enable', 1, 1
                ,$usdtTopupOrders->usdt_sum, '充值后台手动到账，订单号：'. $usdtTopupOrders->trade_no);

            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '操作成功'];
        }catch (Exception $ex){
            Db::rollback();
            return ['code' => CodeEnum::ERROR, 'msg' => $ex->getMessage()];
        }
    }
}