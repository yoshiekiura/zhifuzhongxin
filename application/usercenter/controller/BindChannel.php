<?php


namespace app\usercenter\controller;


use think\Request;

class BindChannel extends Base
{

    /**
     * 申请渠道列表
     */
    public function index(Request $request)
    {
        $user_id  = $request->param('user_id');
        $channel_name = $request->param('channel_name');
        $where = [];
        $user_id &&  $where['a.user_id'] = $user_id;
        $channel_name && $where['c.name'] = ['like',  '%'. $channel_name .'%'];
        $where['a.merchant_user_id'] = $this->user['id'];
        $lists = $this->modelBindChannel->alias('a')
                    ->join('cm_pay_channel c', 'c.id = a.channel_id')
                    ->join('cm_pay_center_user cu', 'cu.id = c.pay_center_uid')
                    ->where($where)
                    ->order('a.create_time desc')
                    ->field('a.*, c.name as channel_name, cu.username as channel_username')
                    ->paginate($request->param('limit'));
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /**
     * 禁用or启用（渠道）
     * @throws \think\exception\DbException
     */
    public function enableHandel()
    {
        $en_able = $this->request->param('en_able', '');
        $id = $this->request->param('id', 0);
        if ((empty($en_able) && $en_able !=0) or !in_array($en_able, [0,1])){
            $this->error('数据错误！');
        }
        $where = ['merchant_user_id' => $this->user['id'], 'id' => $id];
        $row = $this->modelBindChannel->where($where)->find();
        if (!$row){
            $this->error('数据错误！');
        }
        $row->en_able = $en_able;
        $row->save();
        $this->success('操作成功');
    }

    /**
     * 添加渠道账号
     */
    public function addAccount(Request $request){
        $bind_id = $request->param('bind_id');
        $row = $this->modelBindChannel->get($bind_id);

        if (!$row or $row['merchant_user_id'] != $this->user['id'] )  $this->error('数据错误');

        $channel = $this->modelPayChannel->where('pay_center_uid', $row['channel_user_id'])->find();

        if (!$channel) $this->error('渠道用户暂未添加渠道');

        $row['channel_name'] = $channel['name'];

        $this->request->isPost() && $this->result($this->logicBindChannel->saveAccount($this->request->post()));

        $this->assign('row', $row);
        return $this->fetch();
    }

    /**
     * 编辑渠道账号
     */
    public function editAccount(){
        $id = $this->request->param('id');
        $where = array(
            'a.pay_center_uid' => $this->user['id'],
            'a.id' => $id
        );
        $row = $this->modelPayCenterUserAccount
            ->alias('a')
            ->join('cm_pay_channel c', 'c.id = a.channel_id')
            ->field('a.*, c.name as channel_name')
            ->where($where)->find();
        if (!$row){
            $this->error('数据错误');
        }
        $this->request->isPost() && $this->result($this->logicMerchantsBinding->saveAccount($this->request->post()));
        $this->assign('row', $row);
        return $this->fetch();
    }

    /**
     * 商户申请绑定渠道列表
     */
    public function userBindChannel(Request $request)
    {
        $user_id  = $request->param('user_id');
        $where = [];
        $user_id &&  $where['a.user_id'] = $user_id;
        $where['a.channel_user_id'] = $this->user['id'];
        $lists = $this->modelBindChannel->alias('a')
            ->join('cm_pay_center_user cu', 'cu.id = a.merchant_user_id')
            ->where($where)
            ->order('a.create_time desc')
            ->field('a.*, cu.username as merchant_username')
            ->paginate($request->param('limit'));
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /**
     * 渠道审核商户绑定渠道申请操作
     */
    public function channelBindHandle(Request $request)
    {
        $id = $request->param('id');
        $status = $request->param('status');
        $where = array('id' => $id, 'channel_user_id' => $this->user['id']);
        $row = $this->modelBindChannel->where($where)->find();
        if (!$row or !$status or !in_array($status, [1,2])){
            $this->error('数据错误');
        }

        $this->result($this->logicBindChannel->channelBindHandle($request->param()));
    }

    /**
     * 渠道禁用or启用商户
     */
    public function ChannelHandleUser(Request $request)
    {
        $id = $request->param('id');
        $channel_status = $request->param('channel_status');
        $where = array('id' => $id, 'channel_user_id' => $this->user['id']);
        $row = $this->modelBindChannel->where($where)->find();
        if (!$row or !in_array($channel_status, [0,1])){
            $this->error('数据错误');
        }
        $row->channel_status = $channel_status;
        $row->save();
        $this->success('操作成功');
    }


    /**
     * 获取渠道支付方式list
     */
    public function getBindChannelCodes(Request $request)
    {
        $uid = $request->param('uid');

        $where  = [
            'user_id' => $uid,
            'merchant_user_id' => $this->user['id'],
            'status' => 1,
            'channel_status' => 1,
            'en_able' => 1
        ];

        $bindChannel = $this->modelBindChannel->where($where)->select();
        $codes = [];
        if ($bindChannel){
            $channelWh = [
                'a.channel_id' => ['in', array_column($bindChannel, 'channel_id')],
                'c.status' => 1
            ];
            $channelCodes = $this->modelPayCenterChannelCode
                ->alias('a')
                ->join('cm_pay_channel c', 'c.id = a.channel_id')
                ->where($channelWh)
                ->column('code_id');
            $codes =  $this->logicPay->getCodeList(array('id'=>array('IN', $channelCodes)), true, 'create_time desc', false);
        }

        $this->success('操作成功','', $codes);
    }


    /**
     * 商户申请绑定支付渠道
     */
    /**
     *商户申请渠道
     */
    public function applyChannel()
    {
        $uid = $this->request->param('uid');
        $map = array(
            'uid' => $uid,
            'pay_center_uid' => $this->user['id']
        );
        $user = $this->modelUser->where($map)->find();
        if (!$user) $this->error('数据错误');

        if ($this->request->isAjax()) $this->result($this->logicBindChannel->saveBind($this->request->param()));

        /*过滤已经申请过的渠道*/
        $userBindChannelWh = array(
            'merchant_user_id'=> $this->user['id'],
            'user_id' => $uid,
        );
        $userBindChannels = $this->modelBindChannel->where($userBindChannelWh)->column('channel_id');

        $where = ['a.status' => 1, 'a.id' => ['NOT IN' ,array_unique($userBindChannels)]];

        $channels = $this->modelPayChannel
            ->alias('a')
            ->join('cm_pay_center_user pu', 'pu.id = a.pay_center_uid')
            ->field('a.*, pu.username')
            ->where($where)
            ->select();
        $this->assign('uid', $uid);
        $this->assign('channels', $channels);
        return $this->fetch();
    }


    /**
     * 商户渠道测试
     */
    public function channelTest()
    {
        $uid = $this->request->param('uid');
        $code = $this->request->param('code', '');
        $amount = $this->request->param('amount', 0);
        if (empty($code)){
            $this->error('没有选择通道编码！');
        }
        if ($amount  <= 0){
            $this->error('金额不正确！');
        }

        $user = $this->modelUser->alias('a')
            ->join('cm_api ap', 'ap.uid = a.uid')
            ->where([
                'a.uid' => $uid,
                'pay_center_uid' => $this->user['id'],
            ])
            ->field('a.*, ap.key')
            ->find();


        $mchid = $user['uid'];

        $Md5key = $user['key'];
        $host = $_SERVER["HTTP_HOST"];
        $requestUrl = 'http://'.$host.'/apis/order';
        $data = array(
            'mid' => $mchid,
            'o_trade_no' => date('ymdHis').rand(1000,9999),
            'amount' => $amount,
            'channel' =>$code,
            'notify_address' => $host.'/test/notify.php',
            'return_address' => $host.'/test/return.php',
            'body' => "addH",
        );
        ksort($data);
        $signData = "";
        foreach ($data as $key=>$value)
        {
            $signData = $signData.$key."=".$value;
            $signData = $signData . "&";
        }

        $signData = $signData."key=".$Md5key;
        $sign = md5($signData);

        $data['sign'] = $sign;

        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $requestUrl);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //执行命令
        $json = curl_exec($curl);

        if(isset($_GET['debug']) && $_GET['debug']==1)
        {
            echo $json;die();
        }
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
//halt($json);
        $data = json_decode($json, true);
        if(isset($data['status']) && $data['status'] == 1)
        {
            $this->success('操作成功', $data['data']['request_url']);
        }
        else
        {
            $this->error($data['msg'] ?? '未知错误');
        }

    }



}