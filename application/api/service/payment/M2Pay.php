<?php


namespace app\api\service\payment;


use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use think\Log;

class M2Pay extends ApiPayment
{

    /**
     * 统一下单
     */
    public function pay($order, $type)
    {
        $data = [
            'mch_id'     => $this->config['pay_merchant'],
            'pass_code'  => $type,
            'subject' => 'goods',
            'out_trade_no' => $order['trade_no'],
            'money' => $order['amount'],
            'client_ip' => '127.0.0.1',
            'notify_url' => $this->config['notify_url'],
            'timestamp' => date('Y-m-d H:i:s', time())
        ];
        $data['sign'] = $this->getSign($data, $this->config['pay_secret']);

        $result = json_decode(self::curlPost($this->config['pay_address'], json_encode($data)), true);

        if ($result['code'] != 0){
            Log::error('Create JiuyuePay API Error:'.$result['msg']);
            throw new OrderException([
                'msg'   => 'Create JiuyuePay API Error:'.$result['msg'],
                'errCode'   => 200009
            ]);
        }

        return $result['url'];
    }

    /**
     * 签名加密
     * @param $parameters
     * @param $sign_key
     */
    function getSign($data, $sign_key) {
        //去空
        $data = array_filter($data);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);
        //签名步骤二：在string后加入KEY
        $string_sign_temp = $string_a . "&key=" . $sign_key;
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result = strtoupper($sign);
        return $result;

    }

    /**
     * @return mixed
     * 回调
     */
    public function notify()
    {
        $notifyData = $_POST;
        Log::notice("JiuyuePay notify data".json_encode($notifyData));
        if($notifyData['callbacks'] == "CODE_SUCCESS" ){
            echo "SUCCESS";
            $data['out_trade_no'] = $notifyData['out_trade_no'];
            return $data;
        }
        echo "ERROR";
        Log::error('JiuyuePay API Error:'.json_encode($notifyData));
    }
}
