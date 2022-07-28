<?php


namespace app\usercenter\controller;


class Index extends Base
{
    public function Index()
    {
        $where = [];

        $username = $this->request->param('username');
        $username && $where['username'] = ['like', '%'.$username.'%'];

        //查询商户
        $users = $this->modelUser->where(['status' => 1])->where($where)->paginate($this->request->param('limit', 10));

        foreach ($users as $user){
            $user->avatar = letter_avatar($user->username);
        }

        $this->assign('users', $users);
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