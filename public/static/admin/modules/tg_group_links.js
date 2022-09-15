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
        i.render({
            elem: "#app-tg-group-links-list",
            url: "getLinksList",
            //自定义响应字段
            response: {
                statusCode: 1
            },
            cols: [
                [{
                    field: "id",
                    width: 80,
                    title: "ID",
                    sort: !0
                },{
                    field: "link_address",
                    width: 250,
                    title: "链接"
                },{
                    field: "trade_no",
                    width: 150,
                    title: "匹配订单号"
                },{
                    field: "allocation_type",
                    width: 180,
                    title: "匹配类型"
                },{
                    field: "status",
                    width: 90,
                    templet:'#statusTpl',
                    title: '状态'
                },{
                    title: "操作",
                    align: "center",
                    minWidth:200,
                    fixed: "right",
                    toolbar: "#table-tg-group-links"
                }
                ]
            ],
            page: !0,
            limit: 10,
            limits: [10, 15, 20, 25, 30],
            text: "对不起，加载出现异常！"
        }),
        i.on("tool(app-tg-group-links-list)",
            function (e) {
                if ("edit" === e.event) {
                    t(e.tr);
                    layer.open({
                        type: 2,
                        title: "编辑用户",
                        content: "editLink.html?id=" + e.data.id,
                        maxmin: !0,
                        maxmin: !0, area: ['80%', '60%'],
                        btn: ["确定", "取消"],
                        yes: function (f, t) {
                            var l = window["layui-layer-iframe" + f],
                                r = "app-edit-link-submit",
                                n = t.find("iframe").contents().find("#" + r);
                            l.layui.form.on("submit(" + r + ")",
                                function (t) {
                                    var l = t.field;
                                    layui.$.post("editLink", l, function (res) {
                                        if (res.code == 1) {
                                            i.reload('app-tg-group-links-list');
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
        e("tg_group_links", {})
    });
