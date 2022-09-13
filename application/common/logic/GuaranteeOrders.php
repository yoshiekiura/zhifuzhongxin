<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
use app\common\library\Tron;
use think\Db;
use think\Exception;

class GuaranteeOrders extends BaseLogic
{

    /**
     * 添加担保订单
     */
    public function addOrder($data)
    {
        $validate =  $this->validateGuaranteeOrders->scene('addOrder')->check($data);
        if (false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg' => $this->validateGuaranteeOrders->getError()];
        }

        $CenterUserModel = $this->modelPayCenterUser;
        $centerUserWh = ['status' => 1];
        $channelUser = $CenterUserModel->where(array_merge(['id' => $data['channel_user_id']], $centerUserWh))->find();
        $merchantUser = $CenterUserModel->where(array_merge(['id' => $data['merchant_user_id']], $centerUserWh))->find();
        $channel = $this->logicPay->getChannelInfo(['status' => 1, 'id' => $data['channel_id']]);
        if (empty($channelUser) or empty($merchantUser)){
            return ['code' => CodeEnum::ERROR, 'msg' => '用户不存在!'];
        }

        if ($channelUser['user_type'] !=1){
            return ['code' => CodeEnum::ERROR, 'msg' => '渠道用户类型错误！'];
        }

        if ( empty($channel)){
            return ['code' => CodeEnum::ERROR, 'msg' => '支付渠道错误！'];
        }

        $guaranteeOrders = $this->modelGuaranteeOrders->where([
            'merchant_user_id' => $merchantUser['id'],
            'channel_user_id' => $data['channel_user_id']
        ])->find();

        if ($guaranteeOrders && !in_array($guaranteeOrders->status, array(0, 2, 4))){
            return ['code' => CodeEnum::ERROR, 'msg' => ($guaranteeOrders->status == 1) ? '已有订单进行中！': '已有该渠道担保订单！'];
        }

        $usdtRate =  $this->modelConfig->where('name', 'usdt_rate')->value('value');
        if (!$usdtRate){
            return ['code' => CodeEnum::ERROR, 'msg' => '后台未设置usdt比率!'];
        }

        $usdtSum =  $this->getUsdtSum($data['amount'], $usdtRate);

        if ($usdtSum <= 0){
            return ['code' => CodeEnum::ERROR, 'msg' => '金额错误，请稍后在试！'];
        }

        $order = array(
            'trade_no'  => create_order_no(),
            'merchant_user_id' =>  $data['merchant_user_id'],
            'channel_user_id'  => $data['channel_user_id'],
            'channel_id' => $data['channel_id'],
            'amount'  => $data['amount'],
            'usdt_sum' => $usdtSum,
            'status' => 1,
            'effective_time' => time() + $this->modelGuaranteeOrders->effective_time,
            'pay_type' => 0,
        );

        $result =  $this->modelGuaranteeOrders->save($order);
        if ($result){
            return ['code' => CodeEnum::SUCCESS, 'msg' => '订单生成成功，待渠道付款。'];
        }else{
            return ['code' => CodeEnum::ERROR, 'msg' => '订单生成失败!'];
        }
    }

    /**
     * 获取usdt数量
     * @param $amount
     * @param $usdtRate
     * @return float|int|string|null
     */
    private function getUsdtSum($amount, $usdtRate){
        $execute_sum = 0;
        $usdtSum = bcdiv($amount, $usdtRate, 3);
        $GuaranteeOrdersModel = $this->modelGuaranteeOrders;
        $GuaranteeOrders = $GuaranteeOrdersModel->where('usdt_sum', $usdtSum)->find();
        if ($GuaranteeOrders){
            while ($GuaranteeOrders){
                $usdtSum = $usdtSum + 0.001;
                $GuaranteeOrders =  Db::query("SELECT * FROM `cm_guarantee_orders` WHERE `usdt_sum` = {$usdtSum} LIMIT 1");
//                print_r("SELECT * FROM `cm_guarantee_orders` WHERE `usdt_sum` = {$usdtSum} LIMIT 1");
//                print_r($GuaranteeOrders);
//                print_r(Db::name('guarantee_orders')->where(['usdt_sum' => $usdtSum])->getLastSql(true));
                $execute_sum ++;
//                print_r('$execute_sum:' . $execute_sum . ' $usdtSum:'  . $usdtSum . ' $GuaranteeOrders:'. ($GuaranteeOrders? '有值' :  '没有值'). PHP_EOL);
                if ($execute_sum >= 100){
                    $usdtSum = 0;
                    break;
                }
            }
        }
        return $usdtSum;
    }

