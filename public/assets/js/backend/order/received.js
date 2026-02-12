define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/received/index' + location.search,
                    add_url: 'order/received/add',
                    edit_url: 'order/prints/edit',
                    del_url: 'order/received/del',
                    multi_url: 'order/received/multi',
                    import_url: 'order/received/import',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                         {field: 'address_status', title: __('是否修改地址'), searchList: {"-1":__('不修改'),"0":__('待审核'),"1":__('已修改'),"2":__('已驳回')}, formatter: Table.api.formatter.status},
                        {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'order_num', title: __('Order_num'), operate: 'LIKE'},
                        {field: 'zip_file', title: __('压缩包'), operate: false, formatter: Table.api.formatter.file},
                        {field: 'express_num', title: __('Express_num'), operate: 'LIKE'},
                        {field: 'download', title: __('是否下载'), searchList: {"0":__('未下载'),"1":__('已下载')}, formatter: Table.api.formatter.normal},
                        {field: 'file_num', title: __('File_num')},
                        {field: 'zip', title: __('是否打包'), formatter:Table.api.formatter.toggle},
                        {field: 'remark', title: __('备注'), operate:'LIKE'},
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
                                    name: 'ajax',
                                    text: __('压缩'),
                                    title: __('压缩'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'order/order/zip',
                                    confirm: '确认打包？',
                                    success: function (data, ret) {
                                        Layer.alert(ret.msg);
                                        $('.btn-refresh').trigger('click');
                                        //如果需要阻止成功提示，则必须使用return false;

                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }
                                },
                                {
                                    name: 'ajax',
                                    text: __('下载'),
                                    title: __('下载'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    // icon: 'fa fa-magic',
                                    url: 'order/order/download',
                                    confirm: '确认设为已下载？',
                                    success: function (data, ret) {
                                        location.href=data;
                                        $('.btn-refresh').trigger('click');
                                        //

                                        // Layer.alert(ret.msg + ",返回数据：" + JSON.stringify(data));
                                        //如果需要阻止成功提示，则必须使用return false;

                                    },
                                    error: function (data, ret) {
                                        console.log(data, ret);
                                        Layer.alert(ret.msg);
                                        return false;
                                    }

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
                                {
                                    name: 'detail',
                                    text: __('添加备注'),
                                    title: __('添加备注'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'order/order/remark',
                                },


                            ],
                            table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
