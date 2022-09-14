<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;
use think\Request;

class GuaranteeOrders extends BaseAdmin
{

    /**
     * 担保列表
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 获取担保列表
     * @param Request $request
     */
    public function getGuaranteeList(Request $request)
    {
        $where = [];

        $merchant_username = $request->param('merchant_username');
        $channel_username = $request->param('channel_username');
        $channel_name = $request->param('channel_name');
        $status = $request->param('status');

        $merchant_username && $where['u1.username'] = ['like', '%'. $merchant_username .'%'];
        $channel_username && $where['u2.username'] = ['like', '%'. $channel_username .'%'];
        $channel_name && $where['c.name'] = ['like', '%'. $channel_name .'%'];
        $status > 0 && $where['a.status'] = $status;


        $join = [
            ['pay_center_user u1', 'u1.id = a.merchant_user_id'],
            ['pay_center_user u2', 'u2.id = a.channel_user_id'],
            ['pay_channel c',  'c.id = a.channel_id']
        ];

        $field = 'a.*, u1.username as merchant_username, u2.username as channel_username, c.name as channel_name';

        $data = $this->logicGuaranteeOrders->getGuaranteeList($where, $join, $field);

        $this->result(!$data->isEmpty()?
            [
                'code' => CodeEnum::SUCCESS,
                'msg'=> '',
                'count'=>$data->count(),
                'data'=>$data->items()
            ] : [
                'code' => CodeEnum::ERROR,
                'msg'=> '暂无数据',
                'count'=>$data->count(),
                'data'=>$data->items()
            ]);
    }

    /**
     * 担保订单详情
     */
    public function details(Request  $request)
    {
        $id = $request->param('id');
        $join = [
            ['pay_center_user u1', 'u1.id = a.merchant_user_id'],
            ['pay_center_user u2', 'u2.id = a.channel_user_id'],
            ['pay_channel c',  'c.id = a.channel_id']
        ];
        $field = 'a.*, u1.username as merchant_username, u2.username as channel_username, c.name  as channel_name';
        $this->assign('order',  $this->logicGuaranteeOrders->getOrderInfo(['a.id' => $id], $join, $field));
        return $this->fetch();
    }

    /**
     * 后台管理员手动成功订单
     * @param Request $request
     * @return mixed
     */
    public function successOrder(Request $request)
    {
        if ($request->isAjax()) $this->result($this->logicGuaranteeOrders->successOrder($request->param()));
        return $this->fetch();
    }
}