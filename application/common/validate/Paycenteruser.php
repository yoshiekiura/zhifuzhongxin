<?php


namespace app\common\validate;


class Paycenteruser extends BaseValidate

{
    protected $rule = [
        'id'                     =>'require',
        'username'         => 'require|min:6|max:16|alphaNum|unique:pay_center_user',
        'password'         => 'require',
        'user_type'         => 'require',
    ];

    protected $message = [
        'id.require'                      => '标识不能为空',
        'username.require'         => '用户名不能为空',
        'username.unique'         => '用户名已存在',
        'username.minx'         => '用户名不能小于6位',
        'username.max'         => '用户名不能大于30位',
        'username.alphaNum'         => '用户名只能为字母数字下划线及其他符号',
        'password.require'         => '密码不能为空',
        'user_type.require'         => '用户类型不能为空',
    ];

    protected  $scene = [
        'add' => ['username', 'password', 'user_type'],
        'edit' => ['username.require', 'username.minx', 'username.max', 'user_type']
    ];
}