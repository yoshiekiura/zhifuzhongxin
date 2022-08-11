<?php


namespace app\common\model;


class PayCenterUser extends BaseModel
{
    protected $autoWriteTimestamp =false;

    protected $append = [
        'user_type_text'
    ];


    public function getUserTypeTextAttr($value,$data)
    {
        $status = [ 1=>'渠道用户', 2=>'商户用户', 3=>'三方用户', 4=>'代理用户'];
        return $status[$data['user_type']] ?? '';
    }
}