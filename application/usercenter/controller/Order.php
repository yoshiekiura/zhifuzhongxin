<?php


namespace app\usercenter\controller;


use app\common\library\enum\CodeEnum;

class Order extends Base
{
    /**
     * 商户交易订单
     *
     * @return mixed
     */
    public function index(){
        $where['a.pay_center_uid'] = $this->user['id'];
        //组合搜索
        !empty($this->request->get('trade_no')) && $where['a.out_trade_no']
            = ['like', '%'.$this->request->get('trade_no').'%'];

        !empty($this->request->get('channel')) && $where['a.channel']
            = ['eq', $this->request->get('channel')];

        //时间搜索  时间戳搜素
        $date = $this->request->param('d/a');

        $start = empty($date['start']) ? date('Y-m-d ')." 00:00:00" : $date['start'];
        $end = empty($date['end']) ? date('Y-m-d')." 23:59:59" : $date['end'];
        $where['a.create_time'] = ['between', [strtotime($start), strtotime($end)]];
        //状态
        if(!empty($this->request->get('status')) || $this->request->get('status') === '0')
        {
            $where['status'] = $this->request->get('status');
        }
        $field = 'a.*, c.name as channel_name';
        $orderLists = $this->logicOrders->getUsercenterOrderList($where,$field, 'create_time desc', 10);

        //查询当前符合条件的订单的的总金额  编辑封闭 新增放开 原则
        $cals =  $this->logicOrders->calUsercenterOrdersData($where);

        $this->assign('list',$orderLists );
        $this->assign('cal',$cals);
        $this->assign('code',$this->logicPay->getCodeList([]));
        $this->assign('start', $start);
        $this->assign('end', $end);
        return $this->fetch();
    }

    /**
     * 强制回调
     */
    public function mandatoryCallback()
    {
        $orderid = $this->request->param('id');
        $orderinfo = $this->modelOrders->where(['id'=>$orderid, 'pay_center_uid' => $this->user['id']])->find();
        if (!$orderinfo){
            $this->error('数据错误');
        }

        if (!empty($orderinfo) && $orderinfo['status'] == '1'  ) {
            $model = new \app\api\logic\Notify();
            $model->updateOrderInfo($orderinfo, 2);
            $OrdersNotify = new  \app\common\logic\OrdersNotify();
            $logicQueue = new  \app\common\logic\Queue();

            $OrdersNotify->saveOrderNotify($orderinfo);
            $logicQueue->pushJobDataToQueue('AutoOrderNotify' , $orderinfo , 'AutoOrderNotify');
            //单独修改补单备注(编辑封闭新增放开原则)todo 此处后期事务处理最好
            $this->modelOrders->where(['id'=>$orderid, 'pay_center_uid' => $this->user['id']])->setField('bd_remarks', '商户强制回调');
        }
        $this->success('操作成功');
    }
}