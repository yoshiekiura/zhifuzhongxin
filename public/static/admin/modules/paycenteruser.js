/*
 *  +----------------------------------------------------------------------
 *  | 中通支付系统 [ WE CAN DO IT JUST THINK ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2018 http://www.iredcap.cn All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed ( https://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: Brian Waring <BrianWaring98@gmail.com>
 *  +----------------------------------------------------------------------
 */


layui.define(["table", "form"],
    function (e) {
        var t = layui.$,
            q = layui.$,
            i = layui.table,
            u = layui.util,
            n = layui.form;
        //渲染码商二维码
        i.render({
            elem: "#app-pay-paycenteruser-list",
            url: "index",
            //自定义响应字段
            response: {
                statusCode: 1 //数据状态一切正常的状态码
            },
            cols: [
                [{
                    field: "id",
                    width: 80,
                    title: "ID",
                    sort: !0
                }, {
                    field: "username",
                    width: 250,
                    title: "用户名"
                }, {
                    field: "alias_name",
                    width: 250,
                    title: "别名"
                }, {
                    field: "user_type",
                    width: 250,
                    title: "用户类型",
                    templet: function (d) {
                        let str = '';
                        if (d.user_type == 1){
                            str = '渠道用户'
                        }else if(d.user_type == 2){
                            str = '商户用户'
                        }else if(d.user_type == 3){
                            str = '三方用户';
                        }else if(d.user_type==4){
                            str = '代理用户';
                        }
                      return str;
                    }
                }]
            ],
            page: !0,
            limit: 10,
            limits: [10, 15, 20, 25, 30],
            text: "对不起，加载出现异常！"
        })


        e("paycenteruser", {})
    });
