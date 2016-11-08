<div class="ty-sitemap__tree-section">
  {foreach from=$all_categories_tree item=category key=cat_key name="categories"}
     {if $category.level == "0"}
         {if $ul_subcategories == "started"}
         </ul>
              {assign var="ul_subcategories" value=""}
         {/if}
         {if $ul_subcategories != "started"}
         <ul class="ty-sitemap__tree-section-list">
                 <li class="ty-sitemap__tree-list-item parent"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}" class="ty-strong">{$category.category}</a></li>
                 {if $seo_filters[$category.category_id]}
                    {foreach from=$seo_filters[$category.category_id] item="_seo_filters"}
                        <li class="ty-sitemap__tree-list-item" style="padding-left: {math equation="x*y+0" x="5" y=$category.level}px;"><a href="{"categories.view?category_id=`$category.category_id`&features_hash=`$_seo_filters.features_hash`"|fn_url}">{$_seo_filters.category_display_name}</a></li>
                    {/foreach}
                 {/if}
              {assign var="ul_subcategories" value="started"}
          {/if}
     {else}
             <li class="ty-sitemap__tree-list-item" style="padding-left: {if $category.level == "1"}0px{elseif $category.level > "1"}{math equation="x*y+0" x="5" y=$category.level}px{/if};"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}">{$category.category}</a></li>
             {if $seo_filters[$category.category_id]}
                {foreach from=$seo_filters[$category.category_id] item="_seo_filters"}
                    <li class="ty-sitemap__tree-list-item" style="padding-left: {math equation="x*y+5" x="5" y=$category.level}px;"><a href="{"categories.view?category_id=`$category.category_id`&features_hash=`$_seo_filters.features_hash`"|fn_url}">{$_seo_filters.category_display_name}</a></li>
                {/foreach}
             {/if}
     {/if}
     {if $smarty.foreach.categories.last}
          </ul>
     {/if}
  {/foreach}
</div>
<div class="clearfix"></div>