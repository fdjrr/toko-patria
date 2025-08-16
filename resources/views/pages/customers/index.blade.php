<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" url="{{ route('customers.getCustomer') }}" toolbar="#toolbar"
        pagination="true" rownumbers="true" fitColumns="true" idField="id" singleSelect="true" fit="true">
        <thead>
            <tr>
                <th field="code" width="50">Code</th>
                <th field="name" width="50">Name</th>
                <th field="phone_number" width="50">Phone Number</th>
                <th field="address" width="50">Address</th>
                <th field="province_name" width="50">Province</th>
                <th field="city_name" width="50">City</th>
            </tr>
        </thead>
    </table>

    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newCustomer()">
            New Customer
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
            onclick="editCustomer()">
            Edit Customer
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="destroyCustomer()">
            Remove Customer
        </a>
    </div>

    <div id="dlg" class="easyui-dialog" style="width:400px"
        data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom: 10px">
                <input name="name" class="easyui-textbox" required="true" label="Name:"
                    data-options="labelPosition: 'top'" style="width:100%" />
            </div>
            <div style="margin-bottom: 10px">
                <input name="phone_number" class="easyui-textbox" required="true" label="Phone Number:"
                    data-options="labelPosition: 'top'" style="width:100%" />
            </div>
            <div style="margin-bottom: 10px">
                <input name="address" class="easyui-textbox" required="true" label="Address:"
                    data-options="labelPosition: 'top'" style="width:100%;height:120px;" multiline="true" />
            </div>
            <div style="display:flex; gap:20px; margin-bottom:10px">
                <div style="flex:1">
                    <input name="province_id" id="province_id" style="width:100%" />
                </div>
                <div style="flex:1">
                    <input name="city_id" id="city_id" style="width:100%" />
                </div>
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveCustomer()"
            style="width:90px">Save</a>
    </div>

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                $('#province_id').combogrid({
                    panelWidth: 500,
                    url: "{{ route('provinces.getProvince') }}",
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    label: 'Province:',
                    labelPosition: 'top',
                    columns: [[
                        { field: 'code', title: 'Code', width: 50 },
                        { field: 'name', title: 'Name', width: 50 },
                    ]],
                    onSelect: function (index, row) {
                        $('#city_id').combogrid('clear');
                        $('#city_id').combogrid('grid').datagrid('load', {
                            province_id: row.id
                        });
                    }
                });

                $('#city_id').combogrid({
                    panelWidth: 500,
                    url: "{{ route('cities.getCity') }}",
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    label: 'City:',
                    labelPosition: 'top',
                    columns: [[
                        { field: 'province_code', title: 'Province Code', width: 50 },
                        { field: 'code', title: 'Code', width: 50 },
                        { field: 'name', title: 'Name', width: 50 },
                    ]],
                    onBeforeLoad: function (param) {
                        param.province_id = $('#province_id').combogrid('getValue');
                    }
                });
            });

            var url;

            function newCustomer() {
                $("#dlg")
                    .dialog("open")
                    .dialog("center")
                    .dialog("setTitle", "New Customer");

                $("#fm").form("clear");

                url = "{{ route('customers.store') }}";
            }

            function editCustomer() {
                var row = $("#dg").datagrid("getSelected");
                if (row) {
                    $("#dlg")
                        .dialog("open")
                        .dialog("center")
                        .dialog("setTitle", "Edit Customer");

                    $("#fm").form("load", row);

                    $('#province_id').combogrid('setValue', {
                        id: row.province_id,
                        name: row.province_name
                    })

                    $('#city_id').combogrid('setValue', {
                        id: row.city_id,
                        name: row.city_name
                    })

                    url = "{{ route('customers.update', ':id') }}";
                    url = url.replace(":id", row.id);
                }
            }

            function saveCustomer() {
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
                        } else {
                            $.messager.alert('Error', result.message, 'error');
                        }
                    },
                });
            }

            function destroyCustomer() {
                var row = $("#dg").datagrid("getSelected");

                if (row) {
                    url = "{{ route('customers.destroy', ':id') }}";
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