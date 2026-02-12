define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'prints/prints/index' + location.search,
                    add_url: 'prints/prints/add',
                    edit_url: 'prints/prints/edit',
                    del_url: 'prints/prints/del',
                    multi_url: 'prints/prints/multi',
                    import_url: 'prints/prints/import',
                    table: 'prints',
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
                        {field: 'brand.name', title: __('Brand.name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'paper.name', title: __('Paper.name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'colour.name', title: __('Colour.name'), operate: 'LIKE'},
                        {field: 'single_double', title: __('Single_double'), searchList: {"0":__('Single_double 0'),"1":__('Single_double 1')}, formatter: Table.api.formatter.normal},
                        {field: 'price', title: __('Price')},
                        {field: 'commission_switch', title: __('Commission_switch'), table: table, formatter: Table.api.formatter.toggle},
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
