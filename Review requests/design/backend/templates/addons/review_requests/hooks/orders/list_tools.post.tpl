{if $orders}
    <li>{btn type="list" text={__("send_review_request_to_selected")} dispatch="dispatch[orders.send_requests]" form="orders_list_form"}</li>
{/if}