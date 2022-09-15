<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
use app\common\service\Code;
use think\Db;
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
    public function remit($data)
    {
        Db::startTrans();
        try {
            $order = $this->modelWithdrawUsdtOrders->where('id', $data['id'] ?? 0)->find();
            if (empty($order)){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单不存在'];
            }
            if ($order->status != 1){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单状态错误' ];
            }
            $order->status = 2;
            $order->save();
            $result = $this->logicQueue->pushJobDataToQueue('AutoUsdtCenterBalanceRemit' , $order , 'AutoUsdtCenterBalanceRemit');
            if (!$result){
               return ['code' => CodeEnum::ERROR, 'msg' => '推送打款队列失败'];
            }
            action_log('推送','推送USDT提现订单打款，单号：' . $order['trade_no']);
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '推送打款入队列'];
        }catch (Exception $ex){
            Db::rollback();
            return ['code' => CodeEnum::ERROR, 'msg' => '订单不存在'];
        }
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
    public function manualRemit($data)
    {
        Db::startTrans();
        try {
            $order = $this->modelWithdrawUsdtOrders->lock(true)->where('id', $data['id'] ?? 0)->find();
            if (empty($order)){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单不存在'];
            }
            if ($order->status != 2){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单状态错误' ];
            }

            $order->transfer_type = 2;
            $order->transfer_time = time();
            $order->admin_id = is_admin_login();
            $order->admin_success_note = isset($data['admin_success_note']) ? $data['admin_success_note'] : '';
            $order->save();
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '操作成功'];
        }catch (Exception $ex) {
            Db::rollback();
            return ['code' => CodeEnum::SUCCESS, 'msg' => $ex->getMessage()];
        }
    }
}