<?php


namespace app\usercenter\controller;


class Channels extends Base
{

    public function index()
    {
        return $this->fetch();
    }

    /**
     * 添加用户中心 模板
     *
     */
    public function add()
    {
        if ($this->request->isPost()) {
            return $this->error('添加成功');
//            halt($this->request->param());

        }
        return $this->fetch('add_channel');
    }
}