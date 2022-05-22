<?php


namespace app\usercenter\controller;


class Index extends Base
{
    public function Index()
    {
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