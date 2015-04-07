<div class="order_revaluation">
        <label for="current_order_amount">קניה נוכחית:</label>
        <input type="text" id="current_order_amount" name="current_order_amount"
               class="presentational_input" readonly/>
        &nbsp;<b>₪</b>
        <label for="previous_debt">חוב קודם:</label>
        <input type="text" id="previous_debt" name="previous_debt"
               class="presentational_input" value="{$user_current_debt}"
               readonly/>
        &nbsp;<b>₪</b>
        <label for="overall_amount_to_pay">לתשלום:</label>
        <input id="overall_amount_to_pay"
               class="presentational_input" readonly/>
        &nbsp;<b>₪</b>
</div>