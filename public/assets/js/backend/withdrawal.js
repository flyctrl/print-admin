define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'withdrawal/index' + location.search,
                    add_url: 'withdrawal/add',
                    edit_url: 'withdrawal/edit',
                    del_url: 'withdrawal/del',
                    multi_url: 'withdrawal/multi',
                    import_url: 'withdrawal/import',
                    table: 'user_withdrawal',
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
                        {field: 'order_num', title: __('Order_num'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('待用户领取')}, formatter: Table.api.formatter.status},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'),
                            buttons: [
                                {
                                    name: 'addtabs',
                                    text: __('确认提现'),
                                    title: __('确认提现'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    confirm: '确认提现？',
                                    icon: 'fa fa-magic',
                                    url: 'withdrawal/confirm',
                                    visible:function (row){
                                        if (row.status==0){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    },
                                    success: function (data, ret) {
                                        $('.btn-refresh').trigger('click');
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    },

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
