define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'signs/index' + location.search,
                    add_url: 'signs/add',
                    edit_url: 'signs/edit',
                    del_url: 'signs/del',
                    multi_url: 'signs/multi',
                    import_url: 'signs/import',
                    table: 'signs',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'sign_image', title: __('Sign_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'card_front_image', title: __('Card_front_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'card_back_image', title: __('Card_back_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
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
                        {field: 'operate', title: __('Operate'),
                            buttons: [
                                {
                                    name: 'addtabs',
                                    text: __('审核'),
                                    title: __('审核'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    confirm: '确认审核？',
                                    icon: 'fa fa-magic',
                                    url: 'signs/pass',
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
                                {
                                    name: 'addtabs',
                                    text: __('驳回'),
                                    title: __('驳回'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    confirm: '确认审核？',
                                    icon: 'fa fa-magic',
                                    url: 'signs/reject',
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
