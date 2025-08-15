<x-app-layout title="{{ $page_meta['title'] }}">
    <table id="dg" class="easyui-datagrid" url="{{ route('product_brands.getBrand') }}" toolbar="#toolbar"
        pagination="true" rownumbers="true" fitColumns="true" idField="id" singleSelect="true" fit="true">
        <thead>
            <tr>
                <th field="name" width="50">Name</th>
            </tr>
        </thead>
    </table>

    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newBrand()">
            New Brand
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editBrand()">
            Edit Brand
        </a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
            onclick="destroyBrand()">
            Remove Brand
        </a>
    </div>

    <div id="dlg" class="easyui-dialog" style="width:400px"
        data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:10px">
            <div style="margin-bottom: 10px">
                <input name="name" class="easyui-textbox" required="true" label="Name:"
                    data-options="labelPosition: 'top'" style="width:100%" />
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveBrand()"
            style="width:90px">Save</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
    </div>

    @push('scripts')
        <script type="text/javascript">
            var url;

            function newBrand() {
                $("#dlg")
                    .dialog("open")
                    .dialog("center")
                    .dialog("setTitle", "New Brand");

                $("#fm").form("clear");

                url = "{{ route('product_brands.store') }}";
            }

            function editBrand() {
                var row = $("#dg").datagrid("getSelected");
                if (row) {
                    $("#dlg")
                        .dialog("open")
                        .dialog("center")
                        .dialog("setTitle", "Edit Brand");

                    $("#fm").form("load", row);

                    url = "{{ route('product_brands.update', ':id') }}";
                    url = url.replace(":id", row.id);
                }
            }

            function saveBrand() {
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

            function destroyBrand() {
                var row = $("#dg").datagrid("getSelected");

                if (row) {
                    url = "{{ route('product_brands.destroy', ':id') }}";
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
