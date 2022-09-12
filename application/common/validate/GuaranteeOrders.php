<?php


namespace app\common\validate;


class GuaranteeOrders extends BaseValidate
{

    protected $rule = [
        '__token__' => 'token',
        'id'                => 'require',
        'amount'      => 'require|number',
        'channel_user_id'  => 'require|integer',
        'merchant_user_id'  => 'require|integer',
        'channel_refund_note' => 'cusMax:255',
        'merchant_refund_note' => 'cusMax:255',
        'status' => 'require'
    ];


    protected $message = [
        '__token__.require'   => '令牌过期请刷新重试！',
        'id.require' => '订单标识不能为空',
        'amount.require'      => '金额不能为空！',
        'card_id.number'   =>  '金额错误！',
        'channel_user_id.require'   => '渠道信息错误！',
        'merchant_user_id.require'   => '商户信息错误！',
        'channel_id.require'   => '支付渠道错误！',
        'amount.integer'   => '渠道标识错误',
        'channel_refund_note.cusMax' => '字符超过最大长度255！',
        'merchant_refund_note.cusMax' => '字符超过最大长度255！',
        'status.require' => '状态错误'

    ];

    protected $scene = [
        'addOrder' => ['amount','channel_user_id'],
        'balancePay' => ['__token__','id'],
        'applyRefund' => ['id', 'channel_refund_note'],
        'refundHandle' => ['id', 'merchant_user_id'],
    ];

}