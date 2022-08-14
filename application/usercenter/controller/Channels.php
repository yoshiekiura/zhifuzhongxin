<?php


namespace app\usercenter\controller;


use app\usercenter\logic\Channel;

class Channels extends Base
{

    /**
     * 渠道列表
     * @return mixed
     */
    public function index()
    {
        $map['pay_center_uid'] = $this->user['id'];
        $map['status'] = 1;
        !empty($this->request->get('name')) && $map['name']
            = ['like', '%' . $this->request->get('name') . '%'];
        $logicChannel = new Channel();

        $field = 'a.*,t.name as template_name';
        $channelLists = $logicChannel->getChannelsList($map, 'a', $field, 'id desc');
        $this->assign('list', $channelLists);
        return $this->fetch();
    }

    /**
     * 添加用户中心渠道
     *
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            //暂时只能添加一个渠道，前端添加按钮是隐藏了的，这里再判断一下，
            $withChannel = $this->modelPayChannel->where(['pay_center_uid' => $this->user['id'], 'status' => 1])->select();
            if (!collection($withChannel)->isEmpty()) {
                $this->error('只能添加一个渠道');
            }
            $logicChannel = new Channel();
            $params['pay_center_uid'] = $this->user['id'];
            $randKey = time() . '_' . getRandChar() . 'Pay';
            $params['notify_url'] = $randKey;
            $params['return_url'] = $randKey;
            $ret = $logicChannel->saveChannel($params);
            if ($ret['code'] == 0) {
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $channelTmeplate = $this->modelChannelTemplate->select();
        $this->assign('channelTemplate', $channelTmeplate);
        return $this->fetch('add_channel');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $row = $this->modelPayChannel->where(['pay_center_uid' => $this->user['id'], 'id' => $this->request->param('id')])->find();
        if (!$row) {
            $this->error('数据错误');
        }
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $logicChannel = new Channel();
            $ret = $logicChannel->saveChannel($params);
            if ($ret['code'] == 0) {
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $channelTmeplate = $this->modelChannelTemplate->select();
        $this->assign('channelTemplate', $channelTmeplate);
        $this->assign('row', $row);
        return $this->fetch('edit_channel');
    }

    /**
     * 删除 把状态改成0 后面看在
     */
    public function del()
    {
        $where = ['pay_center_uid' => $this->user['id'], 'id' => $this->request->param('id'), 'status' => 1];
        $row = $this->modelPayChannel->where($where)->find($where);
        if (!$row) {
            $this->error('数据错误');
        }
        $row->status = 0;
        $row->save();
        $this->success('删除成功');
    }

    /**
     * 添加渠道账号
     */
    public function addChannelAccount()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $logicChannel = new Channel();
            $params['status'] = 0;
            $params['create_time'] = time();
            $ret = $logicChannel->saveChannelAccount($params);
            if ($ret['code'] == 0) {
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }

