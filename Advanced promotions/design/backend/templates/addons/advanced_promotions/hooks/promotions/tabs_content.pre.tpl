{if $promotion_data.promotion_id}
<div id="content_advanced_promotions">
    <div class="control-group">
        <label class="control-label">{__("text_promotion_main_image")}:</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="promotion" image_object_type="promotion" image_pair=$promotion_data.main_pair image_type="M" image_object_id=$promotion_data.promotion_id icon_text=__("text_category_icon") detailed_text=__("text_promotion_main_image") no_thumbnail=true}
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("text_promotion_list_image")}:</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="promotion_list" image_object_type="promotion_list" image_pair=$promotion_data.list_pair image_type="M" image_object_id=$promotion_data.promotion_id icon_text=__("text_category_icon") detailed_text=__("text_promotion_list_image") no_thumbnail=true}
        </div>
    </div>
</div>
{/if}