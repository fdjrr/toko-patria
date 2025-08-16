<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" data-options="
        url: '{{ route('transactions.getTransaction') }}',
        toolbar: '#toolbar',
        pagination: true,
        rownumbers: true,
        fitColumns: true,
        idField: 'id',
        singleSelect: true,
        remoteSort: false,
        multiSort: true,
        fit: true
    ">
        <thead>
            <tr>
                <th data-options="field:'code', width:50, sortable:true">Code</th>
                <th data-options="field:'shipment_no', width:50, sortable:true">Shipment No</th>
                <th data-options="field:'customer_name', width:50, sortable:true">Customer</th>
                <th data-options="field:'transaction_date', width:50, sortable:true">Trx Date</th>
                <th data-options="field:'channel', width:50, sortable:true">Channel</th>
                <th data-options="field:'status', width:50, sortable:true">Status</th>
                <th data-options="field:'payment_method', width:50, sortable:true">Payment Method</th>
                <th data-options="field:'total_discount', width:50, sortable:true, sorter:numSorter">Disc</th>
                <th data-options="field:'total_amount', width:50, sortable:true, sorter:numSorter">Total</th>
            </tr>
        </thead>
    </table>

    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
            onclick="newTransaction()">
            New Transaction
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
            onclick="editTransaction()">
            Edit Transaction
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="destroyTransaction()">
            Remove Transaction
        </a>
    </div>

    <div id="dlg" class="easyui-window" style="width:700px" data-options="closed:true,footer:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom: 10px">
                <input name="customer_id" id="customer_id" style="width:100%" />
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom:10px">
                    <input name="shipment_no" class="easyui-textbox" label="Shipment No:" labelPosition="top"
                        width="100%" />
                </div>
                <div style="margin-bottom:10px">
                    <input name="transaction_date" class="easyui-datebox" label="Trx Date:" labelPosition="top"
                        width="100%" />
                </div>
            </div>
            <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom:10px">
                    <select class="easyui-combobox" name="channel" label="Channel:" labelPosition="top"
                        style="width:100%;">
                        @forelse ($channels as $channel)
                            <option value="{{$channel}}">{{$channel}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <div style="margin-bottom:10px">
                    <select class="easyui-combobox" name="status" label="Status:" labelPosition="top"
                        style="width:100%;">
                        @forelse ($statuses as $status)
                            <option value="{{$status}}">{{$status}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <div style="margin-bottom:10px">
                    <select class="easyui-combobox" name="payment_method" label="Payment Method:" labelPosition="top"
                        style="width:100%;">
                        @forelse ($payment_methods as $payment_method)
                            <option value="{{$payment_method}}">{{$payment_method}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 10px">
                <x-transaction.transaction-item.table />
            </div>
            <div style="margin-bottom:10px">
                <input name="notes" id="notes" class="easyui-textbox" multiline="true" label="Notes:"
                    data-options="labelPosition: 'top'" style="width:100%;height:120px" />
            </div>
        </form>
    </div>
    <div id="dlg-buttons" style="text-align: right; padding: 5px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveTransaction()">Save</a>
    </div>

    <x-window.products />

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                $('#customer_id').combogrid({
                    panelWidth: 500,
                    url: "{{ route('customers.getCustomer') }}",
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    label: 'Customer:',
                    labelPosition: 'top',
                    columns: [[
                        { field: 'code', title: 'Code', width: 50 },
                        { field: 'name', title: 'Name', width: 50 },
                        { field: 'phone_number', title: 'Phone Number', width: 50 },
                        { field: 'address', title: 'Address', width: 50 },
                    ]],
                });
            });

            function numSorter(a, b) {
                a = parseFloat(a);
                b = parseFloat(b);
                return a == b ? 0 : (a > b ? 1 : -1);
            }

            var url;

            function newTransaction() {
                $("#dlg")
                    .dialog("open")
                    .dialog("center")
                    .dialog("setTitle", "New Transaction");

                $("#fm").form("clear");

                url = "{{ route('transactions.store') }}";
            }

            function editTransaction() {
                var row = $("#dg").datagrid("getSelected");
                if (row) {
                    $("#dlg")
                        .dialog("open")
                        .dialog("center")
                        .dialog("setTitle", "Edit Transaction");

                    $("#fm").form("load", row);

                    console.log(row)

                    $('#customer_id').combogrid('setValue', row.customer_id);
                    $('#customer_id').combogrid('setText', row.customer_name);

                    $('#dg-items').datagrid({
                        url: "{{ route('transactions.getItems', ':id') }}".replace(':id', row.id),
                        loadFilter: function (response) {
                            if (response.success) {
                                return {
                                    total: response.data.length,
                                    rows: response.data
                                };
                            } else {
                                return {
                                    total: 0,
                                    rows: []
                                };
                            }
                        }
                    });

                    url = "{{ route('transactions.update', ':id') }}";
                    url = url.replace(":id", row.id);
                }
            }

            function saveTransaction() {
                var items = $('#dg-items').datagrid('getRows');

                $("#fm").form("submit", {
                    url: url,
                    iframe: false,
                    onSubmit: function (param) {
                        param.items = JSON.stringify(items)

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

            function destroyTransaction() {
                var row = $("#dg").datagrid("getSelected");

                if (row) {
                    url = "{{ route('transactions.destroy', ':id') }}";
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
