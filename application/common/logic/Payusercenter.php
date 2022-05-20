<?php


namespace app\common\logic;


use app\common\library\enum\CodeEnum;
use think\Request;

class Payusercenter extends BaseLogic
{
    /**
     * 获取用户列表
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection|\think\Paginator|void
     */
    public function  getUserList($where = [], $field = true,  $order = 'id desc',$paginate = 10)
    {
        $this->modelPayCenterUser->limit = !$paginate;
        $paginate  = Request::instance()->get('limit', 10);
        return $this->modelPayCenterUser->where($where)->field($field)->order($order)->paginate($paginate);
//         $this->modelPayCenterUser->getList($where, $field, $order, $paginate);
    }

    /**
     * 保存用户
     * @param $data
     * @return array
     */
    public function saveUser($data)
    {
        $validate = $this->validatePaycenteruser->scene($data['scene'])->check($data);

        if ($validate == false){
            return ['code' => CodeEnum::ERROR, 'msg' =>$this->validatePaycenteruser->getError() ];
        }
        if ($data['scene'] == 'add'){
            $data['password'] = pwdMd52($data['password']);
            $data['create_time'] = time();
        }

        $ret = $this->modelPayCenterUser->allowField(true)->isUpdate($data['scene'] == 'add' ? false : true)->save($data);
        $msg  = $data['scene'] == 'add' ? '添加' : '修改';
        if (!$ret){
            return ['code' => CodeEnum::SUCCESS, 'msg' =>$msg . '失败'];
        }
        return ['code' => CodeEnum::SUCCESS, 'msg' =>$msg . '成功'];
    }


    /**
     * 获取支付中心用户信息详情
     *
     */
    public function getUserInfo($where = [], $field = true)
    {
        return $this->modelPayCenterUser->getInfo($where, $field);
    }
}