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
                },{
                    field: "username",
                    width: 150,
                    title: "用户名"
                },{
                    field: "money",
                    width: 80,
                    title: "跑量余额"
                },{
                    field: "usdt_balance",
                    width: 120,
                    title: "USDT可用余额"
                },{

                        field: "usdt_disable_balance",
                        width: 120,
                        title: "USDT冻结余额"
                },{
                    field: "pid_username",
                    width: 100,
                    title: "所属代理"
                }, {
                    field: "alias_name",
                    width: 100,
                    title: "别名"
                }, {
                    field: "user_type",
                    width: 100,
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
                },{
                    title: "操作",
                    align: "center",
                    minWidth:500,
                    fixed: "right",
                    toolbar: "#table-pay-channel"
                }
                ]
            ],
            page: !0,
            limit: 10,
            limits: [10, 15, 20, 25, 30],
            text: "对不起，加载出现异常！"
        })
        i.on("tool(app-pay-paycenteruser-list)",
            function (e) {
                if ("del" === e.event) {
                    layer.prompt({
                            formType: 1,
                            title: "敏感操作，请验证口令"
                        },
                        function (d, i) {
                            layer.close(i),
                                layer.confirm("真的删除此用户吗？",
                                    function (d) {
                                        t.ajax({
                                            url: 'delUser?id=' + e.data.id,
                                            method: 'POST',
                                            success: function (res) {
                                                if (res.code == 1) {
                                                    e.del()
                                                }
                                                layer.msg(res.msg, {icon: res.code == 1 ? 1 : 2, time: 1500});
                                                layer.close(d); //关闭弹层
                                            }
                                        });
                                    })
                        });
                }  else if ("edit" === e.event) {
                    t(e.tr);
                    layer.open({
                        type: 2,
                        title: "编辑用户",
                        content: "editUser.html?id=" + e.data.id,
                        maxmin: !0,
                        maxmin: !0, area: ['80%', '60%'],
                        btn: ["确定", "取消"],
                        yes: function (f, t) {
                            var l = window["layui-layer-iframe" + f],
                                r = "app-pay-paycenteruser-submit",
                                n = t.find("iframe").contents().find("#" + r);
                            l.layui.form.on("submit(" + r + ")",
                                function (t) {
                                    var l = t.field;
                                    layui.$.post("editUser", l, function (res) {
                                        if (res.code == 1) {
                                            i.render(),
                                                layer.close(f)
                                        }
                                        layer.msg(res.msg, {icon: res.code == 1 ? 1 : 2, time: 1500});
                                    });
                                }),
                                n.trigger("click")
                        },
                        success: function (e, t) {
                        }
                    })
                } else if ("change-usdt-balance" === e.event){
                    layer.prompt({
                            formType: 1,
                            title: "敏感操作，请验证口令",
                        },
                        function (d, f) {

                            //检测口令
                            t.ajax({
                                url: '/admin/api/checkOpCommand?command=' + d,
                                method: 'POST',
                                success: function (res) {
                                    if (res.code == 1) {
                                        //口令正确
                                        layer.close(d); //关闭弹层
                                        t(e.tr);
                                        layer.open({
                                            type: 2
                                            , title: '增减USDT余额'
                                            , content: 'changeUsdtBalance.html?id=' + e.data.id
                                            , maxmin: true
                                            , area: ['80%', '60%']
                                            , btn: ['确定', '取消']
                                            , yes: function (index, layero) {
                                                var iframeWindow = window['layui-layer-iframe' + index]
                                                    , submitID = 'app-user-manage-submit'
                                                    ,
                                                    submit = layero.find('iframe').contents().find('#' + submitID);

                                                //监听提交
                                                iframeWindow.layui.form.on('submit(' + submitID + ')', function (obj) {
                                                    var field = obj.field; //获取提交的字段

                                                    //提交 Ajax 成功后，静态更新表格中的数据
                                                    t.ajax({
                                                        url: 'changeUsdtBalance.html?uid=' + e.data.id,
                                                        method: 'POST',
                                                        data: field,
                                                        success: function (res) {
                                                            if (res.code == 1) {
                                                                layer.closeAll();
                                                                i.reload('app-pay-paycenteruser-list');
                                                            } else {
                                                                layer.msg(res.msg, {icon: 2, time: 1500});
                                                            }
                                                        }
                                                    });
                                                });
                                                submit.trigger('click');
                                            }
                                        });
                                    } else {
                                        layer.msg(res.msg, {icon: 2, time: 1500});
                                        layer.close(d); //关闭弹层
                                    }
                                }
                            });
                        });
                }
            }),

        e("paycenteruser", {})
    });
