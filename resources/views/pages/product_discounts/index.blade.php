<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" data-options="
        url: '{{ route('product_discounts.getDiscount') }}',
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
                <th data-options="field:'product_code',sortable:true">Code</th>
                <th data-options="field:'product_name',sortable:true">Name</th>
                <th data-options="field:'product_price',sortable:true,sorter:numSorter">Price</th>
                <th data-options="field:'discount_type'">Disc Type</th>
                <th data-options="field:'discount_value',sortable:true,sorter:numSorter">Disc Value</th>
                <th data-options="field:'min_purchase',sortable:true,sorter:numSorter">Min Purchase</th>
                <th data-options="field:'multiple_status'">Multiple</th>
                <th data-options="field:'start_date',sortable:true">Start Date</th>
                <th data-options="field:'end_date',sortable:true">End Date</th>
                <th data-options="field:'status',sortable:true">Status</th>
            </tr>
        </thead>
    </table>

    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newDiscount()">
            New Discount
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
            onclick="editDiscount()">
            Edit Discount
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="destroyDiscount()">
            Remove Discount
        </a>
    </div>

    <div id="dlg" class="easyui-window" style="width:500px" data-options="closed:true,footer:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom: 10px">
                <input name="product_id" id="product_id" style="width:100%" />
            </div>
            <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom:10px">
                    <select class="easyui-combobox" name="discount_type" label="Discount Type:" labelPosition="top"
                        style="width:100%;">
                        @forelse ($discount_types as $key => $discount_type)
                            <option value="{{$key}}">{{$discount_type}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <div style="margin-bottom:10px">
                    <input name="discount_value" class="easyui-textbox" label="Discount Value:" labelPosition="top"
                        width="100%" />
                </div>
            </div>
            <div style="margin-bottom:10px">
                <input name="min_purchase" class="easyui-textbox" label="Min Purchase:" labelPosition="top"
                    width="100%" />
            </div>
            <div style="margin-bottom:10px">
                <input class="easyui-checkbox" name="is_multiple" value="1" label="Multiple:" />
            </div>
            <div style="margin-bottom: 10px">
                <input name="description" class="easyui-textbox" label="Description:"
                    data-options="labelPosition: 'top'" style="width:100%;height:120px;" multiline="true" />
            </div>
            <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom:10px">
                    <input name="start_date" class="easyui-datebox" label="Start Date:" labelPosition="top"
                        width="100%" />
                </div>
                <div style="margin-bottom:10px">
                    <input name="end_date" class="easyui-datebox" label="End Date:" labelPosition="top" width="100%" />
                </div>
            </div>
            <div style="margin-bottom:10px">
                <input class="easyui-checkbox" name="is_active" value="1" label="Active:" />
            </div>
        </form>
    </div>
    <div id="dlg-buttons" style="text-align: right; padding: 5px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveDiscount()">Save</a>
    </div>

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                $('#product_id').combogrid({
                    panelWidth: 500,
                    url: "{{ route('products.getProduct') }}",
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    label: 'Product:',
                    labelPosition: 'top',
                    columns: [[
                        { field: 'code', title: 'Code' },
                        { field: 'name', title: 'Name' },
                        { field: 'part_code', title: 'Part Code' },
                    ]]
                });
            })

            function numSorter(a, b) {
                a = parseFloat(a);
                b = parseFloat(b);
                return a == b ? 0 : (a > b ? 1 : -1);
            }

            var url;

            function newDiscount() {
                $("#dlg")
                    .dialog("open")
                    .dialog("center")
                    .dialog("setTitle", "New Discount");

                $("#fm").form("clear");

                url = "{{ route('product_discounts.store') }}";
            }

            function editDiscount() {
                var row = $("#dg").datagrid("getSelected");
                if (row) {
                    $("#dlg")
                        .dialog("open")
                        .dialog("center")
                        .dialog("setTitle", "Edit Discount");

                    $("#fm").form("load", row);

                    $('#product_id').combogrid('setValue', row.product_id)
                    $('#product_id').combogrid('setText', row.product_name)

                    url = "{{ route('product_discounts.update', ':id') }}";
                    url = url.replace(":id", row.id);
                }
            }

            function saveDiscount() {
                $("#fm").form("submit", {
                    url: url,
                    iframe: false,
                    onSubmit: function (param) {
                        if (!$('input[name=is_multiple]').is(':checked')) {
                            param.is_multiple = 0;
                        }

                        if (!$('input[name=is_active]').is(':checked')) {
                            param.is_active = 0;
                        }

                        return $(this).form("validate");
                    },
                    success: function (result) {
                        result = JSON.parse(result);

                        if (result.success === true) {
                            $("#dlg").dialog("close");
                            $("#dg").datagrid("reload");
                            $('#product_id').combogrid('grid').datagrid('reload');
                        } else {
                            $.messager.alert('Error', result.message, 'error');
                        }
                    },
                });
            }

            function destroyDiscount() {
                var row = $("#dg").datagrid("getSelected");

                if (row) {
                    url = "{{ route('product_discounts.destroy', ':id') }}";
                    url = url.replace(":id", row.id);

                    $.messager.confirm(
                        "Confirm",
                        "Are you sure you want to destroy this discount?",
                        function (r) {
                            if (r) {
                                $.post(url, function (result) {
                                    if (result.success) {
                                        $("#dg").datagrid("reload");
                                        $('#product_id').combogrid('grid').datagrid('reload');
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
