<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" data-options="
        url: '{{ route('warehouses.getWarehouse') }}',
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
                <th data-options="field:'code',sortable:true">Code</th>
                <th data-options="field:'name',sortable:true">Name</th>
                <th data-options="field:'address',formatter:strLimit">Address</th>
            </tr>
        </thead>
    </table>

    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newWarehouse()">
            New Warehouse
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
            onclick="editWarehouse()">
            Edit Warehouse
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="destroyWarehouse()">
            Remove Warehouse
        </a>
    </div>

    <div id="dlg" class="easyui-window" style="width:500px" data-options="closed:true,footer:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom: 10px">
                <input name="name" class="easyui-textbox" required="true" label="Name:"
                    data-options="labelPosition: 'top'" style="width:100%" />
            </div>
            <div style="margin-bottom: 10px">
                <input name="address" class="easyui-textbox" required="true" label="Address:"
                    data-options="labelPosition: 'top'" style="width:100%;height:120px;" multiline="true" />
            </div>
        </form>
    </div>
    <div id="dlg-buttons" style="text-align: right; padding: 5px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveWarehouse()">Save</a>
    </div>

    @push('scripts')
        <script type="text/javascript">
            function strLimit(value, row) {
                return value.length > 50 ? value.substring(0, 50) + '...' : value;
            }

            var url;

            function newWarehouse() {
                $("#dlg")
                    .dialog("open")
                    .dialog("center")
                    .dialog("setTitle", "New Warehouse");

                $("#fm").form("clear");

                url = "{{ route('warehouses.store') }}";
            }

            function editWarehouse() {
                var row = $("#dg").datagrid("getSelected");
                if (row) {
                    $("#dlg")
                        .dialog("open")
                        .dialog("center")
                        .dialog("setTitle", "Edit Warehouse");

                    $("#fm").form("load", row);

                    url = "{{ route('warehouses.update', ':id') }}";
                    url = url.replace(":id", row.id);
                }
            }

            function saveWarehouse() {
                $("#fm").form("submit", {
                    url: url,
                    iframe: false,
                    onSubmit: function () {
                        return $(this).form("validate");
                    },
                    success: function (result) {
                        try {
                            result = JSON.parse(result);

                            if (result.success === true) {
                                $("#dlg").dialog("close");
                                $("#dg").datagrid("reload");
                            } else {
                                $.messager.alert('Error', result.message, 'error');
                            }
                        } catch (e) {
                            $.messager.alert('Error', 'Internal Server Error', 'error');
                        }
                    },
                });
            }

            function destroyWarehouse() {
                var row = $("#dg").datagrid("getSelected");

                if (row) {
                    url = "{{ route('warehouses.destroy', ':id') }}";
                    url = url.replace(":id", row.id);

                    $.messager.confirm(
                        "Confirm",
                        "Are you sure you want to destroy this brand?",
                        function (r) {
                            if (r) {
                                $.post(url, function (result) {
                                    if (result.success) {
                                        $("#dg").datagrid("reload");
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