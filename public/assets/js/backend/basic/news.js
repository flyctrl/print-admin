define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'basic/news/index' + location.search,
                    add_url: 'basic/news/add',
                    edit_url: 'basic/news/edit',
                    del_url: 'basic/news/del',
                    multi_url: 'basic/news/multi',
                    import_url: 'basic/news/import',
                    table: 'news_ticket',
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
                        {field: 'ticket_name', title: __('Ticket_name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'type', title: __('Type'), searchList: {"0":__('满减'),"1":__('折扣')}, formatter: Table.api.formatter.normal},
                        {field: 'limitation_price', title: __('Limitation_price'), operate:'BETWEEN'},
                        {field: 'deduction_price', title: __('Deduction_price'), operate:'BETWEEN'},
                        {field: 'discount', title: __('折扣'), operate:'BETWEEN'},
                        {field: 'end_time', title: __('End_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'user_ids', title: __('User_ids')},
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
                $('#c-type').change(function () {
                    if($(this).val() == 0){
                        $('.discount').hide();
                        $('.deduction').show();
                    }else{
                        $('.discount').show();
                        $('.deduction').hide();
                    }
                });
                $(function (){
                    var type = $('#c-type').val();
                    if(type == 0){
                        $('.discount').hide();
                        $('.deduction').show();
                    }else{
                        $('.discount').show();
                        $('.deduction').hide();
                    }
                });
            }
        }
    };
    return Controller;
});
