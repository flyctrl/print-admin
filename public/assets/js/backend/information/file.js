define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'information/file/index' + location.search,
                    add_url: 'information/file/add',
                    edit_url: 'information/file/edit',
                    del_url: 'information/file/del',
                    multi_url: 'information/file/multi',
                    import_url: 'information/file/import',
                    table: 'file',
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
                        // {field: 'information_id', title: __('Information_id')},
                        {field: 'file', title: __('File'), operate: false, formatter: Table.api.formatter.file},
                        {field: 'paper.name', title: __('Paper.name'), operate: 'LIKE'},
                        {field: 'single_double', title: __('Single_double'), searchList: {"0":__('Single_double 0'),"1":__('Single_double 1')}, formatter: Table.api.formatter.normal},
                        {field: 'colour.name', title: __('Colour.name'), operate: 'LIKE'},
                        {field: 'binding_type', title: __('Binding_type'), searchList: {"0":__('Binding_type 0'),"1":__('Binding_type 1'),"2":__('Binding_type 2'),"3":__('Binding_type 3'),"4":__('Binding_type 4')}, formatter: Table.api.formatter.normal},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'commission_switch', title: __('是否返佣'), table: table, formatter: Table.api.formatter.toggle},
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
