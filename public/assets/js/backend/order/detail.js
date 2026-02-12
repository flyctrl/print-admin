define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/detail/index' + location.search,
                    add_url: 'order/detail/add',
                    edit_url: 'order/detail/edit',
                    del_url: 'order/detail/del',
                    multi_url: 'order/detail/multi',
                    import_url: 'order/detail/import',
                    table: 'order_detail',
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
                         {field: 'user.id', title: __('用户ID')},
                        {field: 'order.order_num', title: __('Order.order_num'), operate: 'LIKE'},
                        {field: 'order.name', title: __('Order.name'), operate: 'LIKE'},
                        {field: 'order.phone', title: __('Order.phone'), operate: 'LIKE'},
                        // {field: 'information', title: __('Information'), searchList: {"0":__('Information 0'),"1":__('Information 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'order_id', title: __('Order_id')},
                        {field: 'file', title: __('File'), operate: false, formatter: Table.api.formatter.file},
                        {field: 'source_file', title: __('源文件'), operate: false, formatter: Table.api.formatter.file},
                        {field: 'page_turning', title: __('Page_turning'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'paper', title: __('Paper'), operate: 'LIKE'},
                        {field: 'brand', title: __('Brand'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'single_double', title: __('Single_double'), searchList: {"0":__('Single_double 0'),"1":__('Single_double 1')}, formatter: Table.api.formatter.normal},
                        {field: 'colour', title: __('Colour'), operate: 'LIKE'},
                        {field: 'color', title: __('Color'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'binding_type', title: __('Binding_type'), operate: 'LIKE'},
                        {field: 'film_covering', title: __('Film_covering'), operate: 'LIKE'},
                        {field: 'cover_type', title: __('Cover_type'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), searchList: {"0":__('Type 0'),"1":__('Type 1'),"2":__('Type 2')}, formatter: Table.api.formatter.normal},
                        {field: 'cover_image', title: __('Cover_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'cover_image', title: __('封面文字'), operate: false,},
                        {field: 'reduction_printing', title: __('Reduction_printing'), operate: 'LIKE'},
                        {field: 'print_range', title: __('Print_range'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'num', title: __('Num')},
                        {field: 'page', title: __('Page')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        // {field: 'commission', title: __('Commission')},
                        // {field: 'template_file', title: __('Template_file'), operate: false, formatter: Table.api.formatter.file},
                        // {field: 'share_id', title: __('Share_id')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                       
                       
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
