<?php


namespace app\common\model;


class GuaranteeOrders extends BaseModel
{
    /** @var float|int  订单过期时间 */
    public $effective_time = 60*30;

    protected $append = [
        'pay_type_text',
        'status_text',
    ];


    public function getPayTypeTextAttr($value, $data)
    {
        $status = ['未支付', '余额支付', 'USDT支付'];

        return isset($status[$data['pay_type']]) ? $status[$data['pay_type']] : '';
    }

    public function getStatusTextAttr($value, $data)
    {
        $status = ['订单关闭', '待支付', '进行中', '申请退保', '退保成功', '拒绝退保'];

        return isset($status[$data['status']]) ? $status[$data['status']] : '';
    }
}