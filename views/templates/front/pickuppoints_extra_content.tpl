<div class="mjvp-pp-container">
   {if isset($notifications.error.mjvp_terminal)}
        <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.mjvp_terminal}
        </div>
    {/if}
    {if $terminals}
        <input type="hidden" id="mjvp-pickup-country" name="mjvp-pickup-country" value="{$country_code}"/>
        <script>
            var mjvp_imgs_url = "{$images_url}";
            var mjvp_country_code = "{$country_code}";
            var mjvp_postal_code = "{$postcode}";
            var mjvp_city = "{$city}";
            if (document.readyState === "complete") { //Execute immediately if the "load" event has already passed
                venipak_custom_modal();
            } else {
                window.addEventListener("load", function(event) {
                    venipak_custom_modal();
                });
            }
        </script>
        <input type="hidden" id="mjvp-selected-terminal" name="mjvp-selected-terminal" value="{$selected_terminal}"/>
        <div id="mjvp-pickup-select-modal">
        </div>
    {/if}
</div>