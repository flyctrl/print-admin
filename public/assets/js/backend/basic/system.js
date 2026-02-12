define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'basic/system/index' + location.search,
                    add_url: 'basic/system/add',
                    edit_url: 'basic/system/edit',
                    // del_url: 'basic/system/del',
                    multi_url: 'basic/system/multi',
                    import_url: 'basic/system/import',
                    table: 'system',
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
                        {field: 'password', title: __('Password'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'image', title: __('微信二维码'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'pay_proportion', title: __('Pay_proportion')},
                        {field: 'share_proportion', title: __('Share_proportion')},
                        {field: 'commission', title: __('开启新用户优惠券'), table: table, formatter: Table.api.formatter.toggle},
                        {field: 'button', title: __('开启淘宝支付文字按钮'), table: table, formatter: Table.api.formatter.toggle},
                        {field: 'agreement_file', title: __('签约协议'), operate: false, formatter: Table.api.formatter.file},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
