<?php


namespace app\usercenter\controller;


use app\common\library\enum\CodeEnum;
use app\common\logic\OrdersNotify;
use think\Collection;
use think\Db;
use think\Log;

class Order extends Base
{
    /**
     * 商户交易订单
     *
     * @return mixed
     */
    public function index(){

        $userType = $this->user['user_type'];

        switch ($userType) {
            case '1':
                    $channels =  $this->logicPay->getChannelList(['pay_center_uid' => $this->user['id'] ], 'id');
                    $cnl_ids = array_column(collection($channels)->toArray(), 'id');
//                    halt($this->user['id']);
                    $where['a.cnl_id'] = ['in', $cnl_ids];
                break;
            case '2':
                $where['a.pay_center_uid'] = $this->user['id'];
                break;
            default:
                $where['a.pay_center_uid'] = $this->user['id'];
                break;
        }

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
        $field = 'a.*, ca.name as account_name, c.name as channel_name';

        $orderLists = $this->logicOrders->getUsercenterOrderList($where,$field, 'create_time desc', 10);
//halt($orderLists);
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
     * test编码强制回调
     */
    public function mandatoryCallback()
    {
        $orderid = $this->request->param('id');
        $orderinfo = $this->modelOrders->where(['id'=>$orderid, 'pay_center_uid' => $this->user['id']])->find();
        if (!$orderinfo){
            $this->error('数据错误');
        }

        $where        = array();
        $where['uid'] = $orderinfo['uid'];
        $LogicApi     = new \app\common\logic\Api();
        $appKey       = $LogicApi->getApiInfo($where, "key");
        $to_sign_data = $this->logicOrders->usercenteBuildSignData($orderinfo->toArray(), $appKey["key"]);
        halt($to_sign_data);
        Log::notice("posturl: " . $orderinfo['notify_url']);
        Log::notice("sign data: " . json_encode($to_sign_data));

        Db::startTrans();
        try {
            $orderinfo->status = 2;
            $orderinfo->bd_remarks = 'test编码商户强制回调';
            $orderinfo->save();
            $OrdersNotify = new OrdersNotify();
            $OrdersNotify->saveOrderNotify($orderinfo);
            Db::commit();

        }catch (\Exception $e){
            Db::rollback();
            $this->error(config('app_debug') ? $e->getMessage() : '未知错误');
        }
        $this->success('操作成功');
    }
}