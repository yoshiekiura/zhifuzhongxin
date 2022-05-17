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
                    title: "ID00",
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
                },{
                    title: "操作",
                    align: "center",
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
                } else if ("appoint_ndex" === e.event) {
                    t(e.tr);
                    layer.open({
                        type: 2,
                        title: "指定渠道",
                        content: "appoint_ndex.html?uid=" + e.data.uid,
                        maxmin: !0,
                        maxmin: !0, area: ['80%', '60%'],
                        // btn: ["确定", "取消"],
                    })
                } else if ("userpaycode" === e.event) {
                    t(e.tr);
                    layer.open({
                        type: 2,
                        title: "商户支付产品",
                        content: "codes.html?id=" + e.data.uid,
                        maxmin: !0,
                        maxmin: !0, area: ['80%', '60%'],
                        btn: ["确定", "取消"],
                        yes: function (f, t) {
                            var l = window["layui-layer-iframe" + f],
                                r = "app-user-profit-submit",
                                n = t.find("iframe").contents().find("#" + r);
                            l.layui.form.on("submit(" + r + ")",
                                function (t) {
                                    var l = t.field;
                                    console.log(l);
                                    layui.$.post("codes?id=" + e.data.uid, l, function (res) {
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
                }
            }),

        e("paycenteruser", {})
    });
