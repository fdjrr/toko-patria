<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" url="{{ route('transactions.getTransaction') }}" toolbar="#toolbar"
        pagination="true" rownumbers="true" fitColumns="true" idField="id" singleSelect="true" fit="true">
        <thead>
            <tr>
                <th field="code" width="50">Code</th>
                <th field="shipment_no" width="50">Shipment No</th>
                <th field="customer_name" width="50">Customer</th>
                <th field="transaction_date" width="50">Trx Date</th>
                <th field="channel" width="50">Channel</th>
                <th field="status" width="50">Status</th>
                <th field="payment_method" width="50">Payment Method</th>
                <th field="total_amount" width="50">Total</th>
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

    <div id="dlg" class="easyui-dialog" style="width:700px"
        data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom: 10px">
                <input name="customer_id" id="customer_id" style="width:100%" />
            </div>
            <div style="display:flex; gap:20px; margin-bottom: 10px">
                <div style="flex: 1;">
                    <input name="shipment_no" class="easyui-textbox" label="Shipment No:" labelPosition="top"
                        style="width:100%" />
                </div>
                <div style="flex: 1;">
                    <input name="transaction_date" class="easyui-datebox" label="Trx Date:" labelPosition="top"
                        style="width:100%;">
                </div>
            </div>
            <div style="display:flex; gap:20px; margin-bottom:10px">
                <div style="flex: 1;">
                    <select class="easyui-combobox" name="channel" label="Channel:" labelPosition="top"
                        style="width:100%;">
                        @forelse ($channels as $channel)
                            <option value="{{$channel}}">{{$channel}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <div style="flex: 1;">
                    <select class="easyui-combobox" name="status" label="Status:" labelPosition="top"
                        style="width:100%;">
                        @forelse ($statuses as $status)
                            <option value="{{$status}}">{{$status}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <div style="flex: 1;">
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

                    $('#province_id').combogrid('setValue', {
                        id: row.province_id,
                        name: row.province_name
                    })

                    $('#city_id').combogrid('setValue', {
                        id: row.city_id,
                        name: row.city_name
                    })

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