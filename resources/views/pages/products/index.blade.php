<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" data-options="
        url: '{{ route('products.getProduct') }}',
        toolbar: '#toolbar',
        pagination: true,
        rownumbers: true,
        fitColumns: true,
        singleSelect: true,
        fit: true,
        multiSort: true
    ">
        <thead>
            <tr>
                <th data-options="field:'code',sortable:true">Code</th>
                <th data-options="field:'name',sortable:true">Name</th>
                <th data-options="field:'part_code',sortable:true">Part Code</th>
                <th data-options="field:'category_name',sortable:true">Category</th>
                <th data-options="field:'brand_name',sortable:true">Brand</th>
                <th data-options="field:'price',sortable:true,sorter:numSorter">Price</th>
                <th data-options="field:'stock',sortable:true,sorter:numSorter">Stock</th>
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

    <div id="dlg" class="easyui-dialog" style="width:600px" data-options="closed:true,footer:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom:10px">
                <input name="name" class="easyui-textbox" required="true" label="Name:"
                    data-options="labelPosition: 'top'" style="width:100%" />
            </div>
            <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom:10px">
                    <input name="code" class="easyui-textbox" required="true" label="Code:"
                        data-options="labelPosition: 'top'" style="width:100%" />
                </div>
                <div style="margin-bottom:10px">
                    <input name="part_code" class="easyui-textbox" required="true" label="Part Code:"
                        data-options="labelPosition: 'top'" style="width:100%" />
                </div>
            </div>
            <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom:10px">
                    <input name="category_id" id="category_id" required="true" style="width:100%" />
                </div>
                <div style="margin-bottom:10px">
                    <input name="brand_id" id="brand_id" required="true" style="width:100%" />
                </div>
            </div>
            <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:20px;">
                <div style="margin-bottom:10px">
                    <input name="price" class="easyui-textbox" required="true" label="Price:"
                        data-options="labelPosition: 'top'" style="width:100%" />
                </div>
                <div style="margin-bottom:10px">
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
    <div id="dlg-buttons" style="text-align: right; padding: 5px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveProduct()">Save</a>
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
                        { field: 'parent_name', title: 'Parent' },
                        { field: 'name', title: 'Name' },
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

            function numSorter(a, b) {
                a = parseFloat(a);
                b = parseFloat(b);
                return a == b ? 0 : (a > b ? 1 : -1);
            }

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

                    $('#category_id').combogrid('setValue', row.category_id)
                    $('#category_id').combogrid('setText', row.category_name)

                    $('#brand_id').combogrid('setValue', row.brand_id)
                    $('#brand_id').combogrid('setText', row.brand_name)

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
