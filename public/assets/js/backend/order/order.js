define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    // Fast.config.openArea = ['100%', '100%'];
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/order/index' + location.search,
                    add_url: 'order/order/add',
                    edit_url: 'order/prints/edit',
                    del_url: 'order/order/del',
                    multi_url: 'order/order/multi',
                    import_url: 'order/order/import',
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
                        {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'order_num', title: __('Order_num'), operate: 'LIKE'},
                        {field: 'file_num', title: __('File_num')},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN'},
                        {field: 'ticket_price', title: __('Ticket_price'), operate:'BETWEEN'},
                        {field: 'balance', title: __('Balance'), operate:'BETWEEN'},
                        {field: 'total_price', title: __('Total_price'), operate:'BETWEEN'},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'freight', title: __('Freight'), operate:'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"0":__('Pay_type 0'),"1":__('Pay_type 1'),"2":__('Pay_type 2')}, formatter: Table.api.formatter.normal},
                        // {field: 'express_id', title: __('Express_id'), operate: 'LIKE'},
                        // {field: 'express_num', title: __('Express_num'), operate: 'LIKE'},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4'),"5":__('Pay_status 5'),"6":__('Pay_status 6')}, formatter: Table.api.formatter.status},
                        // {field: 'invoice', title: __('Invoice'), searchList: {"0":__('Invoice 0'),"1":__('Invoice 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'reason', title: __('Reason'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'user.id', title: __('User.id')},
                        // {field: 'user.openid', title: __('User.openid'), operate: 'LIKE'},
                        //
                        // {field: 'user.avatar_image', title: __('User.avatar_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'user.balance', title: __('User.balance'), operate:'BETWEEN'},
                        // {field: 'user.parent_id', title: __('User.parent_id')},
                        // {field: 'user.qr_code', title: __('User.qr_code'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'user.create_time', title: __('User.create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
                                    name: 'detail',
                                    text: __('设置折扣'),
                                    title: __('设置折扣'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'order/order/discount',
                                    visible: function (row) {
                                        if (row.pay_type==0){
                                            return true;
                                        }else{
                                            return false;

                                        }

                                    }
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
        remark: function () {
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
