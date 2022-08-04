<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaohei
 * Date: 2020/2/14
 * Time: 20:15
 */

namespace app\api\service\payment;


use app\api\service\ApiPayment;
use app\common\library\exception\OrderException;
use think\Log;

/**花花富支付
 * Class HhfPay
 * @package app\api\service\payment
 */
class TaiyangPay extends ApiPayment
{
    protected $publicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC26/sS6t74voQ8pcaHLLmkCB9rk8EeAYKqcCsZ/tYzdnN21If/nwJQWD7oKqsvQQ6CT1Mg1YeqmUEgpuMlmEHsAukzceMX5UHZQgY6HbPZ8VEjTPrGWWZxL2SXhPFqURqc5qfEjsjiomi2EjloMU6xeg8RYf8By+AJK3qTmHTqowIDAQAB';
//商户私钥
    protected $privateKey ='MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBANa3DPxTh5WrjAse4zXP+PNpqrLy9W+icc6Bsl/k/yIcwH5e7EDl+Z5m72STxw+PD1Sm94qrwNMoTqvuksV0O4D8eiT3D3v6SgpkuS+cUviLfYzVSMxj6adY2Spf+vSHOVy2sKlbKx1i/LoVjOw/v+WgrG2r1vwiLCIYHSZ4uQEPAgMBAAECgYAbzVwLGC3IPYxGrFszTpinvBj0Tu1k5R3jZXvZWueGXT63nkbtKFooHqAE3/W4mAeeKHiJJjAzp1Z4gM3Ub3Z8wk/gasCx/XEMLn4aypOfFjJcRTT6GfqPMw4IxOFG4NiF94xdb33kcC6uX6nMTRS4Rlp+x/hhzaZOxZ6MPul04QJBAP0JHeLNlrKeiUp5+SfeBzsqrN19EZvmFU1kSnY9nYTgbUfy9FGySRtjZjPURB1ftZmYfiI3LoyqfSLDs7tfJUcCQQDZOwGR1zOXGXIIYRUCSomanMVQJgLaNw+NcMb3nX2PL8M0ghEcrb6teG2xz3bf6p1hzEjCLt0HWsawC57cT8n5AkA3SfatyB5ViS6Wh3BZtbn+w6RiASIH3o5pCrD6hRwWHLPENOINt9chlOaQDKGViYQ0u41UDJqvQdF19y0ek/uhAkA/rjiLlFafWOpA4pTSEx+7n3GISVxUtAdvIzxwok6IhyvmXKq/iX94QvGFSphCk/iHDufVZP+OhGHygbWUSB+hAkEAx8zFl88nmAmxq4Ir80VLnmrVdZIPlo34aDajr8u16fQzwXSEi7+qM8Rkz692Uz4FqwBsFDGLcD//I0yAH/zDjg==';


    protected $md5Key = 'lyNIbKaOEUZQfkpADRevuTdqCPgLFBWr';


    /**
     * 生成随机字符串
     * @param string $type
     * @param int $len
     * @return false|string
     */
    public static function buildNonceStr($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'alpha':
            case 'alnum':
            case 'numeric':
            case 'nozero':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt':
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
        }
    }


    /**
     * 生成签名
     * @param $data
     * @param $md5Key
     * @param $privateKey
     * @return mixed
     */
    public function getSign($data, $md5Key, $privateKey)
    {
        ksort($data);
        reset($data);
        $arg = '';
        foreach ($data as $key => $val) {
            //空值不参与签名
            if ($val == '' || $key == 'sign') {
                continue;
            }
            $arg .= ($key . '=' . $val . '&');
        }
        $arg = $arg . 'key=' . $md5Key;

        //签名数据转换为大写
        $sig_data = strtoupper(md5($arg));
        //私钥签名
        return $this->sign($sig_data);
    }


    /**
     * 设置私钥
     * @return bool
     */
    private function setupPrivKey()
    {
        if (is_resource($this->privateKey)) {
            return true;
        }
        $pem = chunk_split($this->privateKey, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
        $this->privateKey = openssl_pkey_get_private($pem);
        return true;
    }


    /**
     * 构造签名
     * @param string $dataString 被签名数据
     * @return string
     */
    public function sign($dataString)
    {
        $this->setupPrivKey();
        $signature = false;
        openssl_sign($dataString, $signature, $this->privateKey,OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }





    /**
     * 统一下单
     */
    public function pay($order, $type = 'Wechat')
    {
        //请求格式
        $merId = '20220197';
        $merId = $this->config['pay_merchant'];
        $data  = [
            'merId'     => $merId,               //商户号
            'orderId'   => $order['trade_no'],            //订单号，值允许英文数字
            'orderAmt'  => sprintf("%.2f", $order["amount"]),              //订单金额,单位元保留两位小数
            'channel'   => $type,            //支付通道编码
            'desc'      => 'goods',           //简单描述，只允许英文数字 最大64
            'attch'     => 'goods',             //附加信息,原样返回
            'smstyle'   => '1',               //用于扫码模式（sm），仅带sm接口可用，默认0返回扫码图片，为1则返回扫码跳转地址。
            'userId'    => '',                 //用于识别用户绑卡信息，仅快捷接口可用。
            'ip'        => request()->ip(),          //用户的ip地址必传，风控需要
            'notifyUrl' => $this->config['notify_url'],   //异步返回地址
            'returnUrl' => $this->config['return_url'],     //同步返回地址
            'nonceStr'  => $this->buildNonceStr()   //随机字符串不超过32位
        ];
        //私钥签名
        $md5Key = $this->config['pay_secret'];
        $data['sign'] = $this->getSign($data, $md5Key, $this->privateKey);
        $url          = 'http://a.sunfu.store/api/pay';
        $url          = $this->config['pay_address'];
        $result       = json_decode(self::curlPost($url, $data), true);
        if ($result['code'] != '1') {
            Log::error('Create TaiyangPay API Error:' . $result['msg']);
            throw new OrderException([
                'msg'     => 'Create TaiyangPay API Error:' . $result['msg'],
                'errCode' => 200009
            ]);
        }
        return ['request_url' => $result['data']['payurl']];
    }


    /**
     * 微信扫码支付
     * @param $params
     */
    public function h5_vx1($params)
    {
        //获取预下单
        $url = $this->pay($params, 'wxwap');
        return [
            'request_url' => $url,
        ];
    }

    public function h5_zfb1($params)
    {
        //获取预下单
        $url = $this->pay($params, 'zfbwap');
        return [
            'request_url' => $url,
        ];
    }




    /**
     * @param $params
     * @return array
     *  test
     */
    public function test1($params)
    {
        //获取预下单
        halt(112);
        $url = $this->pay($params, 'wxwap');
        return [
            'request_url' => $url,
        ];
    }

    public function wxios1($params)
    {
        //获取预下单
        $url = $this->pay($params, 'wxyk');
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
        $input = file_get_contents("php://input");
        Log::notice("HhfPay notify data" . $input);
        $notifyData = $_POST;
        if ($notifyData['status'] ==1) {
            echo "success";
            $data['out_trade_no'] = $notifyData['orderId'];
            return $data;
        }
        echo "error";
        Log::error('HhfPay API Error:' . $input);
    }
}
