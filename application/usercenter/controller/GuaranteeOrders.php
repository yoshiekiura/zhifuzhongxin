<?php


namespace app\usercenter\controller;


use think\Request;

class GuaranteeOrders extends Base
{

    /**
     * 担保列表
     */
    public function index(Request $request)
    {
        $trade_no =  $request->param('trade_no');
        $status = $request->param('status');
        $where['cu.user_type'] = $this->user['user_type'];
        $trade_no && $where['a.trade_no'] = $trade_no;
        $status && $where['a.status'] = $status;
        switch ($this->user['user_type']){
            case 1:
                $where['a.channel_user_id'] = $this->user['id'];
                $join = [
                    ['pay_center_user cu', 'cu.id = a.channel_user_id'],
                    ['pay_center_user cu1', 'cu1.id = a.merchant_user_id']
                ];
                break;
            case 2:
                $where['a.merchant_user_id'] = $this->user['id'];
                $join = [
                    ['pay_center_user cu', 'cu.id = a.merchant_user_id'],
                    ['pay_center_user cu1', 'cu1.id = a.channel_user_id']
                ];
                break;
            default:
                $this->error('数据错误');
                break;
        }

        $field = 'a.*, cu.username as my_username, cu.user_type, cu1.username as rest_username';

        $lists = $this->logicGuaranteeOrders->getGuaranteeList($where, $join, $field, 'a.create_time desc');

        $this->assign('lists', $lists);

        return $this->fetch();

    }

    /**
     * 添加担保订单
     */
    public function addOrder(Request  $request)
    {
        return $this->result($this->logicGuaranteeOrders->addOrder(  array_merge($request->param(),  ['merchant_user_id' => $this->user['id']]) ));
    }

    /**
     * 余额支付
     */
    public function balancePay(Request $request)
    {
        $data = array_merge(['channel_user_id' => $this->user['id']], $request->param());
        $this->result($this->logicGuaranteeOrders->balancePay($data));
    }

    /**
     * usdt在线支付
     */
    public function onlinePay(Request $request)
    {
        $data = array_merge(['channel_user_id' => $this->user['id']], $request->param());
        $this->result($this->logicGuaranteeOrders->onlinePay($data));
    }

    /**
     * 申请退保
     */
    public function applyRefund(Request $request)
    {
        $data = array_merge(['channel_user_id' => $this->user['id']], $request->param());
        $this->result($this->logicGuaranteeOrders->applyRefund($data));
    }

    /**
     * 订单详情
     * @param Request $request
     */
    public function orderDetails(Request $request)
    {
        $id = $request->param('id');
        $where = array(
            'a.id' => $id,
//            'a.merchant_user_id' => $this->user['id']
        );
        $field = 'a.*, c.name as channel_name, u.username as channel_username,tg.link_address';
        $join = [
            ['cm_pay_channel c', 'c.id = a.channel_id'],
            ['cm_pay_center_user u', 'u.id = c.pay_center_uid'],
            ['cm_tg_group_links tg', 'tg.id = a.link_id and tg.allocation_type = 1', 'left']
        ];
        $order = $this->logicGuaranteeOrders->getOrderInfo($where, $join, $field);
        if ($order){
            $order['effective_time'] = date('Y-m-d H:i:s',   $order['effective_time']);
            $order['refund_time'] =  $order['refund_time'] ?  date('Y-m-d H:i:s',   $order['refund_time']) : '';
            $this->result('1', 'success', $order);
        }else{
            $this->result(0, '订单错误');
        }
    }

    /**
     * 退保操作
     */
    public function refundHandle(Request $request)
    {
        $data = array_merge(['merchant_user_id' => $this->user['id']], $request->param());
        $this->result($this->logicGuaranteeOrders->refundHandle($data));
    }

}