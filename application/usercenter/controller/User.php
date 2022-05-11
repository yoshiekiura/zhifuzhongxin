<?php


namespace app\usercenter\controller;


class User extends Base
{

    public function index()
    {
        $this->assign('user', $this->user);
        return $this->fetch('');
    }
}