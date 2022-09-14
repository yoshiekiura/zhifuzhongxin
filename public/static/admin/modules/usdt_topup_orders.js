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
                    field: "uid",
                    width: 60,
                    title: "UID"
                },{
                    field: "username",
                    width: 150,
                    title: "用户名"
                },{
                    field: "trade_no",
                    width: 180,
                    title: "订单号"
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
        i.render({
                elem: "#app-balance-list",
                url: "getCenterBalanceList",
                //自定义响应字段
                response: {
                    statusName: 'code' //数据状态的字段名称
                    ,statusCode: 1 //数据状态一切正常的状态码
                    ,msgName: 'msg' //状态信息的字段名称
                    ,dataName: 'data' //数据详情的字段名称
                },
                cols: [[
                    {
                        field: "uid",
                        width: 100,
                        title: "商户UID",
                    },
                    {
                        field: "username",
                        width: 100,
                        title: "商户名称",
                    },
                    {
                        field: "usdt_enable",
                        width: 120,
                        title: "USDT可用余额",
                        sort:true
                    },
                    {
                        field: "usdt_disable",
                        width: 120,
                        title: "USDT冻结余额"
                    },
                    {
                        field: "pl_enable",
                        width: 120,
                        title: "跑量可用余额",
                        // sort:true
                    },
                    {
                        field: "pl_disable",
                        width: 120,
                        title: "跑量冻结余额"
                    },
                    {
                        field: "status",
                        title: "状态",
                        templet: "#buttonTpl",
                        width: 80,
                        align: "center"
                    },
                    {
                        field: "create_time",
                        title: "创建时间",
                        width: 180,
                        sort: !0,
                    },
                    {
                        title: "操作",
                        minWidth: 220,
                        align: "center",
                        // fixed: "right",
                        toolbar: "#table-balance-list"
                    }]],
                page: !0,
                limit: 10,
                limits: [10, 15, 20, 25, 30],
                text: "对不起，加载出现异常！"
            }),
        i.on("tool(app-balance-list)",
            function(e) {
                if ("details" === e.event) {
                    t(e.tr);
                    layer.open({
                        type: 2,
                        title: "账户明细",
                        content: "usdtBalanceDetails.html?id=" + e.data.uid,
                        maxmin: !0,
                        area:  ['80%', '60%'],
                        yes: function(f, t) {},
                        success: function(e, t) {}
                    })
                }else if("op_balance" === e.event){  //增加用户资金余额
                    layer.prompt({
                            formType: 1,
                            title: "敏感操作，请验证口令",
                        },
                        function(d, f) {
                            // console.log(i);return false;
                            //检测口令
                            t.ajax({
                                url: '/admin/api/checkOpCommand?command='+ d,
                                method:'POST',
                                success:function (res) {
                                    if (res.code == 1){
                                        //口令正确
                                        layer.close(d); //关闭弹层
                                        t(e.tr);
                                        layer.open({
                                            type: 2
                                            ,title: '增减余额'
                                            ,content: 'changeUsdtBalance.html?uid=' + e.data.uid
                                            ,maxmin: true
                                            ,area: ['80%','60%']
                                            ,btn: ['确定', '取消']
                                            ,yes: function(index, layero){
                                                var iframeWindow = window['layui-layer-iframe'+ index]
                                                    ,submitID = 'app-user-manage-submit'
                                                    ,submit = layero.find('iframe').contents().find('#'+ submitID);

                                                //监听提交
                                                iframeWindow.layui.form.on('submit('+ submitID +')', function(obj){
                                                    var field = obj.field; //获取提交的字段

                                                    //提交 Ajax 成功后，静态更新表格中的数据
                                                    t.ajax({
                                                        url:'changeUsdtBalance.html?uid=' + e.data.uid,
                                                        method:'POST',
                                                        data:field,
                                                        success:function (res) {
                                                            if (res.code == 1){
                                                                layer.closeAll();
                                                                i.reload('app-balance-list');
                                                            }else{
                                                                layer.msg(res.msg, {icon: 2,time: 1500});
                                                            }
                                                        }
                                                    });
                                                });
                                                submit.trigger('click');
                                            }
                                        });
                                    }else{
                                        layer.msg(res.msg,{icon:2,time:1500});
                                        layer.close(d); //关闭弹层
                                    }
                                }
                            });
                        });
                }
            }),
        i.render({
                elem: "#app-balance-details-list",
                url: 'usdtBalanceDetails',
                //添加请求字段
                where: {
                    uid:  t("input[ name='uid' ] ").val()
                },
                //自定义响应字段
                response: {
                    statusName: 'code' //数据状态的字段名称
                    ,statusCode: 1 //数据状态一切正常的状态码
                    ,msgName: 'msg' //状态信息的字段名称
                    ,dataName: 'data' //数据详情的字段名称
                },
                cols: [[
                    {
                        field: "type",
                        templet: "#buttonType",
                        width: 100,
                        title: "资金类型"
                    },
                    {
                        field: "change_category_text",
                        width: 100,
                        title: "流水类型"
                    },
                    {
                        field: "preinc",
                        width: 100,
                        title: "变动前金额",
                        style: "color:red"
                    },
                    {
                        field: "increase",
                        width: 100,
                        title: "增加金额",
                        style: "color:red"
                    },
                    {
                        field: "reduce",
                        width: 100,
                        title: "减少金额",
                        style: "color:red"
                    },
                    {
                        field: "suffixred",
                        width: 100,
                        title: "变动后金额",
                        style: "color:red"
                    },
                    {
                        field: "remarks",
                        title: "变动备注"
                    },
                    {
                        field: "update_time",
                        width: 200,
                        title: "更新时间",
                        //templet: function(d) {return u.toDateString(d.update_time*1000); }
                    }]],
                page: {
                    limit: 10,
                    limits: [10, 15, 20, 25, 30]
                },
                text: "对不起，加载出现异常！"
            }),
        e("guarantee_orders", {})
    });
