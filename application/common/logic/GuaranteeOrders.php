<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
use think\Db;

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

        if (empty($channelUser) or empty($merchantUser)){
            return ['code' => CodeEnum::ERROR, 'msg' => '用户不存在!'];
        }

        if ($channelUser['user_type'] !=1){
            return ['code' => CodeEnum::ERROR, 'msg' => '渠道用户类型错误！'];
        }

        $guaranteeOrders = $this->modelGuaranteeOrders->where([
            'merchant_user_id' => $merchantUser['id'],
            'channel_user_id' => $data['channel_user_id']
        ])->find();

        if ($guaranteeOrders && $guaranteeOrders->status != 0){
            return ['code' => CodeEnum::ERROR, 'msg' => ($guaranteeOrders->status == 1) ? '已有订单进行中！': '已有该渠道担保订单！'];
        }

        $usdtRate =  $this->modelConfig->where('name', 'usdt_rate')->value('value');
        if (!$usdtRate){
            return ['code' => CodeEnum::ERROR, 'msg' => '后台未设置usdt汇率!'];
        }

        $usdtSum =  $this->getUsdtSum($data['amount'], $usdtRate);

        if ($usdtSum <= 0){
            return ['code' => CodeEnum::ERROR, 'msg' => '金额错误，请稍后在试！'];
        }

        $order = array(
            'trade_no'  => create_order_no(),
            'merchant_user_id' =>  $data['merchant_user_id'],
            'channel_user_id'  => $data['channel_user_id'],
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
}