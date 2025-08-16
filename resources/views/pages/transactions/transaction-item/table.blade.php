<div>
    <h4>Transaction Items</h4>
    <table id="dg-items" class="easyui-datagrid" style="width:100%;height:200px" data-options="
            singleSelect:true,
            fitColumns:true,
            toolbar:'#toolbar-items',
            footer:'#footer-items',
            onClickCell:onClickCell
        ">
        <thead>
            <tr>
                <th data-options="field:'product_code',width:50">Code</th>
                <th data-options="field:'product_name',width:50">Name</th>
                <th data-options="field:'qty',width:50,editor:'textbox'">Qty</th>
                <th data-options="field:'price',width:50,editor:'textbox'">Price</th>
            </tr>
        </thead>
    </table>
    <div id="toolbar-items">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" plain="true"
            onclick="openProductWindow()">Add Product</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="removeItem()">Remove Item</a>
    </div>
    <div id="footer-items" style="text-align: right;">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true"
            onclick="reject()">Reject</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true"
            onclick="accept()">Accept</a>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveTransaction()"
            style="width:90px">Save</a>
    </div>
</div>

@push('scripts')
    <script>
        function openProductWindow() {
            $('#win-products').window('open');
            $('#dg-products').datagrid('reload');
        }

        function chooseProduct() {
            var row = $('#dg-products').datagrid('getSelected');
            if (row) {
                var items = $('#dg-items').datagrid('getRows');
                var exists = items.some(item => item.product_code === row.code);
                if (!exists) {
                    $('#dg-items').datagrid('appendRow', {
                        product_code: row.code,
                        product_name: row.name,
                        qty: 1,
                        price: row.price,
                    });
                }
                $('#win-products').window('close');
            }
        }

        function removeItem() {
            var row = $('#dg-items').datagrid('getSelected');
            if (row) {
                var index = $('#dg-items').datagrid('getRowIndex', row);
                $('#dg-items').datagrid('deleteRow', index);
            }
        }

        var editIndex = undefined;

        function endEditing() {
            if (editIndex == undefined) { return true }
            if ($('#dg-items').datagrid('validateRow', editIndex)) {
                $('#dg-items').datagrid('endEdit', editIndex);
                editIndex = undefined;
                return true;
            } else {
                return false;
            }
        }

        function onClickCell(index, field) {
            if (editIndex != index) {
                if (endEditing()) {
                    $('#dg-items').datagrid('selectRow', index)
                        .datagrid('beginEdit', index);
                    var ed = $('#dg-items').datagrid('getEditor', { index: index, field: field });
                    if (ed) {
                        ($(ed.target).data('textbox') ? $(ed.target).textbox('textbox') : $(ed.target)).focus();
                    }
                    editIndex = index;
                } else {
                    setTimeout(function () {
                        $('#dg-items').datagrid('selectRow', editIndex);
                    }, 0);
                }
            }
        }

        function accept() {
            if (endEditing()) {
                $('#dg-items').datagrid('acceptChanges');
            }
        }

        function reject() {
            $('#dg-items').datagrid('rejectChanges');
            editIndex = undefined;
        }
    </script>
@endpush