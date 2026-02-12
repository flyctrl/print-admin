define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'information/information/index' + location.search,
                    add_url: 'information/information/add',
                    edit_url: 'information/information/edit',
                    del_url: 'information/information/del',
                    multi_url: 'information/information/multi',
                    import_url: 'information/information/import',
                    table: 'information',
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
                        {field: 'one.name', title: __('一级分类名称'), operate: 'LIKE'},
                        {field: 'two.name', title: __('一级分类名称'), operate: 'LIKE',},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'),

                            buttons: [
                                {
                                    name: 'addtabs',
                                    text: __('文件管理'),
                                    title: __('文件管理'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-folder-o',
                                    url: 'information/file/index',

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

                $("#c-category_two_id").data("params", function (obj) {
                    //obj为SelectPage对象
                    return {custom: {parent_id: $("#c-category_one_id").val()}};
                });
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
