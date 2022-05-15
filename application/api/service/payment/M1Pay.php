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

class M1Pay extends ApiPayment
{
    /**
     * 统一下单
     */
    public function pay($order,$type='alipaySolidCode'){


        $data = [
            //'return_type'   =>  'PC',
            'appid'   =>  $this->config['pay_merchant'],
            'pay_type'   =>  $type,
            'amount'   =>  sprintf("%.2f",$order["amount"]),
            'callback_url'   =>  $this->config['notify_url'],
            'success_url'   =>  $this->config['return_url'],
            'error_url'   =>  'http://www.baidu.com',
            //   'out_uid'   =>  '1',
            'out_trade_no'   =>  $order['trade_no'],
            'extend'   =>  '{username:0}',
        ];

        //$merkey = 'HvzlpLIIR4ecRgFF2XeTEwruR5izEUAW';
        $merkey =$this->config['pay_secret'];
        $url = $this->config['pay_address'];
        $data['sign'] = $this->getSign($merkey,$data);
//        $data['request_post_url']= $url;
//        return "http://caishen.sviptb.com/pay.php?".htmlspecialchars(http_build_query($data));


        $result =  json_decode(self::curlPost($url,$data,null,15),true);
        if($result['code'] != '200' )
        {
            Log::error('Create XiongmaoV2Pay API Error:'.$result['msg']);
            throw new OrderException([
                'msg'   => 'Create XiongmaoV2Pay API Error:'.$result['msg'],
                'errCode'   => 200009
            ]);
        }
        return [
            'request_url' => $result['url']
        ];
    }


    /**
     * 查询接口
     */
    public function query($notifyData){
        $url = 'http://api.8798996.cn/index/getorder';

        $key = 'n6x1RXajkamHNuFSqY4XRaiOBd9PXzqK';
        $data=array(
            'out_trade_no'  =>  $notifyData['out_trade_no'],
            'appid' => '1061331',
        );
        $data['sign'] = $this->getSign($key,$data);

        $result =  json_decode(self::curlPost($url,$data),true);
        Log::notice('query XiongmaoV2Pay  API notice:'.json_encode($result));
        if(  $result['code'] != '200' ){
            Log::error('query XiongmaoV2Pay  API Error:'.$result['msg']);
            return false;
        }

        if(count($result['data']) < 1 ){
            return false;
        }

        if($result['data'][0]['status'] != '4' ){
            return false;
        }
        return true;
    }


    /**
     * @Note  生成签名
     * @param $secret   商户密钥
     * @param $data     参与签名的参数
     * @return string
     */
    private function getSign($secret, $data)
    {

        // 去空
        $data = array_filter($data);

        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);

        //签名步骤二：在string后加入mch_key
        $string_sign_temp = $string_a . "&key=" . $secret;
//;echo $string_sign_temp;
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
        Log::notice("FuwaPay notify data".json_encode($notifyData));
        if(1 ){
            if(1) {
                echo "success";
                $data['out_trade_no'] = $notifyData['out_trade_no'];
                return $data;
            }
        }
        echo "error";
        Log::error('FuwaPay API Error:'.json_encode($notifyData));
    }
}
