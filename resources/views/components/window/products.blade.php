<div id="win-products" class="easyui-window" title="Select Product" style="width:800px;height:500px"
    data-options="closed:true">
    <table id="dg-products" class="easyui-datagrid" url="{{ route('products.getProduct') }}"
        style="width:100%;height:100%" singleSelect="true" fitColumns="true" pagination="true" rownumbers="true"
        fit="true" toolbar="#tb" footer="#ft">
        <thead>
            <tr>
                <th field="code" width="50">Code</th>
                <th field="name" width="50">Name</th>
                <th field="part_code" width="50">Part Code</th>
                <th field="category_name" width="50">Category</th>
                <th field="brand_name" width="50">Brand</th>
                <th field="price" width="50">Price</th>
                <th field="stock" width="50">Stock</th>
            </tr>
        </thead>
    </table>
    <div id="tb" style="padding:5px;">
        <input class="easyui-textbox" id="q-product" style="width:200px">
        <a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="doSearch()">Search</a>
    </div>
    <div id="ft" style="text-align:right;padding:5px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="chooseProduct()">Choose</a>
    </div>
</div>

@push('scripts')
    <script>
        function doSearch() {
            $('#dg-products').datagrid('load', {
                q: $('#q-product').val()
            });
        }
    </script>
@endpush
