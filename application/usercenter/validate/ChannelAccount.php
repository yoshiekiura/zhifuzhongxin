<?php


namespace app\usercenter\validate;


use think\Validate;

class ChannelAccount extends Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule =   [
        'channel_id' => 'require|integer',
        'name'  => 'require',
        'secret_key' => 'require',

    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message  =   [
        'channel_id.require' => '渠道标识不能为空',
        'channel_id.integer' => '渠道标识类型错误',
        'name.require' => '账号名称不能为空',
        'secret_key.require'    => '密钥不能为空',
    ];

    protected $scene = [
        'add' => ['channel_id', 'name', 'secret_key'],
    ];
}