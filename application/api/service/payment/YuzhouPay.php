<?php

namespace app\api\service\payment;

use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use app\common\model\OwnpayOrder;
use think\Log;

/*
 *
 * Class Ocpay
 * @package app\api\service\payment
 */

class YuZhouPay extends ApiPayment
{

    /*
     *OC支付平台支付签名
     * @param $data  代签名参数
     * @param $key  秘钥
     * @return string
     */
    public static function getSign($data)
    {
        ksort($data);
        $md5str = http_build_query($data);
        $md5str = urldecode($md5str) . "1b533ced9613466a9eec094eccd29694";
        return strtolower(md5($md5str));
    }

    /*
    *  taobao _pay  统一下单
    *
    */
    public function pay($order, $payType = 'ALIHB')
    {
        $unified["mch_id"] = $this->config['pay_merchant'];
        $unified["pay_type"] = $payType;
        $unified["out_trade_no"] = $order['trade_no'];
        $unified["total_fee"] = sprintf("%.2f", $order['amount']) * 100;
        $unified["callback_url"] = urlencode($this->config['notify_url']);
        //        $key = 'f9668785d7d24b0385b769a52eef3d53';
        $key = $this->config['pay_secret'];
        $unified['sign'] = md5($unified["out_trade_no"] . $unified["total_fee"] . $unified["mch_id"] . $key);
//      $requestUrl = 'http://pay.meloqiao.com/lpay/pay/gateway?' . http_build_query($unified);
        $requestUrl = $this->config['pay_address']. '?' . http_build_query($unified);
        return ['request_url' => $requestUrl];
    }




    /**
     * @return mixed
     * 回调
     */
    public function notify()
    {
        Log::notice("YuZhouPay notify data" . json_encode($_REQUEST));
        if (1) {
            echo "success";
            $data['out_trade_no'] = $_REQUEST['out_trade_no'];
            return $data;
        }
    }


}