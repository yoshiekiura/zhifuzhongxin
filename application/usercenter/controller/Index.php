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
            $username && $where['username'] = ['like', '%'.$username.'%'];
            $users = $this->modelUser->where(['status' => 1])->where($where)->paginate($this->request->param('limit', 10));
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