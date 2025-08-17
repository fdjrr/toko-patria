<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" data-options="
        url: '{{ route('product_categories.getCategory') }}',
        toolbar: '#toolbar',
        pagination: true,
        rownumbers: true,
        fitColumns: true,
        idField: 'id',
        singleSelect: true,
        fit: true,
        multiSort: true
    ">
        <thead>
            <tr>
                <th data-options="field:'parent_name',sortable:true">Parent</th>
                <th data-options="field:'name',sortable:true">Name</th>
            </tr>
        </thead>
    </table>

    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newCategory()">
            New Category
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
            onclick="editCategory()">
            Edit Category
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="destroyCategory()">
            Remove Category
        </a>
    </div>

    <div id="dlg" class="easyui-window" style="width:500px" data-options="closed:true,footer:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom: 10px">
                <input name="parent_id" id="parent_id" style="width:100%" />
            </div>
            <div style="margin-bottom: 10px">
                <input name="name" class="easyui-textbox" required="true" label="Name:"
                    data-options="labelPosition: 'top'" style="width:100%" />
            </div>
        </form>
    </div>
    <div id="dlg-buttons" style="text-align: right; padding: 5px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveCategory()">Save</a>
    </div>

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                $('#parent_id').combogrid({
                    panelWidth: 500,
                    url: "{{ route('product_categories.getCategory') }}",
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    label: 'Parent:',
                    labelPosition: 'top',
                    columns: [[
                        { field: 'parent_name', title: 'Parent' },
                        { field: 'name', title: 'Name' },
                    ]]
                });
            })

            var url;

            function newCategory() {
                $("#dlg")
                    .dialog("open")
                    .dialog("center")
                    .dialog("setTitle", "New Category");

                $("#fm").form("clear");

                url = "{{ route('product_categories.store') }}";
            }

            function editCategory() {
                var row = $("#dg").datagrid("getSelected");
                if (row) {
                    $("#dlg")
                        .dialog("open")
                        .dialog("center")
                        .dialog("setTitle", "Edit Category");

                    $("#fm").form("load", row);

                    $('#parent_id').combogrid('setValue', row.parent_id)
                    $('#parent_id').combogrid('setText', row.parent_name)

                    url = "{{ route('product_categories.update', ':id') }}";
                    url = url.replace(":id", row.id);
                }
            }

            function saveCategory() {
                $("#fm").form("submit", {
                    url: url,
                    iframe: false,
                    onSubmit: function () {
                        return $(this).form("validate");
                    },
                    success: function (result) {
                        result = JSON.parse(result);

                        if (result.success === true) {
                            $("#dlg").dialog("close");
                            $("#dg").datagrid("reload");
                            $('#parent_id').combogrid('grid').datagrid('reload');
                        } else {
                            $.messager.alert('Error', result.message, 'error');
                        }
                    },
                });
            }

            function destroyCategory() {
                var row = $("#dg").datagrid("getSelected");

                if (row) {
                    url = "{{ route('product_categories.destroy', ':id') }}";
                    url = url.replace(":id", row.id);

                    $.messager.confirm(
                        "Confirm",
                        "Are you sure you want to destroy this category?",
                        function (r) {
                            if (r) {
                                $.post(url, function (result) {
                                    if (result.success) {
                                        $("#dg").datagrid("reload");
                                        $('#parent_id').combogrid('grid').datagrid('reload');
                                    } else {
                                        $.messager.show({
                                            title: "Error",
                                            msg: result.errorMsg,
                                        });
                                    }
                                }, "json");
                            }
                        }
                    );
                }
            }
        </script>
    @endpush
</x-app-layout>
