<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaohei
 * Date: 2020/6/27
 * Time: 23:02
 */

namespace app\api\service\payment;


use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use think\Log;

class KuaikuaifuPay extends ApiPayment
{
    /**
     * 统一下单
     */
    public function pay($order,$type='951'){

        $pay_memberid = "10010";   //商户ID
        $pay_orderid = $order['trade_no'];    //订单号
        $pay_amount = sprintf("%.2f",$order["amount"]);    //交易金额
        $pay_applydate = date("Y-m-d H:i:s");  //订单时间
        $pay_notifyurl = $this->config['notify_url'];   //服务端返回地址
        $pay_callbackurl = $this->config['return_url'];  //页面跳转返回地址
        $Md5key = "u3vhj5mwo8ec410g31818g5y63l05rmf";   //密钥
        $tjurl = "http://jin.canaskme.com/Pay_Index.html";   //提交地址
        $pay_bankcode = $type;   //银行编码
        //扫码
        $native = array(
            "pay_memberid" => $pay_memberid,
            "pay_orderid" => $pay_orderid,
            "pay_amount" => $pay_amount,
            "pay_applydate" => $pay_applydate,
            "pay_bankcode" => $pay_bankcode,
            "pay_notifyurl" => $pay_notifyurl,
            "pay_callbackurl" => $pay_callbackurl,
        );
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        $native["pay_md5sign"] = $sign;
        $native['pay_attach'] = "123";
        $native['pay_productname'] ='goods';
//      halt($native);

        $native['request_post_url'] =$tjurl;
        return [
            'request_url' => "http://97.74.89.67/pay.php?".(http_build_query($native))
        ];
    }

    public function getSign($native,$key){
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        return strtoupper(md5($md5str . "key=" . $key));
    }


    /**
     * @param $params
     * 支付宝
     */
    public function guma_yhk($params)
    {
        //获取预下单
        $url = $this->pay($params,'996');
        return [
            'request_url' => $url,
        ];
    }



    /**
     * @param $params
     * 支付宝
     */
    public function wap_zfb($params)
    {
        //获取预下单
        $url = $this->pay($params,'955');
        return [
            'request_url' => $url,
        ];
    }
    public function h5_vx($params)
    {
        //获取预下单
        $url = $this->pay($params,'951');
        return [
            'request_url' => $url,
        ];
    }

    public function sm_vx($params)
    {
        //获取预下单
        $url = $this->pay($params,'951');
        return [
            'request_url' => $url,
        ];
    }

    public function zfbys($params){
        //获取预下单
        $url = $this->pay($params, '955');
        return [
            'request_url' => $url,
        ];
    }

    public function h5_zfb($params){
        //获取预下单
        $url = $this->pay($params, '955');
        return [
            'request_url' => $url,
        ];
    }


    /**
     * @param $params
     * @return array
     *  test
     */
    public function test($params){
        //获取预下单
        $url = $this->pay($params);
        return [
            'request_url' => $url,
        ];
    }
    /**
     * @return mixed
     * 回调
     */
    public function notify()
    {
        $notifyData = $_POST;
        Log::notice("KuaikuaiPay notify data1".json_encode($notifyData));
        if($notifyData['returncode'] == '00' ){
            echo "OK";
            $data['out_trade_no'] = $notifyData['orderid'];
            return $data;
        }
        Log::error("KuaikuaiPay error data".json_encode($notifyData));
    }
}

