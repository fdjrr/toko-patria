<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" data-options="
        url: '{{ route('products.getProduct') }}',
        toolbar: '#toolbar',
        pagination: true,
        rownumbers: true,
        fitColumns: true,
        singleSelect: true,
        fit: true,
        remoteSort: false,
        multiSort: true
    ">
        <thead>
            <tr>
                <th data-options="field:'code', width:10,sortable:true">Code</th>
                <th data-options="field:'name', width:10,sortable:true">Name</th>
                <th data-options="field:'part_code', width:10,sortable:true">Part Code</th>
                <th data-options="field:'category_name', width:10,sortable:true">Category</th>
                <th data-options="field:'brand_name', width:10,sortable:true">Brand</th>
                <th data-options="field:'price', width:10,sortable:true,sorter:numSorter">Price</th>
                <th data-options="field:'stock', width:10,sortable:true,sorter:numSorter">Stock</th>
                <th data-options="field:'description', width:10">Description</th>
            </tr>
        </thead>
    </table>
    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newProduct()">
            New Product
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editProduct()">
            Edit Product
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="destroyProduct()">
            Remove Product
        </a>
    </div>

    <div id="dlg" class="easyui-dialog" style="width:600px"
        data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom:10px">
                <input name="name" class="easyui-textbox" required="true" label="Name:"
                    data-options="labelPosition: 'top'" style="width:100%" />
            </div>
            <div style="display:flex; gap:20px; margin-bottom:10px">
                <div style="flex:1">
                    <input name="code" class="easyui-textbox" required="true" label="Code:"
                        data-options="labelPosition: 'top'" style="width:100%" />
                </div>
                <div style="flex:1">
                    <input name="part_code" class="easyui-textbox" required="true" label="Part Code:"
                        data-options="labelPosition: 'top'" style="width:100%" />
                </div>
            </div>
            <div style="display:flex; gap:20px; margin-bottom:10px">
                <div style="flex:1">
                    <input name="category_id" id="category_id" required="true" style="width:100%" />
                </div>
                <div style="flex:1">
                    <input name="brand_id" id="brand_id" required="true" style="width:100%" />
                </div>
            </div>
            <div style="display:flex; gap:20px; margin-bottom:10px">
                <div style="flex:1">
                    <input name="price" class="easyui-textbox" required="true" label="Price:"
                        data-options="labelPosition: 'top'" style="width:100%" />
                </div>
                <div style="flex:1">
                    <input name="stock" class="easyui-textbox" required="true" label="Stock:"
                        data-options="labelPosition: 'top'" style="width:100%" />
                </div>
            </div>
            <div style="margin-bottom:10px">
                <input name="keywords" id="kw" class="easyui-textbox" multiline="true" label="Keywords:"
                    data-options="labelPosition: 'top'" style="width:100%;height:120px;" />
                <div style="margin-top: 5px; display: flex; justify-content: flex-end">
                    <a href="#" id="generateKeywords" class="easyui-linkbutton"
                        data-options="iconCls:'icon-reload'">Generate</a>
                </div>
            </div>
            <div style="margin-bottom:10px">
                <input name="description" id="description" class="easyui-textbox" multiline="true" label="Description:"
                    data-options="labelPosition: 'top'" style="width:100%;height:120px" />
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveProduct()"
            style="width:90px">Save</a>
    </div>

    @push('scripts')
        <script type="text/javascript">
            $(function () {
                $('#category_id').combogrid({
                    panelWidth: 500,
                    url: "{{ route('product_categories.getCategory') }}",
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    label: 'Category:',
                    labelPosition: 'top',
                    columns: [[
                        { field: 'parent_name', title: 'Parent', width: 50 },
                        { field: 'name', title: 'Name', width: 50 },
                    ]],
                });

                $('#brand_id').combogrid({
                    panelWidth: 500,
                    url: "{{ route('product_brands.getBrand') }}",
                    idField: 'id',
                    textField: 'name',
                    mode: 'remote',
                    fitColumns: true,
                    label: 'Brand:',
                    labelPosition: 'top',
                    columns: [[
                        { field: 'name', title: 'Name', width: 100 },
                    ]],
                });

                $('#generateKeywords').click(function (e) {
                    e.preventDefault();

                    var name = $('input[name="name"]').val();
                    var description = $('input[name="description"]').val();

                    $.ajax({
                        url: "{{ route('products.generateKeywords') }}",
                        type: 'POST',
                        data: {
                            name: name,
                            description: description
                        },
                        beforeSend: function () {
                            $.messager.progress({
                                title: 'Please wait',
                                msg: 'Generating keywords...'
                            });
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#kw').textbox('setValue', response.data);
                            } else {
                                $.messager.alert('Error', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            $.messager.alert('Error', xhr.responseText, 'error');
                        },
                        complete: function () {
                            $.messager.progress('close');
                        }
                    })
                })
            })

            var url;

            function newProduct() {
                $("#dlg")
                    .dialog("open")
                    .dialog("center")
                    .dialog("setTitle", "New Product");

                $("#fm").form("clear");

                url = "{{ route('products.store') }}";
            }

            function editProduct() {
                var row = $("#dg").datagrid("getSelected");
                if (row) {
                    $("#dlg")
                        .dialog("open")
                        .dialog("center")
                        .dialog("setTitle", "Edit Product");

                    $("#fm").form("load", row);

                    $('#category_id').combogrid('setValue', {
                        id: row.category_id,
                        name: row.category_name
                    })

                    $('#brand_id').combogrid('setValue', {
                        id: row.brand_id,
                        name: row.brand_name
                    })

                    $('#kw').textbox('setValue', row.keywords);

                    $('#description').textbox('setValue', row.description);

                    url = "{{ route('products.update', ':id') }}";
                    url = url.replace(":id", row.id);
                }
            }

            function saveProduct() {
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

            function destroyProduct() {
                var row = $("#dg").datagrid("getSelected");

                if (row) {
                    url = "{{ route('products.destroy', ':id') }}";
                    url = url.replace(":id", row.id);

                    $.messager.confirm(
                        "Confirm",
                        "Are you sure you want to destroy this product?",
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