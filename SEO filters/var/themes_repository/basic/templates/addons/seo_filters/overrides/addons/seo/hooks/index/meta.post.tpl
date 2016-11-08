
{if !$smarty.request|fn_seo_filters_is_indexed_page}
{* <meta name="robots" content="noindex{if $settings.Security.secure_storefront == "partial" && 'HTTPS'|defined},nofollow{/if}" /> *}
<meta name="robots" content="noindex, nofollow" />
{else}
 <meta name="robots" content="index, follow" />
{if $override_seo_canonical}
{if $override_seo_canonical.current}
    <link rel="canonical" href="{$override_seo_canonical.current}" />
{/if}

{if $override_seo_canonical.prev}
    <link rel="prev" href="{$override_seo_canonical.prev}" />
{/if}

{if $override_seo_canonical.next}
    <link rel="next" href="{$override_seo_canonical.next}" />
{/if}
{else}
{if $seo_canonical.current}
    <link rel="canonical" href="{$seo_canonical.current}" />
{/if}

{if $seo_canonical.prev}
    <link rel="prev" href="{$seo_canonical.prev}" />
{/if}

{if $seo_canonical.next}
    <link rel="next" href="{$seo_canonical.next}" />
{/if}
{/if}
{/if}

{if $languages|sizeof > 1}
{foreach from=$languages item="language"}
<link title="{$language.name}" dir="rtl" type="text/html" rel="alternate" hreflang="{$language.lang_code}" href="{$config.current_url|fn_link_attach:"sl=`$language.lang_code`"|fn_url}" />
{/foreach}
{/if}

