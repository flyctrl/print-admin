define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    // Fast.config.openArea = ['100%', '100%'];
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/prints/index' + location.search,
                    add_url: 'order/prints/add',
                    edit_url: 'order/prints/edit',
                    del_url: 'order/prints/del',
                    multi_url: 'order/prints/multi',
                    import_url: 'order/prints/import',
                    table: 'order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('用户ID')},
                        {field: 'address_status', title: __('是否修改地址'), searchList: {"-1":__('不修改'),"0":__('待审核'),"1":__('已修改'),"2":__('已驳回')}, formatter: Table.api.formatter.status},
                        {field: 'info', title: __('收货信息'), operate: 'LIKE'},
                        // {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        // {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'order_num', title: __('Order_num'), operate: 'LIKE'},
                        {field: 'zip_file', title: __('压缩包'), operate: false, formatter:Table.api.formatter.file},
                        {field: 'express_num', title: __('Express_num'), operate: 'LIKE'},
                        {field: 'zip', title: __('是否打包'), formatter:Table.api.formatter.toggle},
                        {field: 'file_num', title: __('File_num')},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN'},
                        {field: 'ticket_price', title: __('Ticket_price'), operate:'BETWEEN'},
                        {field: 'balance', title: __('Balance'), operate:'BETWEEN'},
                        {field: 'total_price', title: __('Total_price'), operate:'BETWEEN'},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'freight', title: __('Freight'), operate:'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"0":__('Pay_type 0'),"1":__('Pay_type 1'),"2":__('Pay_type 2')}, formatter: Table.api.formatter.normal},


                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4'),"5":__('Pay_status 5'),"6":__('Pay_status 6')}, formatter: Table.api.formatter.status},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'),


                            table: table,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('查看清单'),
                                    title: __('查看清单'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'order/order/detail',
                                    extend: 'data-area=\'["100%", "100%"]\''
                                },
                                {
                                    name: 'detail',
                                    text: __('退款'),
                                    title: __('退款'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'order/refund/refund',
                                    // visible:function (row){
                                    //     if (row.pay_status==4 && row.pay_type==0){
                                    //         return true;
                                    //     }
                                    // }

                                },
                                // {
                                //     name: 'ajax',
                                //     text: __('打包'),
                                //     title: __('打包'),
                                //     classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                //     // icon: 'fa fa-magic',
                                //     url: 'order/order/zip',
                                //     confirm: '确认打包？',
                                //     success: function (data, ret) {
                                //         Layer.alert(ret.msg);
                                //         $('.btn-refresh').trigger('click');
                                //         // Layer.alert(ret.msg + ",返回数据：" + JSON.stringify(data));
                                //         //如果需要阻止成功提示，则必须使用return false;
                                //
                                //     },
                                //     error: function (data, ret) {
                                //         console.log(data, ret);
                                //         Layer.alert(ret.msg);
                                //         return false;
                                //     }
                                // },



                            ],
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });
            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');
                console.log(options);
                var columns = [];
                $.each(options.columns[0], function (i, j) {
                    if (j.field && !j.checkbox && j.visible && j.field != 'operate') {
                        columns.push(j.field);
                    }
                });
                var search = options.queryParams({});
                $("input[name=search]", layero).val(options.searchText);
                $("input[name=ids]", layero).val(ids);
                $("input[name=filter]", layero).val(search.filter);
                $("input[name=op]", layero).val(search.op);
                $("input[name=columns]", layero).val(columns.join(','));
                $("form", layero).submit();
            };
            $(document).on("click", ".btn-export", function () {
                var ids = Table.api.selectedids(table);
                var page = table.bootstrapTable('getData');
                var all = table.bootstrapTable('getOptions').totalRows;
                console.log(ids, page, all);
                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("order/prints/export") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
                    title: '导出数据',
                    area: ['440px', '260px'],
                    btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)", "全部(" + all + "条)"],
                    success: function (layero, index) {
                        $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                    }
                    , yes: function (index, layero) {
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn2: function (index, layero) {
                        var ids = [];
                        $.each(page, function (i, j) {
                            ids.push(j.id);
                        });
                        submitForm(ids.join(","), layero);
                        return false;
                    }
                    ,
                    btn3: function (index, layero) {
                        submitForm("all", layero);
                        return false;
                    }
                })
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {

            Controller.api.bindevent();
        },
        import: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"),function(ret,data){
                    if (ret){
                        let html = '';
                        for (i=0;i<ret.success.length;i++){
                            html +='成功：'+ret.success[i]+'<br>';
                        }
                        for (i=0;i<ret.error.length;i++){
                            html +='失败：'+ret.error[i]+'<br>';
                        }
                        Layer.open({
                            title: '提示',
                            content: html,
                            yes:function(){
                                Fast.api.close();
                            }
                        });
                        console.log(ret);
                        console.log(data);
                        return false;
                    }

                },function(){},function(){});
            }
        }
    };
    return Controller;
});
