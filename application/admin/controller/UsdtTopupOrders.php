<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;
use think\Request;

class UsdtTopupOrders extends BaseAdmin
{

    public function index()
    {
        return $this->fetch();
    }

    /**
     * 充值列表
     * @param Request $request
     */
    public function getTopupList(Request $request)
    {
        $where = [];
        $join = [
            ['pay_center_user u', 'u.id = a.uid']
        ];

        $field = 'a.*, u.username';

        $data =  $this->logicUsdtTopupOrders->getTopupList($where, $field, $join);

        $this->result(!$data->isEmpty()?
            [
                'code' => CodeEnum::SUCCESS,
                'msg'=> '',
                'count'=>$data->total(),
                'data'=>$data->items()
            ] : [
                'code' => CodeEnum::ERROR,
                'msg'=> '暂无数据',
                'count'=>$data->total(),
                'data'=>$data->items()
            ]);
    }
}