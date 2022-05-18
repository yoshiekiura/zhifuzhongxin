<?php


namespace app\admin\controller;


use app\common\library\enum\CodeEnum;

class Paycenteruser extends BaseAdmin
{

    public function index()
    {
        if ($this->request->isAjax()){
            $where = [];
            //组合搜索
            !empty($this->request->param('username')) && $where['username']
                = ['like', '%'.$this->request->param('username').'%'];
           $data = $this->logicPayusercenter->getUserList($where,true, 'create_time desc',false);
           foreach ($data as &$item){
               $item['pid_username'] =  $this->modelPaycenteruser->where('id', '=', $item['pid'])->value('username');
           }
            $this->result($data || !empty($data) ?
                [
                    'code' => CodeEnum::SUCCESS,
                    'msg'=> '',
                    'count'=>$data->total(),
                    'data'=>$data->items()
                ] : [
                    'code' => CodeEnum::ERROR,
                    'msg'=> '暂无数据',
                    'count'=>10,
                    'data'=>$data
                ]);
        }
        return $this->fetch();
    }

    /**
     * 添加用户
     */
    public function addUser()
    {
        if ($this->request->isPost()){
            $params = $this->request->param();
            $params['admin_id'] = is_admin_login();
            $ret =  $this->logicPayusercenter->saveUser($params);
            if ($ret['code'] == 0 ){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        return $this->fetch('add_user');
    }

    /**
     * 编辑用户
     */
    public function editUser()
    {
        $id = $this->request->get('id');
        $row = $this->modelPayCenterUser->get($id);
        if ($this->request->isPost()){
            $params = $this->request->param();
            if (isset($params['password']) && !empty($params['password'])){
                $params['password'] = pwdMd52($params['password']);
            }else{
                unset($params['password']);
            }
            $ret = $this->logicPayusercenter->saveUser($params);
            if ($ret['code'] == 0 ){
                $this->error($ret['msg']);
            }
            $this->success($ret['msg']);
        }
        $this->assign('row', $row);
        return $this->fetch('edit_user');
    }

    /**
     * 删除用户
     */
    public function delUser()
    {
        $id = $this->request->get('id');
        $row = $this->modelPayCenterUser->get($id);
        action_log('删除', '删除支付中心用户' . $row['id']);
        $row->status = 0;
        $row->save();
        $this->success('操作成功');
    }

}