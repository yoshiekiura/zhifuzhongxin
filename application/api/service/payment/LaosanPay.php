<?php /*IT JUST THINK ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2018 http://www.iredcap.cn All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed ( https://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: Brian Waring <BrianWaring98@gmail.com>
 *  +----------------------------------------------------------------------
 */

namespace app\api\service\payment;

use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;

use app\common\logic\Orders;
use think\Log;

class LaosanPay extends ApiPayment
{

    /**
     * 统一支付
     */
    public function pay($order, $type)
    {
        $data = array(
            'amount' =>   sprintf("%.2f", $order["amount"]),
            'ip'          =>   get_userip(),
            'merchantId' => $this->config['pay_merchant'],
            'notifyUrl'     =>  $this->config['notify_url'],
            'outTradeNo' => $order['trade_no'],
            'passageCode' => $type,
            'subject' => 'goods',
            'timestamp' => date('Y-m-d H:i:s', time())
        );

        $data['sign'] = $this->getSign($data, $this->config['pay_secret']);

        $result = json_decode(self::curlPost($this->config['pay_address'], $data), true);

        if ($result['code'] != 200){
            Log::error('Create LaosanPay API Error:' . $result['message']);
            throw new OrderException([
                'msg' => 'Create LaosanPay API Error:' . $result['message'],
                'errCode' => 200009
            ]);
        }

        return ['request_url' => $result['data']['payUrl']];

    }

    /*
     * 本支付平台签名方式
     * @param $param
     * @return string
     */
    public function getSign($param, $secret)
    {
        ksort($param);
        $md5str = "";
        foreach ($param as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        $md5str = rtrim($md5str, '&');

        return strtolower(md5($md5str . $secret));
    }

    /*
     *本支付服务回调地址
     * @return mixed
     */
    public function notify()
    {
        $notifyData = $_POST;
        Log::notice("LaosanPay notify data" . json_encode($notifyData));
        if ($notifyData['status'] == '2') {
            echo "success";
            $data['out_trade_no'] = $notifyData['outTradeNo'];
            return $data;
        }
        echo "error";
        Log::error('LaosanPay API Error:' . json_encode($notifyData));
    }

}
