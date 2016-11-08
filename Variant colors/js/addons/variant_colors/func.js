(function(_, $) {
    $(document).ready(function() {
        $(_.doc).on('change', '.cm-color-feature', function(e) {
            var elm = $(e.target);
            var feature_id = $(this).data('caFeatureId');
            var t = $('#content_tab_variants_' + feature_id);
            if (elm.is(":checked")) {
                $(".cm-variant-color-cell", t).removeClass("hidden");
            } else {
                $(".cm-variant-color-cell", t).addClass("hidden");                    
            }
        });
    });
}(Tygh, Tygh.$));