define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'orders/index' + location.search,
                    add_url: 'orders/add',
                    edit_url: 'orders/edit',
                    del_url: 'orders/del',
                    multi_url: 'orders/multi',
                    import_url: 'orders/import',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'address_id', title: __('Address_id')},
                        {field: 'pay_status', title: __('Pay_status'), searchList: {"0":__('Pay_status 0'),"1":__('Pay_status 1'),"2":__('Pay_status 2'),"3":__('Pay_status 3'),"4":__('Pay_status 4'),"5":__('Pay_status 5'),"6":__('Pay_status 6')}, formatter: Table.api.formatter.status},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'order_num', title: __('Order_num'), operate: 'LIKE'},
                        {field: 'file_num', title: __('File_num')},
                        {field: 'order_price', title: __('Order_price'), operate:'BETWEEN'},
                        {field: 'user_ticket_id', title: __('User_ticket_id')},
                        {field: 'ticket_price', title: __('Ticket_price'), operate:'BETWEEN'},
                        {field: 'balance', title: __('Balance'), operate:'BETWEEN'},
                        {field: 'total_price', title: __('Total_price'), operate:'BETWEEN'},
                        {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'freight', title: __('Freight'), operate:'BETWEEN'},
                        {field: 'pay_type', title: __('Pay_type'), searchList: {"0":__('Pay_type 0'),"1":__('Pay_type 1'),"2":__('Pay_type 2')}, formatter: Table.api.formatter.normal},
                        // {field: 'express_id', title: __('Express_id'), operate: 'LIKE'},
                        {field: 'express_num', title: __('Express_num'), operate: 'LIKE'},
                        // {field: 'invoice', title: __('Invoice'), searchList: {"0":__('Invoice 0'),"1":__('Invoice 1'),"2":__('Invoice 2')}, formatter: Table.api.formatter.normal},
                        // {field: 'look_up_id', title: __('Look_up_id')},
                        // {field: 'invoice_time', title: __('Invoice_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'reason', title: __('Reason'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'download', title: __('Download')},
                        // {field: 'transaction_id', title: __('Transaction_id'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'zip_file', title: __('Zip_file'), operate: false, formatter: Table.api.formatter.file},
                        // {field: 'delete', title: __('Delete')},
                        // {field: 'zip', title: __('Zip')},
                        // {field: 'import', title: __('Import')},
                        // {field: 'remark', title: __('Remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'user.id', title: __('User.id')},
                        // {field: 'user.openid', title: __('User.openid'), operate: 'LIKE'},
                        // {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'user.avatar_image', title: __('User.avatar_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'user.balance', title: __('User.balance'), operate:'BETWEEN'},
                        // {field: 'user.parent_id', title: __('User.parent_id')},
                        // {field: 'user.qr_code', title: __('User.qr_code'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'user.scan', title: __('User.scan')},
                        // {field: 'user.create_time', title: __('User.create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
