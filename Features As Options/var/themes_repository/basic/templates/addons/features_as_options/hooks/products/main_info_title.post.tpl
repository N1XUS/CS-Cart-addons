{hook name="products:features_as_options"}
{if $product.option_features}
    <div class="ty-product-block__also-available-container">
        <p class="ty-product-block__also-available-title">{__("also_available_models")}:</p>
        {foreach from=$product.option_features item="opt_group"}
            {hook name="product_features:feature_options"}
            <div class="ty-control-group prv-option-group">
                <label class="ty-control-group__label">{$opt_group.description}:</label>
                <div class="ty-control-group__item">
                    {foreach from=$opt_group.variants item="var"}
                        <a class="prv-option-group__btn{if $var.product_id == $product.product_id} active{/if}{if $var.amount == 0} disabled{elseif $var.amount <= $settings.General.low_stock_threshold} low-stock{/if}" href="{"products.view?product_id={$var.product_id}"|fn_url}">{if $opt_group.prefix}{$opt_group.prefix}{/if}{$var.variant}{if $opt_group.suffix}{$opt_group.suffix}{/if}</a>
                    {/foreach}
                </div>
            </div>
            {/hook}
        {/foreach}
    </div>
{/if}
{/hook}