    /**
     * 担保列表
     */
    public function getGuaranteeList($where = [], $join = null , $field = true, $order = 'create_time desc', $paginate = 15)
    {
        $this->modelGuaranteeOrders->alias('a');

        if (!is_null($join)){
            $this->modelGuaranteeOrders->join = $join;
        }

        $this->modelGuaranteeOrders->limit = !$paginate;
        return $this->modelGuaranteeOrders->getList($where, $field, $order, $paginate);
    }

    public function getOrderInfo($where = [], $field = true)
    {
        $this->modelGuaranteeOrders->alias('a');
        $join = [
            ['cm_pay_channel c', 'c.id = a.channel_id'],
            ['cm_pay_center_user u', 'u.id = c.pay_center_uid']
        ];
        return $this->modelGuaranteeOrders->where($where)->field($field)->join($join)->find();
    }

    /**
     * 余额支付
     */
    public function balancePay($data)
    {
        $validate =  $this->validateGuaranteeOrders->scene('balancePay')->check($data);
        if ( false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg' =>
                $this->validateGuaranteeOrders->getError() != '令牌数据无效' ? $this->validateGuaranteeOrders->getError()
                : '令牌数据无效请刷新页面！'];
        }

        Db::startTrans();
        try {
            $guaranteeOrdersWh = array('id'=>$data['id'], 'channel_user_id'=>$data['channel_user_id']);
            $guaranteeOrders = $this->modelGuaranteeOrders->where($guaranteeOrdersWh)->find();
            if ( empty($guaranteeOrders)){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单错误'];
            }
            if ($guaranteeOrders->status !=1){
                return ['code' => CodeEnum::ERROR, 'msg' => ($guaranteeOrders->status == 0)
                    ? '订单已关闭！': '订单已支付！' ];
            }
            if ( time() > $guaranteeOrders['effective_time']){
                return ['code' => CodeEnum::ERROR, 'msg' => '订单已过期！'];
            }

            $user = $this->modelPayCenterUser->lock(true)->find($data['channel_user_id']);
            if (bccomp($user->usdt_balance, $guaranteeOrders->usdt_sum) != 1) {
                return ['code' => CodeEnum::ERROR, 'msg' => '用户余额不足！'];
            }

            $this->logicPayusercenter->usdtChange(
                $user->id, 4, 0, $guaranteeOrders->usdt_sum,
                '渠道担保余额支付'. $guaranteeOrders->usdt_sum .'，订单号：'. $guaranteeOrders->trade_no
            );

            $guaranteeOrders->status = 2;
            $guaranteeOrders->pay_type = 1;
            $guaranteeOrders->pay_arrival_time = time();
            $guaranteeOrders->save();
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '支付成功'];
        }catch (Exception $ex){
            Db::rollback();
            return ['code' => CodeEnum::ERROR, config('app_debug') ? $ex->getMessage() : '未知错误'];
        }

    }


    /**
     * usdt在线支付
     */
    public function onlinePay($data)
    {
        $validate =  $this->validateGuaranteeOrders->scene('balancePay')->check($data);
        if ( false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg' => $this->validateGuaranteeOrders->getError()];
        }

        $guaranteeOrdersWh = array('id'=>$data['id'], 'channel_user_id'=>$data['channel_user_id']);
        $guaranteeOrders = $this->modelGuaranteeOrders->where($guaranteeOrdersWh)->find();

        if ( empty($guaranteeOrders)){
            return ['code' => CodeEnum::ERROR, 'msg' => '订单错误'];
        }
        if ($guaranteeOrders->status !=1){
            return ['code' => CodeEnum::ERROR, 'msg' => ($guaranteeOrders->status == 0)
                ? '订单已关闭！': '订单已支付！' ];
        }

//        if ( time() > $guaranteeOrders->effective_time){
//            return ['code' => CodeEnum::ERROR, 'msg' => '订单已过期！'];
//        }


        $rechargeAddress = 'http://www.baidu.com';

        $usdtQr =  generateQRfromGoogle($rechargeAddress);

        $usdtData = [
            'usdt_qr' =>   $usdtQr,
            'usdt_num' => $guaranteeOrders->usdt_sum
        ];

       if ($usdtQr){
           return ['code' => CodeEnum::SUCCESS, 'msg' => $usdtQr, 'data'=>$usdtData];
       }else{
           return ['code' => CodeEnum::ERROR, 'msg' => '地址获取失败！'];
       }

    }

    public function applyRefund($data)
    {
        $validate =  $this->validateGuaranteeOrders->scene('applyRefund')->check($data);
        if ( false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg' => $this->validateGuaranteeOrders->getError()];
        }

        $guaranteeOrdersWh = array('status' => ['in', [2,5]],'id'=>$data['id'], 'channel_user_id'=>$data['channel_user_id']);
        $guaranteeOrders = $this->modelGuaranteeOrders->where($guaranteeOrdersWh)->find();

        if ( empty($guaranteeOrders)){
            return ['code' => CodeEnum::ERROR, 'msg' => '订单错误'];
        }

        Db::startTrans();
        try {
            $guaranteeOrders->channel_refund_note = $data['channel_refund_note'];
            $guaranteeOrders->status = 3;
            $guaranteeOrders->save();
            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' => '申请成功，等待商户操作。'];
        }catch (Exception $ex) {
            Db::rollback();
            return ['code' => CodeEnum::ERROR, 'msg' => '未知错误！'];
        }
    }


    /**
     * 退保操作
     */
    public function refundHandle($data)
    {
        $validate =  $this->validateGuaranteeOrders->scene('refundHandle')->check($data);
        if ( false === $validate){
            return ['code' => CodeEnum::ERROR, 'msg' => $this->validateGuaranteeOrders->getError()];
        }

        $guaranteeOrdersWh = array('status' => 3,'id'=>$data['id'], 'merchant_user_id'=>$data['merchant_user_id']);
        $guaranteeOrders = $this->modelGuaranteeOrders->where($guaranteeOrdersWh)->find();
        if ( empty($guaranteeOrders)){
            return ['code' => CodeEnum::ERROR, 'msg' => '订单错误！'];
        }

        if (!in_array($data['status'], [4, 5])){
            return ['code' => CodeEnum::ERROR, 'msg' => '状态错误！'];
        }

        Db::startTrans();
        try {
            if ($data['status'] == 4){
                    $this->logicPayusercenter->usdtChange($guaranteeOrders->channel_user_id, 5, 1,
                        $guaranteeOrders->usdt_sum, '担保订单：'.  $guaranteeOrders->trade_no .'，退保成功');
                    $guaranteeOrders->refund_time = time();
            }
            $guaranteeOrders->merchant_refund_note = $data['merchant_refund_note'] ?? '';
            $guaranteeOrders->status = $data['status'];
            $guaranteeOrders->save();

            Db::commit();
            return ['code' => CodeEnum::SUCCESS, 'msg' =>'操作成功' ];
        }catch (Exception $ex){
            Db::rollback();
            return ['code' => CodeEnum::ERROR, 'msg' =>'未知错误！'];
        }


    }


}