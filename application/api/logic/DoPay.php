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

namespace app\api\logic;
use app\api\service\ApiPayment;
use app\api\service\payment\Qpay;
use app\api\service\payment\Rongjupay;
use app\common\library\exception\OrderException;
use app\common\logic\Config;
use think\Exception;
use think\Log;
use think\Request;

/**
 * 支付处理类  （优化方案：提出单个支付类  抽象类对象处理方法 便于管理）
 *
 * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
 *
 */
class DoPay extends BaseApi
{
    /**
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $orderNo
     *
     * @return mixed
     * @throws \Exception
     */
    public function pay($orderNo)
    {

        //todo
        //检查支付状态
        $order = $this->modelOrders->checkOrderValid($orderNo);
        //创建支付预订单
        return $this->prePayOrder($order);
    }

    /**
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $orderNo
     *
     * @return mixed
     * @throws \Exception
     */
    public function dafiupay($orderNo)
    {
        //检查支付状态
        $order = $this->modelDaifuOrders->checkOrderValid($orderNo);

        //创建支付预订单
        return $this->preDaifuPayOrder($order);
    }

    /**
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $order
     *
     * @return mixed
     * @throws OrderException
     */
    private function preDaifuPayOrder($order){
        //渠道和参数获取
        $appChannel = $this->logicPay->getDaifuAllowedAccount($order);
        if (isset($appChannel['errorCode'])){
            Log::error($appChannel['msg']);
            throw new OrderException($appChannel);
        }

        //取出数据
        list($payment,$action,$config) = array_values($appChannel);


        //带付分发
        $result = ApiPayment::$payment($config)->$action($order->id, $order->amount, 1, $order->bank_number, $order->bank_owner);
        return $result;
    }

    /**
     *
     * @author 勇敢的小笨羊 <brianwaring98@gmail.com>
     *
     * @param $order
     *
     * @return mixed
     * @throws OrderException
     */
    private function prePayOrder($order){

        //如果是test编码强制匹配test 渠道
        if ($order['channel'] == 'test'){
            $result = ApiPayment::TestPay()->pay($order, 0);
        }else {
            $accountChannel = explode(':', $order['body']);
            //如果uid 为100001，则是拉单测试，直接获取body字段（传入的是账号ID），获取对应的渠道信息
            if ($order['uid'] == 100001) {
                $channelAccount = $this->modelPayCenterChannelAccount
                    ->alias('a')
                    ->join('pay_channel c', 'c.id = a.channel_id', 'left')
                    ->join('channel_template t', 't.id = template_id')
                    ->where(['a.id' => $accountChannel[0]])
                    ->field('a.secret_key as pay_secret, a.appid as pay_merchant, c.id, c.name, c.timeslot, c.remarks, c.notify_url, c.return_url,  c.template_id, c.pay_center_uid, c.pay_address,t.class_name')
                    ->find();

                if (!$channelAccount){
                    throw new OrderException('渠道错误！');
                }

                $payment = $channelAccount['class_name'];
                $channelAccount['channel_code_value'] = $accountChannel[1];
                $channelAccount['notify_url']  = Request::instance()->domain() . "/api/notify/notify/channel/". $channelAccount['notify_url'];
                $channelAccount['return_url']  = Request::instance()->domain() . "/api/notify/notify/channel/". $channelAccount['return_url'];
                $config=  $channelAccount->toArray();
                //添加订单支付通道ID
                $this->logicOrders->setOrderValue(['trade_no' => $order['trade_no']], 'cnl_id', $channelAccount['id']);
//                halt($channelAccount);
            }else{
                //渠道和参数获取
                $appChannel = $this->logicPay->getAllowedAccount($order);

                if (isset($appChannel['errorCode'])) {
                    Log::error($appChannel['msg']);
                    throw new OrderException($appChannel);
                }
                //取出数据
                list($payment, $action, $config) = array_values($appChannel);
            }



            $result = ApiPayment::$payment($config)->pay($order, $config['channel_code_value']);
        }


        //获取当前毫秒时间
        $start_time = msectime();

        //支付分发
        //$result = ApiPayment::$payment($config)->$action($order);





        $elapsed_time = msectime() - $start_time;

        $request_elapsed_time = $elapsed_time/1000;

        if($result && $request_elapsed_time == '0' ){
            $request_elapsed_time = '0.001';
        }
        if (isset($config['pay_center_uid'])){
            $channel_agent_uid = $this->logicPayusercenter->getUserInfo(['id'=>$config['pay_center_uid']])['pid'] ?? 0;
        }
        $this->modelOrders->setInfo(['id'=>$order['id'],'request_elapsed_time'=>$request_elapsed_time, 'channel_agent_uid' => $channel_agent_uid ?? 0]);


        return $result;
    }
}