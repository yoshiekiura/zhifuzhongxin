<?php


namespace app\api\service\payment;


use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use think\Log;

class DadaPay extends ApiPayment
{
    /**
     * 统一下单
     */
    public  function pay($order,$type)
    {
        $data = [
            'pay_memberid' => $this->config['pay_merchant'],
            'pay_orderid' => $order['trade_no'],
            'pay_applydate' => date('Y-m-d H:i:s'),
            'pay_bankcode' => $type,
            'pay_notifyurl' => $this->config['notify_url'],
            'pay_callbackurl' => $this->config['return_url'],
            'pay_amount' => $order['amount'],
        ];
        $data['pay_md5sign'] = $this->getSign($data, $this->config['pay_secret']);
        $data['pay_productname'] = 'goods';


        //$data['type'] = 'json';
        $data['request_post_url'] = $this->config['pay_address'];
        return ['request_url' => "http://47.242.45.116/pay.php?" . http_build_query($data)];


        //halt($data);

//      halt(self::curlPost($this->config['pay_address'], $data));
        //      $result =  json_decode(self::curlPost($this->config['pay_address'], $data ),true);
//      halt($result);
        //      if($result['status'] != '1' )
        //    {
        //      Log::error('Create MayifuPay API Error:'.$result['msg']);
        //    throw new OrderException([
        //         'msg'   => 'Create MayifuPay API Error:'.$result['msg'],
        //         'errCode'   => 200009
        //      ]);
        //  }
        //  return $data['payUrl'];

    }


    /**
     * @Note  生成签名
     * @param $secret   商户密钥
     * @param $data     参与签名的参数
     * @return string
     */
    private function getSign($data, $secret)
    {

        ksort($data);
        $md5str = "";
        foreach ($data as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        $sign =  strtoupper(md5($md5str . "key=" . $secret));
        return $sign;
    }


    /**
     * @return mixed
     * å›žè°ƒ
     */
    public function notify()
    {
        $notifyData =$_POST;
        Log::notice("DadaPay notify data1".json_encode($notifyData));
        if($notifyData['returncode'] == "00" ){
            echo 'OK';
            $data['out_trade_no'] = $notifyData['orderid'];
            return $data;
        }
        echo "error";
        Log::error('DadaPay API Error:'.json_encode($notifyData));
    }

}