<?php

namespace app\api\service\payment;

use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use think\Log;

/*
 * 鼎盛支付支付平台第三方出码
 * Class 鼎盛支付
 * @package app\api\service\payment
 */

class HuitongPay extends ApiPayment
{

    public function pay($order, $type='2')
    {
        // 平台支付创建订单API地址，具体地址请联系代理
        $apiUrl = $this->config['pay_address'];
        $appkey = $this->config['pay_merchant'];
        $appsecret = $this->config['pay_secret'];

        // 选填，动态回调地址（系统将收款结果回调此地址）
        $return_url = $this->config['return_url'];

        // 选填，成功支付后跳转的地址（如果填写此值，系统在收款成功后跳转到此地址）
        $notify_url = $this->config['notify_url'];

        // 选填, 商户自定义的备注，回调通知的时候会原样返回
        $beizhu = 'goods';
        $number = $order['trade_no'];
        $money = sprintf("%.2f",$order["amount"]);

        // 计算安全校验码
        $key = md5($appkey.'&'.$number.'&'.$money.'&'.$type.'&'.$appsecret);

        //post参数
        $data = array(
            'appkey' => $appkey,
            'number' => $number,
            'money' => $money,
            'type' => $type,
            'key' => $key,
            'return_url' => $return_url,
            'notify_url' => $notify_url,
            'beizhu' => $beizhu,
        );
//    $ret = goPost($apiUrl, $data);

        $result =  json_decode(self::curlPost($apiUrl,$data),true);
//        halt($result);
        if($result['code'] != '1000' )
        {
            Log::error('Create HuitongPay API Error:'.$result['data']);
            throw new OrderException([
                'msg'   => 'Create HuitongPay API Error:'.$result['data'],
                'errCode'   => 200009
            ]);
        }
        return [
            'request_url' => $result['data']['pay_url']
        ];

    }


    /**
     * 微信扫码支付
     * @param $params
     */
/*    public function h5_vx($params)
    {
        //获取预下单
        $url = $this->pay($params, 1);
        return [
            'request_url' => $url,
        ];
    }*/



    /**
     * 微信扫码支付
     * @param $params
     */
   /* public function h5_zfb($params)
    {
        //获取预下单
        $url = $this->pay($params, 2);
        return [
            'request_url' => $url,
        ];
    }*/


    /**"
     * 支付宝H5
     * @param $params
     * @return string[]
     */
    /*public function test($params)
    {
        //获取预下单
        $url = $this->pay($params, 2);

    }*/


    /*
     * DsPay平台支付回调处理
     */
    public function notify()
    {

        $notifyData =$_POST;
        Log::notice("HuitongPay notify data1".json_encode($notifyData));
//        channel=alipay_qrcode_auto&tradeNo=456931005714923520&outTradeNo=115868704409556&money=200&realMoney=200&uid=456844661697282048&sign=CBE1BE600AFEEA6FB5DEA2CC9698F865
        if(isset($notifyData['status']) && $notifyData['status'] == 'success'  ){
            echo "1";
            $data['out_trade_no'] = $notifyData['number'];
            return $data;

        }
        echo "error";
        Log::error('HuitongPay API Error:'.json_encode($notifyData));


    }
}
