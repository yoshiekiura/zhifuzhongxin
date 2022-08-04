<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaohei
 * Date: 2020/5/4
 * Time: 15:00
 */

namespace app\api\service\payment;


use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use think\Log;

class HzhifuPay extends ApiPayment
{
    /**
     * 统一下单
     */
    public function pay($order,$type='alipaySolidCode'){


        $data = [
            'appid'   =>  $type,
            'orderno'   => $order['trade_no'] ,
            'total_amount'   =>  sprintf("%.2f",$order["amount"]),
        ];
        $result =  json_decode(self::curlPost($this->config['pay_address'],$data),true);
//      halt($result);
        if($result['status'] != 'success' )
        {
            Log::error('Create HzhifuPay API Error:'.$result['data']);
            throw new OrderException([
                'msg'   => 'Create HzhifuPay API Error:'.$result['data'],
                'errCode'   => 200009
            ]);
        }
        return ['request_url' => $result['data']['payinfo']['deepLink']];
    }

    /**
     * 回调
     */
    public function notify()
    {
        $notifyData = $_POST;
        Log::notice("HzhifuPAY notify data".json_encode($notifyData));
        if(isset($notifyData['orderno'])){

            echo "success";
            $data['out_trade_no'] = $notifyData['orderno'];
            return $data;

        }
        echo "error";
        Log::error('HzifuPay API Error:'.json_encode($notifyData));
    }
}
