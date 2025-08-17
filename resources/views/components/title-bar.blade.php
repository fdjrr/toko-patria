<div id="titlebar" style="padding:3px">
    <a href="{{ route('dashboard') }}" class="easyui-linkbutton" style="width:100%;margin-bottom:5px"
        data-options="iconCls:'icon-dashboard',size:'large',iconAlign:'top'">Home</a>
    <a href="{{ route('transactions.index') }}" class="easyui-linkbutton" style="width:100%;margin-bottom:5px"
        data-options="iconCls:'icon-money-bundle',size:'large',iconAlign:'top'">Trx</a>
    <a href="{{ route('customers.index') }}" class="easyui-linkbutton" style="width:100%;margin-bottom:5px"
        data-options="iconCls:'icon-contacts',size:'large',iconAlign:'top'">Customer</a>
    <a href="{{ route('products.index') }}" class="easyui-linkbutton" style="width:100%;margin-bottom:5px"
        data-options="iconCls:'icon-database',size:'large',iconAlign:'top'">Product</a>
    <a href="{{ route('product_discounts.index') }}" class="easyui-linkbutton" style="width:100%;margin-bottom:5px"
        data-options="iconCls:'icon-money',size:'large',iconAlign:'top'">Discount</a>
    <a href="{{ route('product_categories.index') }}" class="easyui-linkbutton" style="width:100%;margin-bottom:5px"
        data-options="iconCls:'icon-large-shapes',size:'large',iconAlign:'top'">Category</a>
    <a href="{{ route('product_brands.index') }}" class="easyui-linkbutton" style="width:100%;margin-bottom:5px"
        data-options="iconCls:'icon-large-smartart',size:'large',iconAlign:'top'">Brand</a>
</div>
