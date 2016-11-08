{if $shipments}
    <li>{btn type="list" text=__("resend_shipment_info") class="cm-confirm" dispatch="dispatch[shipments.resend]" form="manage_shipments_form"}</li>
    <li class="divider"></li>
{/if}