
{if $field.field_name == 'b_city' }

            <script type="text/javascript"  class="cm-ajax-force">
            //<![CDATA[

                    Tygh.$("[name='user_data[b_city]']").autocomplete({$ldelim}
                        source: function( request, response ) {$ldelim}

                            var check_country;
                            var check_state;
                            check_country = $("[name='user_data[b_country]']").val();
                            check_state = $("[name='user_data[b_state]']").val();

                            $.ceAjax('request', fn_url('novaposhta.autocomplete_city?q=' + request.term + '&check_state=' + check_state + '&check_country=' + check_country), {$ldelim} 
                                callback: function(data) {$ldelim}
                                    response(data.autocomplete);
                                {$rdelim}
                            {$rdelim});
                        {$rdelim},
                        select: function(event, ui) {$ldelim}
                            var item = ui.item;
                            Tygh.$("[name='user_data[b_country]']").val(item.country).trigger('change');
                            Tygh.$("select[name='user_data[b_state]']").val(item.region_id).trigger('change');
                        {$rdelim}
                    {$rdelim});

            //]]>
            </script>

{/if}
{if $field.field_name == 's_city'}

            <script type="text/javascript"  class="cm-ajax-force">
            //<![CDATA[

                    Tygh.$("[name='user_data[s_city]']").autocomplete({$ldelim}
                        source: function( request, response ) {$ldelim}

                            var check_country;
                            var check_state;
                            check_country = $("[name='user_data[s_country]']").val();
                            check_state = $("[name='user_data[s_state]']").val();

                            $.ceAjax('request', fn_url('novaposhta.autocomplete_city?q=' + request.term + '&check_state=' + check_state + '&check_country=' + check_country), {$ldelim} 
                                callback: function(data) {$ldelim}
                                    response(data.autocomplete);
                                {$rdelim}
                            {$rdelim});
                        {$rdelim},
                        select: function(event, ui) {$ldelim}
                            var item = ui.item;
                            Tygh.$("[name='user_data[s_country]']").val(item.country).trigger('change');
                            Tygh.$("select[name='user_data[s_state]']").val(item.region_id).trigger('change');
                        {$rdelim}
                    {$rdelim});

            //]]>
            </script>

{/if}
