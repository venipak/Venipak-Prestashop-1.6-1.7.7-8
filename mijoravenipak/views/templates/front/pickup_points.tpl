<div class="mjvp-pp-container">
  {if $terminals}
    <pre>{*$terminals|print_r:true*}</pre>
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
      });
    </script>
    <input type="hidden" id="mjvp-selected-terminal" name="mjvp-selected-terminal" value="{$selected_terminal}"/>
    <div id="mjvp-pickup-select-modal">
        <select id="mjvp-terminal-select-field" class="selectpicker">
            <option>{l s='Select pick-up point' mod='mijoravenipak'}
            {foreach from=$terminals item=terminal}
                <option value="{$terminal->id}" data-subtext="{$terminal->address}, {$terminal->city}">{$terminal->name}</option>
            {/foreach}
        </select>
    </div>
  {/if}
</div>