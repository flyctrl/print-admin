define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'prints/price/index' + location.search,
                    add_url: 'prints/price/add',
                    edit_url: 'prints/price/edit',
                    del_url: 'prints/price/del',
                    multi_url: 'prints/price/multi',
                    import_url: 'prints/price/import',
                    table: 'price',
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
                        {field: 'paper_cover_price', title: __('Paper_cover_price'), operate:'BETWEEN'},
                        {field: 'art_paper_price', title: __('Art_paper_price'), operate:'BETWEEN'},
                        {field: 'staple_price', title: __('Staple_price'), operate:'BETWEEN'},
                        {field: 'ring_mounting_price', title: __('Ring_mounting_price'), operate:'BETWEEN'},
                        {field: 'film_covering', title: __('Film_covering'), operate:'BETWEEN'},
                        {field: 'first_weight_price', title: __('First_weight_price'), operate:'BETWEEN'},
                        {field: 'continuation_weight_price', title: __('Continuation_weight_price'), operate:'BETWEEN'},
                        {field: 'random_color_price', title: __('Random_color_price'), operate:'BETWEEN'},
                        {field: 'select_color_price', title: __('Select_color_price'), operate:'BETWEEN'},
                        {field: 'cover_price', title: __('Cover_price'), operate:'BETWEEN'},
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
