<?php


namespace app\api\service\payment;


use app\api\service\ApiPayment;

class TestPay extends ApiPayment
{

    public function pay($order, $type){

        return [
            'request_url' => 'https://www.baidu.com',
        ];
    }

}