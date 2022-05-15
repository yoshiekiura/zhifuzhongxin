<?php


namespace app\usercenter\controller;


use app\usercenter\logic\Channel;
use think\exception\Handle;

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
            = ['like', '%'.$this->request->get('name').'%'];
        $logicChannel = new Channel();

        $channelLists = $logicChannel->getChannelsList($map,true, 'id desc');
        $this->assign('list',$channelLists );
        return $this->fetch();
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
            $withChannel =  $this->modelPayChannel->where('pay_center_uid' ,'=', $this->user['id'])->select();
            if (!collection($withChannel)->isEmpty()){
                $this->error('只能添加一个渠道');
            }
            $logicChannel = new Channel();
            $params['pay_center_uid'] = $this->user['id'];
            $randKey = time().'_'.getRandChar().'Pay';
            $params['notify_url'] = $randKey;
            $params['return_url'] = $randKey;
            $ret =$logicChannel->saveChannel($params);
            if ($ret['code'] == 0){
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
        if (!$row){
            $this->error('数据错误');
        }
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $logicChannel = new Channel();
            $ret =$logicChannel->saveChannel($params);
            if ($ret['code'] == 0){
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
        if (!$row){
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
            $ret =$logicChannel->saveChannelAccount($params);
            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
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
        $channel  = $this->modelPayChannel->where($where)->find();
        if (!$channel){
            $this->error('数据错误');
        }
        $logicChannel = new Channel();
        if ($this->request->isPost()){
            $ret =$logicChannel->setChannelCode($this->request->param());
            if ($ret['code'] == 0){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $codes =  $this->modelPayCode->where(['status' => 1])->select();
        $this->assign('channel_id', $channel_id);
        $this->assign('codes',   $logicChannel->matchingCode($channel_id));
        return $this->fetch();
    }


    /**
     * 获取渠道下面得账号
     */
    public function getChannelAccountList()
    {
        //做了限制只能选择添加一个渠道，这里直接获取一条数据就可以了
        $map = [
            'pay_center_uid' => $this->user['id'],
            'status' => 1
        ];
        $channel =  $this->modelPayChannel->where($map)->find();
        halt($channel);
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
        if (!$channel){
            $this->error('没有可用得渠道');
        }
        $accountMap = [
            'channel_id' =>     $channel['id'],
            'status' => 0
        ];
        $field = 'id,channel_id,name';
        $PayCenterChannelAccount = $this->modelPayCenterChannelAccount->field($field)->where($accountMap)->select();
        if (collection($PayCenterChannelAccount)->isEmpty()){
            $this->error('没有可用的账号，请先添加渠道账号');
        }
        $this->success('操作成功', null, $PayCenterChannelAccount);
    }
}