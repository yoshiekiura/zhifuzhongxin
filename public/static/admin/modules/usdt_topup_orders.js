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
            elem: "#app-usdt-topup-orders-list",
            url: "getTopupList",
            //自定义响应字段
            response: {
                statusCode: 1
            },
            cols: [
                [{
                    field: "id",
                    width: 60,
                    title: "ID",
                    sort: !0
                },{
                    field: "trade_no",
                    width: 180,
                    title: "订单号"
                },{
                    field: "uid",
                    width: 60,
                    title: "UID"
                },{
                    field: "username",
                    width: 150,
                    title: "用户名"
                },{
                    field: "usdt_sum",
                    width: 100,
                    title: "USDT数量",
                    templet: function (d) {
                        return "<span  style='color:red;font-weight: bold'>"+ d.usdt_sum +"</span>"
                    }
                },{
                    field: "complete_type",
                    templet:"#completeTpl",
                    width: 80,
                    title: "到账类型",
                },{
                    field: "status",
                    width: 80,
                    templet:"#statusTpl",
                    title: "状态",
                },{
                    field: "create_time",
                    width: 200,
                    title: "创建时间",
                },{
                    title: "操作",
                    align: "center",
                    minWidth:250,
                    fixed: "right",
                    toolbar: "#table-usdt-topup-order"
                }
                ]
            ],
            page: !0,
            limit: 10,
            limits: [10, 15, 20, 25, 30],
            text: "对不起，加载出现异常！"
        })
        i.on("tool(app-usdt-topup-orders-list)",
            function (e) {
                if ("manual-finish" === e.event) {
                    layer.prompt({
                        formType: 1,
                        title: "敏感操作，请验证口令"
                    }, function(d, f) {
                        //检测口令
                        t.ajax({
                            url: '/admin/api/checkOpCommand?command='+ d,
                            method:'POST',
                            success:function (res) {
                                if (res.code == 1){
                                    //口令正确
                                    layer.close(f); //关闭弹层
                                    t(e.tr);
                                    layer.open({
                                        type: 2,
                                        title: "手动到账",
                                        content: "manualFinish.html?id=" + e.data.id,
                                        maxmin: !0,
                                        area: ['60%','40%'],
                                        btn: ["确定", "取消"],
                                        yes: function(e1, layero) {
                                            var admin_note= t.trim(layero.find('iframe').contents().find('#admin_note').val());
                                            if(admin_note===''){
                                                layer.msg('手动成功备注不能为空',{icon:2,time:1500});
                                            }
                                            if(admin_note.length >255){
                                                layer.msg('备注最长255个字符',{icon:2,time:1500});
                                            }
                                            //正式补单操作
                                            t.ajax({
                                                url: 'manualFinish?id='+ e.data.id,
                                                method:'POST',
                                                data:{admin_note:admin_note},
                                                success:function (res) {
                                                    if (res.code == 1){
                                                        layer.closeAll();
                                                        i.reload('app-usdt-topup-orders-list');
                                                    }else{
                                                        layer.msg(res.msg, {icon: 2,time: 1500});
                                                    }
                                                }
                                            });
                                        },
                                    })
                                }else{
                                    layer.msg(res.msg,{icon:2,time:1500});
                                    layer.close(d); //关闭弹层
                                }
                            }
                        });
                    });
                }  else if ("success-order" === e.event) {
                    layer.prompt({
                        formType: 1,
                        title: "敏感操作，请验证口令"
                    }, function(d, f) {
                        //检测口令
                        t.ajax({
                            url: '/admin/api/checkOpCommand?command='+ d,
                            method:'POST',
                            success:function (res) {
                                if (res.code == 1){
                                    //口令正确
                                    layer.close(f); //关闭弹层
                                    t(e.tr);
                                    layer.open({
                                        type: 2,
                                        title: "手动成功详情",
                                        content: "successOrder.html?id=" + e.data.id,
                                        maxmin: !0,
                                        area: ['60%','40%'],
                                        btn: ["确定", "取消"],
                                        yes: function(e1, layero) {
                                            var admin_success_note= t.trim(layero.find('iframe').contents().find('#admin_success_note').val());
                                            if(admin_success_note===''){
                                                layer.msg('手动成功备注不能为空',{icon:2,time:1500});
                                            }
                                            if(admin_success_note.length >255){
                                                layer.msg('备注最长255个字符',{icon:2,time:1500});
                                            }
                                            //正式补单操作
                                            t.ajax({
                                                url: 'successOrder?id='+ e.data.id,
                                                method:'POST',
                                                data:{admin_success_note:admin_success_note},
                                                success:function (res) {
                                                    if (res.code == 1){
                                                        layer.closeAll();
                                                        i.reload('app-guarantee-orders-list');
                                                    }else{
                                                        layer.msg(res.msg, {icon: 2,time: 1500});
                                                    }
                                                }
                                            });
                                        },
                                    })
                                }else{
                                    layer.msg(res.msg,{icon:2,time:1500});
                                    layer.close(d); //关闭弹层
                                }
                            }
                        });
                    });
                }
            }),
        e("usdt_topup_orders", {})
    });
