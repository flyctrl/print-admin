define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/express/index' + location.search,
                    add_url: 'order/express/add',
                    // edit_url: 'order/express/edit',
                    // del_url: 'order/express/del',
                    multi_url: 'order/express/multi',
                    import_url: 'order/express/import',
                    table: 'order_express',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'order.order_num', title: __('Order.order_num'), operate: 'LIKE'},
                        {field: 'order.name', title: __('修改前姓名'), operate: 'LIKE'},
                        {field: 'order.phone', title: __('修改前手机号'), operate: 'LIKE'},
                        {field: 'order.address', title: __('修改前地址'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'name', title: __('修改后姓名'), operate: 'LIKE'},
                        {field: 'phone', title: __('修改后手机号'), operate: 'LIKE'},
                        {field: 'address', title: __('修改后地址'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'order.id', title: __('Order.id')},
                        // {field: 'order.user_id', title: __('Order.user_id')},
                        // {field: 'order.address_id', title: __('Order.address_id')},


                        // {field: 'order.file_num', title: __('Order.file_num')},
                        // {field: 'order.order_price', title: __('Order.order_price'), operate:'BETWEEN'},
                        // {field: 'order.user_ticket_id', title: __('Order.user_ticket_id')},
                        // {field: 'order.ticket_price', title: __('Order.ticket_price'), operate:'BETWEEN'},
                        // {field: 'order.balance', title: __('Order.balance'), operate:'BETWEEN'},
                        // {field: 'order.total_price', title: __('Order.total_price'), operate:'BETWEEN'},
                        // {field: 'order.pay_time', title: __('Order.pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'order.freight', title: __('Order.freight'), operate:'BETWEEN'},
                        // {field: 'order.pay_type', title: __('Order.pay_type')},
                        // {field: 'order.express_id', title: __('Order.express_id'), operate: 'LIKE'},
                        // {field: 'order.express_num', title: __('Order.express_num'), operate: 'LIKE'},
                        // {field: 'order.pay_status', title: __('Order.pay_status'), formatter: Table.api.formatter.status},
                        // {field: 'order.invoice', title: __('Order.invoice')},
                        // {field: 'order.look_up_id', title: __('Order.look_up_id')},
                        // {field: 'order.invoice_time', title: __('Order.invoice_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'order.reason', title: __('Order.reason'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'order.image', title: __('Order.image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'order.download', title: __('Order.download')},
                        // {field: 'order.transaction_id', title: __('Order.transaction_id'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'order.zip_file', title: __('Order.zip_file'), operate: false, formatter: Table.api.formatter.file},
                        // {field: 'order.delete', title: __('Order.delete')},
                        // {field: 'order.create_time', title: __('Order.create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
