<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaohei
 * Date: 2020/3/11
 * Time: 23:20
 */

namespace app\api\service\payment;


use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use GuzzleHttp\Client;
use think\Log;

class XiaoxiaobangPay extends ApiPayment
{
    /**
     * 统一下单
     */
    public function pay($order,$type){
        $url = $this->config['pay_address'];
        $secret = $this->config['pay_secret'];
        $data  = [
            'amount' => sprintf("%.2f",$order["amount"]),
            'partnerid' => $this->config['pay_merchant'],
            'notifyUrl' => $this->config['notify_url'],
            'out_trade_no' => $order['trade_no'],
            'payType' => $type,
            'returnUrl' => $this->config['return_url'],
            'version' => '1.0',
            'format' => 'json'
        ];



        $data['sign'] = $this->getSign($data, $secret);
//        $result =  json_decode(self::curlPost($url,$data,null,15),true);
        $client =  new Client();
        $result = $client->post($url, ['form_params' =>$data] );
        $result = json_decode($result->getBody()->getContents(), true);
//var_dump($data,$result);die();

        if($result['code'] != 200)
        {
            Log::error('Create BaofuPay API Error:'.$result['msg']);
            throw new OrderException([
                'msg'   => 'Create BaofuPay API Error:'.$result['msg'],
                'errCode'   => 200009
            ]);
        }

        return [
            'request_url' => $result['data']['url']
        ];

    }


    /**
     * 生成签名
     */
    private function getSign($native,$appSecret) {
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $appSecret));
        return $sign;
    }



    /**
     * @param $params
     * 支付宝
     */
/*    public function h5_vx($params)
    {
        //获取预下单
        $url = $this->pay($params,'wechat');
        return [
            'request_url' => $url,
        ];
    }*/


    /**
     * @param $params
     * @return array
     *  test
     */
/*    public function test($params){
        //获取预下单
        $url = $this->pay($params, 'wechat');
        return [
            'request_url' => $url,
        ];
    }*/





    /**
     * @return mixed
     * 回调
     */
    public function notify()
    {

        $notifyData = $_POST;
        Log::notice("BaofuPay notify data1".json_encode($notifyData));
        echo "success";
        $data['out_trade_no'] = $notifyData['out_trade_no'];
        return $data;

    }

}