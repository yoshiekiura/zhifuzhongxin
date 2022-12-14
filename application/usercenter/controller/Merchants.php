<?php


namespace app\usercenter\controller;


use app\common\logic\User;
use think\exception\Handle;
use think\Request;

class Merchants extends Base
{
    /**
     * 商户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function list_merchant()
    {
        $map['a.pay_center_uid'] = $this->user['id'];
        $map['a.status'] = 1;
        !empty($this->request->get('username')) && $map['a.username']
            = ['like', '%' . $this->request->get('username') . '%'];

        $data = $this->modelUser->where($map)
            ->alias('a')
            ->join('api ap', 'a.uid = ap.uid')
            ->order('a.create_time desc')
            ->field('a.*, ap.key')
            ->paginate(10);

        $this->assign('list', $data);
        return $this->fetch('list_merchant');
    }



    /**
     * 添加商户
     *
     */
    public function add_merchant()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['pay_center_uid'] = $this->user['id'];
            //我不改以前的addUser方法数据写死
            $params['password'] = getRandChar(6);
            $params['account'] = 'usercenter666@qq.com';
            $params['auth_login_ips'] = '127.0.0.1';
            $params['status'] = 1;

            $ret = $this->logicUser->addUser($params);

            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        return $this->fetch('add_merchant');
    }

    /**
     * 编辑
     */
    public function edit_merchant()
    {
        $row = $this->modelUser->where(['pay_center_uid' => $this->user['id'], 'uid' => $this->request->param('uid')])->find();
        if (!$row){
            $this->error('数据错误');
        }
        if ($this->request->isPost()) {
            $username = $this->request->param('username');
            if (empty($username)){
                $this->error('用户名不能为空');
            }
            $data['username'] = $username;
            $data['uid'] = $this->request->param('uid');
            $ret = $this->modelUser->allowField(true)->isUpdate(true)->save($data);
            if (!$ret){
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }
        $this->assign('row', $row);
        return $this->fetch('edit_merchant');
    }

    /**
     * 删除 把状态改成0 后面看在
     */
    public function del_merchant()
    {
        $where = ['pay_center_uid' => $this->user['id'], 'uid' => $this->request->param('uid'), 'status' => 1];
        $row = $this->modelUser->where($where)->find($where);
        if (!$row){
            $this->error('数据错误');
        }
        $row->status = 0;
        $row->save();
        $this->success('删除成功');
    }

    /**
     * 对接信息
     */
    public function enter_into()
    {
        $uid = $this->request->param('uid');
        $map = [
            'a.uid' => $uid,
            'a.pay_center_uid' => $this->user['id']
        ];
        $userinfo = $this->modelUser->where($map)
            ->alias('a')
            ->join('api ap', 'a.uid = ap.uid')
            ->field('a.*, ap.key')
            ->find();




        $userinfo['pay_address']= $this->modelConfig->where(['name' => 'pay_address'])->value('value');
        $userinfo['query_address'] = $this->modelConfig->where(['name' => 'query_address'])->value('value');
        $userinfo['callback_ip'] = $this->modelConfig->where(['name' => 'notify_ip'])->value('value');

        if (!$userinfo){
            $this->error('数据错误');
        }
        $this->assign('userinfo', $userinfo);
        return $this->fetch('enter_into');
    }

    /**
     * 绑定列表
     */

    public function bind_list()
    {
        $map['a.merchant_id'] = $this->request->param('uid', '');
        if (empty($map['a.merchant_id'])){
            $this->error('数据错误');
        }
        $map['a.merchant_user_id'] = $this->user['id'];
        !empty($this->request->get('user_username')) && $map['u.username']
            = ['like', '%' . $this->request->get('user_username') . '%'];
        $logicMerchantsBinding = new \app\usercenter\logic\MerchantsBinding();
        $field = 'a.*, pu.username as pay_center_username, u.username as user_username';

        $list = $logicMerchantsBinding->bindingUserList($map, 'a', $field, 'a.channel_user_id', 'a.addtime desc');
        $this->assign('list', $list);
        $this->assign('uid', $map['a.merchant_id']);
        return $this->fetch();
    }

    /**
     * 获取支付方式list
     */
    public function getChannelCodesList()
    {
         $lists = [];
         $where = [];
         //是否绑定渠道，没有绑定只返回test编码
         $binding =  $this->modelMerchantBinding->where(['id' => $this->request->param('id'), 'merchant_user_id' => $this->user['id']])->find();
         if (!$binding) return $lists;
        $where['status'] = 1;
         if ($binding->status != 1){
             $where['name'] = 'test';
         }
         $ret =  $this->logicPay->getCodeList($where, true, 'create_time desc', false);
         $this->success('操作成功','', $ret);
    }


    /**
     * 商户渠道测试
     */
    public function channelTest()
    {
        $id = $this->request->param('bindId');
        $code = $this->request->param('code', '');
        $amount = $this->request->param('amount', 0);
        if (empty($code)){
            $this->error('没有选择通道编码！');
        }
        if ($amount  <= 0){
            $this->error('金额不正确！');
        }

        $merchant_binding = $this->modelMerchantBinding
            ->alias('a')
            ->join('api p', 'p.uid = a.merchant_id')
            ->field('a.*, p.key')
            ->where('a.id', '=', $id)
            ->find($id);
        if (!$merchant_binding or $merchant_binding['merchant_user_id'] !=$this->user['id']){
            $this->error('数据错误');
        }

        $mchid = $merchant_binding['merchant_id'];

        $Md5key = $merchant_binding['key'];
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
     * 获取可绑定渠道的商户列表
     */
    public function getBindChannelUserList(Request $request)
    {
        $channel_id = $request->param('channel_id');
        $userIds = $this->logicBindChannel->where(array(
            'channel_id' => $channel_id,
            'merchant_user_id' => $this->user['id'],
            'status' => array('neq', 2)
        ))->column('user_id');

        $users = $this->logicUser->where(array(
            'status' => 1,
            'uid' => array('not in', $userIds),
            'pay_center_uid' => $this->user['id']
        ))->field('uid,username')->select();

        $this->result(1, 'success', $users);
    }

    /**
     * 申请绑定渠道
     */
    public function bindChannel(Request $request)
    {
       $this->result($this->logicBindChannel->saveBind($request->param()));
    }
}
