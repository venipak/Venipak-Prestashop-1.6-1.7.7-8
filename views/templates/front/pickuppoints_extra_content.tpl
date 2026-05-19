<div class="mjvp-pp-container">
    {if $terminals}
        <input type="hidden" id="mjvp-pickup-country" name="mjvp-pickup-country" value="{$country_code}"/>
        {if $is_16 || $terminals_overwrite}
            <script>
                mjvp_terminals = {$terminals|@json_encode nofilter};
            </script>
        {/if}
        <script>
            var mjvp_imgs_url = "{$images_url}";
            var mjvp_country_code = "{$country_code}";
            var mjvp_postal_code = "{$postcode}";
            var mjvp_city = "{$city}";
            var mjvp_initAttempts = 0;
            function mjvp_waitForInit() {
                if (typeof mjvp_removeMap === 'function' && typeof venipak_custom_modal === 'function') {
                    mjvp_removeMap();
                    venipak_custom_modal();
                } else if (mjvp_initAttempts < 30) {
                    mjvp_initAttempts++;
                    setTimeout(mjvp_waitForInit, 100);
                } else {
                    console.error('Venipak: failed to initialize pickup point map - required scripts did not load.');
                }
            }
            if (document.readyState === "complete") {
                mjvp_waitForInit();
            } else {
                window.addEventListener("load", function(event) {
                    mjvp_waitForInit();
                });
            }
        </script>
        <input type="hidden" id="mjvp-selected-terminal" name="mjvp-selected-terminal" value="{$selected_terminal}"/>
        <div id="mjvp-pickup-select-modal"></div>
    {/if}
</div>