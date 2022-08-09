<?php


namespace app\api\service\payment;


use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use think\Log;

class YinqianPay extends ApiPayment
{

    public function pay($order, $type)
    {

        $mchid = $this->config['pay_merchant'];
        $Md5key = $this->config['pay_secret'];
        $requestUrl = $this->config['pay_address'];
        $data = array(
            'mchid' => $mchid,
            'out_trade_no' => $order['trade_no'],
            'amount' => sprintf("%.2f", $order["amount"]),
            'channel' =>$type,
            'notify_url' => $this->config['notify_url'],
            'return_url' => $this->config['return_url'],
            'time_stamp' => date("Ymdhis"),
            'body' => '123',
        );
        ksort($data);
        $signData = "";
        foreach ($data as $key=>$value)
        {
            $signData = $signData.$key."=".$value;
            $signData = $signData . "&";
        }

        $signData = $signData."key=".$Md5key;
        $sign = md5($signData);

        $data['sign'] = $sign;

        $result = json_decode(self::curlPost($requestUrl, $data), true);

        if ($result['code'] != 0) {
            Log::error('Create YinqianPay API Error:' . $result['retMsg']);
            throw new OrderException([
                'msg' => 'Create YinqianPay API Error:' . $result['retMsg'],
                'errCode' => 200009
            ]);
        }
        return ['request_url' => $result['data']['request_url']];
    }

    /**
     * @return mixed
     * 回调
     */
    public function notify()
    {
        $notifyData = $_POST;
        Log::notice("YinqianPay notify data" . json_encode($notifyData));
        if ($notifyData['status'] == '2' ) {
            echo "success";
            $data['out_trade_no'] = $notifyData['out_trade_no'];
            return $data;
        }
        echo "error";
        Log::error('YinqianPay API Error:' . json_encode($notifyData));
    }

}