        $channelMap['pay_center_uid'] = $this->user['id'];
        $channelMap['status'] = 1;
        $logicChannel = new Channel();
        $channelLists = $logicChannel->getChannelsList($channelMap, 'a', 'a.*', 'id desc');
        $this->assign('channel', $channelLists);
        $this->assign('channel_id', $this->request->param('channel_id'));
        return $this->fetch();
    }


    /**
     * 配置编码
     */
    public function setChannelCode()
    {
        $channel_id = $this->request->param('channel_id');
        $where = [
            'pay_center_uid' => $this->user['id'],
            'id' => $channel_id
        ];
        $channel = $this->modelPayChannel->where($where)->find();
        if (!$channel) {
            $this->error('数据错误');
        }
        $logicChannel = new Channel();
        if ($this->request->isPost()) {
            $ret = $logicChannel->setChannelCode($this->request->param());
            if ($ret['code'] == 0) {
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $codes = $this->modelPayCode->where(['status' => 1])->select();
        $this->assign('channel_id', $channel_id);
        $this->assign('codes', $logicChannel->matchingCode($channel_id));
        return $this->fetch();
    }


    /**
     * 获取渠道下面得账号
     */
    public function getChannelAccountList()
    {
        $channel_id = $this->request->param('channel_id');
        $where = [
            'pay_center_uid' => $this->user['id'],
            'id' => $channel_id
        ];
        $channel = $this->modelPayChannel->where($where)->find();
        if (!$channel) {
            $this->error('数据错误');
        }

        return $this->success('success', '', $this->modelPayCenterChannelAccount->where('channel_id', $channel_id)->select());

    }

    /**
     * 获取第一个渠道下面得账户
     */
    public function getOneChannelAccountList()
    {
        $map = [
            'pay_center_uid' => $this->user['id'],
            'status' => 1
        ];
        $channel = $this->modelPayChannel->where($map)->order('id asc')->find();
        if (!$channel) {
            $this->error('没有可用得渠道');
        }
        $accountMap = [
            'channel_id' => $channel['id'],
            'status' => 0
        ];
        $field = 'id,channel_id,name';
        $PayCenterChannelAccount = $this->modelPayCenterChannelAccount->field($field)->where($accountMap)->select();
        if (collection($PayCenterChannelAccount)->isEmpty()) {
            $this->error('没有可用的账号，请先添加渠道账号');
        }
        $this->success('操作成功', null, $PayCenterChannelAccount);
    }

    /**
     * 渠道账户列表
     */
    public function channelAccountList()
    {
        !empty($this->request->get('name')) && $map['a.name']
            = ['like', '%' . $this->request->get('name') . '%'];
        $logicChannel = new Channel();
        $map['c.pay_center_uid'] = $this->user['id'];
        $map['c.status'] = 1;
        $field = 'a.*, c.name as channel_name';
        $accountLists = $logicChannel->channelAccountList($map, 'a', $field, 'id desc', 10);

        $this->assign('list', $accountLists);

        return $this->fetch();
    }

    /**
     * 编辑渠道账户
     */
    public function editChannelAccount()
    {
        $id = $this->request->param('id', '');
        $map = [
            'a.id' => $id,
            'c.pay_center_uid' => $this->user['id'],
            'c.status' => 1
        ];
        $modelPayCenterChannelAccount = $this->modelPayCenterChannelAccount;
        $modelPayCenterChannelAccount->alias('a');
        $row = $modelPayCenterChannelAccount->join('pay_channel c', 'c.id = a.channel_id', 'left')
            ->field('a.*')
            ->where($map)->find();
        if (!$row) {
            $this->error('数据错误');
        }
        if ($this->request->post()) {
            $params = $this->request->param();
            $logicChannel = new Channel();
            $params['create_time'] = time();
            $ret = $logicChannel->saveChannelAccount($params);
            if ($ret['code'] == 0) {
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $this->assign('row', $row);
        return $this->fetch();
    }

    public function channelTest()
    {
/*        $json = ' {"code":0,"msg":"\u8bf7\u6c42\u6210\u529f","data":{"request_url":"https:\/\/d.alipay.com\/i\/index2.htm?scheme=alipays%3A%2F%2Fplatformapi%2Fstartapp%3FsaId%3D10000007%26clientVersion%3D3.7.0.0718%26qrcode%3Dhttp%253a%252f%252fliandong2.kitycb.com%252fpayurl%252fEcpss.aspx%253fo%253d22081411585873230896%2526a%253d100%2526b%253d1011%3F_s%3Dweb-other"}}';


        $this->success('success', '',  json_decode( $json, true));*/

        $accountId = $this->request->param('accountId',  '');
        $channelCode = $this->request->param('channelCode',  '');
        $codeVal = $this->request->param('codeVal', '');
        $amount = $this->request->param('amount', 0);

//        halt(json_encode(compact('codeVal', 'channelCode', 'amount')));

        if (empty($channelCode)){
            $this->error('没有选择通道编码！！！');
        }
        if (empty($codeVal)){
            $this->error('编码值内容为空！！！');
        }
        if ($amount  <= 0){
            $this->error('金额不正确！！！');
        }
        if (empty($accountId)){
            $this->error('账号不能为空！！！');
        }

        $mchid = '100001';  //写死100001 测试
        $Md5key = '772ae1d32322f49508307b2f31a0107f';
        $host = $_SERVER["HTTP_HOST"];

        $requestUrl = 'http://'.$host.'/api/pay/unifiedorder';
        $data = array(
            'mchid' => $mchid,
            'out_trade_no' => date('ymdHis').rand(1000,9999),
            'amount' => $amount,
            'channel' =>$codeVal,
            'notify_url' => $host.'/test/notify.php',
            'return_url' => $host.'/test/return.php',
            'time_stamp' => date("Ymdhis"),
            'body' =>$accountId . ':' . $codeVal, //拉单测试传入account_id过去
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

        $data = json_decode($json, true);
        if(isset($data['code']) && $data['code'] == 0)
        {
            $this->success('操作成功', '', $data);
        }
        else
        {
            $this->error($data['msg'] ?? '未知错误');
        }
    }
}