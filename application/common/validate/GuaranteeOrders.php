<?php


namespace app\common\validate;


class GuaranteeOrders extends BaseValidate
{

    protected $rule = [
        '__token__' => 'token',
        'id'                => 'require',
        'amount'      => 'require|number',
        'channel_user_id'  => 'require|integer',
    ];


    protected $message = [
        'amount.require'      => '金额不能为空！',
        'card_id.number'   =>  '金额错误！',
        'channel_user_id.require'   => '渠道信息错误！',
        'amount.integer'   => '渠道标识错误',
    ];

    protected $scene = [
        'addOrder' => ['amount','channel_user_id'],
    ];

}