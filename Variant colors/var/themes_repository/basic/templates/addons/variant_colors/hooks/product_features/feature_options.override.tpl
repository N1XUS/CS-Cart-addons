<div class="ty-control-group prv-option-group">
    <label class="ty-control-group__label">{$opt_group.description}:</label>
    <div class="ty-control-group__item{if $opt_group.color == "Y"}-colorbox{/if}">
        {foreach from=$opt_group.variants item="var"}
            <a title="{if $opt_group.prefix}{$opt_group.prefix}{/if}{$var.variant}{if $opt_group.suffix}{$opt_group.suffix}{/if}" class="prv-option-group__btn{if $var.product_id == $product.product_id} active{elseif $opt_group.color == "Y"} cm-tooltip{/if}" href="{"products.view?product_id={$var.product_id}"|fn_url}"><i style="background: {$var.color}"></i><span>{if $opt_group.prefix}{$opt_group.prefix}{/if}{$var.variant}{if $opt_group.suffix}{$opt_group.suffix}{/if}</span></a>
        {/foreach}
    </div>
</div>