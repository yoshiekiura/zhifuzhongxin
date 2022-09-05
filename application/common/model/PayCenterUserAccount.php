<?php


namespace app\common\model;


class PayCenterUserAccount extends BaseModel
{

    public $append = [
        'status_text'
    ];

    public function getStatusTextAttr($value, $data)
    {
        $status = [-1=>'删除',0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
}