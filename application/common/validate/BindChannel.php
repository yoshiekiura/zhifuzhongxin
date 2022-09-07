<?php


namespace app\common\validate;


class BindChannel extends BaseValidate
{

    protected $rule = [

        'channel_id'  =>'require',
        'uid'         => 'require',
    ];

    protected $message = [
        'channel_id.require' =>'渠道标识错误！',
        'uid.require'        => '商户标识错误'
    ];

    protected  $scene = [
        'saveBind' => ['channel_id', 'uid'],
    ];

}