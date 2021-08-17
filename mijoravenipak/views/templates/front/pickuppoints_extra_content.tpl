<div class="mjvp-pp-container">
    {if $terminals}
        <input type="hidden" id="mjvp-pickup-country" name="mjvp-pickup-country" value="{$country_code}"/>
        <script>
            var mjvp_imgs_url = "{$images_url}";
            var mjvp_country_code = "{$country_code}";
            var mjvp_postal_code = "{$postcode}";
            var mjvp_city = "{$city}";
            document.addEventListener("DOMContentLoaded", function(event) {
                if ({$cart_quantity} > 2) {
                    $('#mjvp-pickup-select-modal').closest('.checkoutblock').hide();
                    return;
                }

                venipak_custom_modal();
            });
        </script>
        <input type="hidden" id="mjvp-selected-terminal" name="mjvp-selected-terminal" value="{$selected_terminal}"/>
        <div id="mjvp-pickup-select-modal">
        </div>
    {/if}
</div>