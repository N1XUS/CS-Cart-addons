{if $carrier == "nova_poshta"}
    {$url = "https://novaposhta.ua/tracking/?cargo_number=`$tracking_number`" scope=parent}
    {$carrier_name = __("carrier_nova_poshta") scope=parent}
    
    {if $shipment.shipment_status.StateName == true}
        {capture name="shipment_status"}
            <hr />
            <p><strong>{__("shipment_status")}:</strong> {$shipment.shipment_status.StateName}</p>
            {if $shipment.shipment_status.DateReceived == true}
                <p><strong>{__("shipment_received_at")}:</strong> {$shipment.shipment_status.DateReceived}</p>
            {/if}
        {/capture}
        {$carrier_info = $smarty.capture.shipment_status scope=parent}
    {/if}
    
{/if}