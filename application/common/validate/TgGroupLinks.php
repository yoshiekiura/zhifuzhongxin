<?php


namespace app\common\validate;


class TgGroupLinks extends BaseValidate
{

    protected $rule = [
        'id'                => 'require',
        'link_address'      => 'require|url',
    ];


    protected $message = [
        'id.require' => '标识不能为空',
        'link_address.require'      => '链接地址不能为空',
        'link_address.url'      => '不是一个合法的链接地址',
    ];

    protected $scene = [
        'add' => ['link_address'],
        'edit' => ['id', 'link_address'],
    ];
}