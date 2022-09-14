<?php


namespace app\common\model;


class CenterUsdtBalanceChange extends BaseModel
{

    protected $append = [
        'change_category_text'
    ];

    public function getChangeCategoryTextAttr($value, $data)
    {

        $type = ['USDT充值', 'USDT提现', '管理员账变', '担保订单支付', '担保订单退款'];

        $val = '';
        if ( isset($data['change_category'])  && isset($type[$data['change_category']-1])){
            $val =  $type[$data['change_category']-1];
        }
        return  $val;
    }
}