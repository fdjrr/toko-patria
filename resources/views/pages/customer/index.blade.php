<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" data-options="
        url: '{{ route('customers.getCustomer') }}',
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
                <th data-options="field:'phone_number'">Phone Number</th>
                <th data-options="field:'address',formatter:strLimit">Address</th>
                <th data-options="field:'province_name',sortable:true">Province</th>
                <th data-options="field:'city_name',sortable:true">City</th>
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

    <div id="dlg" class="easyui-window" style="width:500px" data-options="closed:true,footer:'#dlg-buttons'">
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
            <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom: 10px">
                    <input name="province_id" id="province_id" style="width:100%" />
                </div>
                <div style="margin-bottom: 10px">
                    <input name="city_id" id="city_id" style="width:100%" />
                </div>
            </div>
        </form>
    </div>
    <div id="dlg-buttons" style="text-align: right; padding: 5px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveCustomer()">Save</a>
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
                        { field: 'code', title: 'Code' },
                        { field: 'name', title: 'Name' },
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
                        { field: 'province_code', title: 'Province Code' },
                        { field: 'code', title: 'Code' },
                        { field: 'name', title: 'Name' },
                    ]],
                    onBeforeLoad: function (param) {
                        param.province_id = $('#province_id').combogrid('getValue');
                    }
                });
            });

            function strLimit(value, row) {
                return value.length > 50 ? value.substring(0, 50) + '...' : value;
            }

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

                    $('#province_id').combogrid('setValue', row.province_id)
                    $('#province_id').combogrid('setText', row.province_name)

                    $('#city_id').combogrid('setValue', row.city_id)
                    $('#city_id').combogrid('setText', row.city_name)

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