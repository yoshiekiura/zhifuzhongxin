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

}