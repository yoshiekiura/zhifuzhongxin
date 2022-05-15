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
                    width: 150,
                    title: "用户名"
                }, {
                    field: "alias_name",
                    width: 150,
                    title: "别名"
                }, {
                    field: "user_type",
                    width: 150,
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
        }),
            i.on("tool(app-pay-code-list)",
                function (t) {
                    var e = t.data;
                    "del" === t.event ? layer.confirm("确定删除此二维码？",
                        function (d) {
                            q.ajax({
                                url: 'delPayCode?id=' + e.id,
                                method: 'POST',
                                success: function (res) {
                                    if (res.code == 1) {
                                        t.del()
                                    }
                                    layer.msg(res.msg, {icon: res.code == 1 ? 1 : 2, time: 1500});
                                    layer.close(d); //关闭弹层
                                }
                            });
                        }) : "edit" === t.event && layer.open({
                        type: 2,
                        title: "编辑文章",
                        content: "edit.html?id=" + e.id,
                        maxmin: !0,
                        maxmin: !0, area: ['80%', '60%'],
                        btn: ["确定", "取消"],
                        yes: function (e, i) {
                            var l = window["layui-layer-iframe" + e],
                                a = i.find("iframe").contents().find("#app-article-form-edit");
                            l.layui.form.on("submit(app-article-form-edit)",
                                function (i) {
                                    var l = i.field;
                                    layui.$.post("edit", l, function (res) {
                                        if (res.code == 1) {
                                            //更新数据表
                                            t.update({
                                                label: l.label,
                                                title: l.title,
                                                author: l.author,
                                                status: l.status
                                            }),
                                                n.render(),
                                                layer.close(e)
                                        }
                                        layer.msg(res.msg, {icon: res.code == 1 ? 1 : 2, time: 1500});
                                    });
                                }),
                                a.trigger("click")
                        }
                    })
                });



    });
