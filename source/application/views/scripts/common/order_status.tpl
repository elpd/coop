<div class="order_status">
    <input type="text" id="order_status" class="presentational_input"
           value=
           {if $order.order_status == "unpayed"}"ההזמנה פתוחה"{/if}
    {if $order.order_status == "payed"}"ההזמנה סגורה"{/if}
    {if !isset($order)}"הזמנה חדשה"{/if}
    readonly />
</div>