<?php


namespace app\usercenter\controller;


use think\Request;

class Index extends Base
{
    public function Index(Request $request)
    {
        $where = [];
        if ($request->isAjax()){
            $username = $this->request->param('username');
            $createTime = $this->request->param('createTime');
            $userType = $this->request->param('userType');
            $username && $where['username'] = ['like', '%'.$username.'%'];
            if ($createTime){
                switch ($createTime){
                    case 'w':
                        $where['create_time'] = ['>', strtotime('-1 week')];
                        break;
                    case 'm':
                        $where['create_time'] = ['>', strtotime('-1 month')];
                        break;
                    case '3m':
                        $where['create_time'] = ['>', strtotime('-3 month')];
                        break;
                    case 'd':
                        $where['create_time'] = ['>', strtotime('-1 year')];
                        break;
                }
            }
            $userType && $where['user_type'] = $userType;
            $users = $this->modelPayCenterUser->where(['status' => 1])->where($where)->order('create_time desc')->paginate($this->request->param('limit', 10));;

            foreach ($users as $user){
                $user->avatar = letter_avatar($user->username);
            }
            return $this->success('success','', $users);
        }

        $this->assign('user_info', $this->user);
        return $this->fetch('index');
    }

    /**
     * 在线文档
     */
    public function apiDoc()
    {
        return $this->fetch();
    }
}