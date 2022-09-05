<?php


namespace app\common\validate;


class PayCenterUserAccount extends BaseValidate
{

    protected $rule = [
        'pay_center_uid'                     =>'require',
        'user_id'         => 'require',
        'channel_id'         => 'require',
        'bind_id'         => 'require',
        'name'         => 'require',
        'pay_merchant'         => 'require',
        'pay_secret'         => 'require',
    ];

    protected $message = [
        'pay_center_uid.require'                      => '所属支付中心用户不能为空',
        'user_id.require'         => '商户标识不能为空',
        'channel_id.require'         => '渠道标识不能为空',
        'bind_id.require'         => '绑定标识不能为空',
        'name.require'         => '账号名称不能为空',
        'pay_merchant.require'         => '商户号不能为空',
        'pay_secret.require'         => '支付密钥不能为空',
    ];

    protected  $scene = [
        'add' => ['pay_center_uid', 'user_id', 'channel_id', 'bind_id', 'name', 'pay_merchant', 'pay_secret'],
    ];

}