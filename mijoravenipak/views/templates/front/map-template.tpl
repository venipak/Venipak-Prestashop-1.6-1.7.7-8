<div class="tmjs-modal-content">
    <div class="tmjs-modal-body">
        <div class="tmjs-map-container">
            <div class="tmjs-map"></div>
        </div>
        <div class="tmjs-terminal-sidebar col-md-4 col-sm-6">
        <div class="tmjs-terminal-finder">
            <h2 data-tmjs-string="modal_header">{l s='Pickup points map' mod='mijoravenipak'}</h2><br>
            <div class="tmjs-d-block tmjs-search-input-container">
                <input type="text" class="tmjs-search-input" placeholder="{l s='Enter address' mod='mijoravenipak'}">
                <a href="#search" class="tmjs-search-btn"><img width="18" src="{$images_url}search_orange.svg"></a>
                <a href="#useMyLocation" class="tmjs-geolocation-btn">
                    <img width="15" src="{$images_url}arrow.svg">
                </a>
            </div>
            <h3>{l s='Filter delivery points' mod='mijoravenipak'}</h3>
            <div id="filter-container" class="col-xs-12">
                <div class="col-xs-4 form-check-inline">
                    <input id="mjvp-pickup-filter-pickups" type="checkbox" name="mjvp-pickup-filter-pickups" class="mjvp-pickup-filter form-check-inline not_uniform"  data-filter="pickup" checked>
                    <label for="mjvp-pickup-filter-pickups">{l s='Pickups' mod='mijoravenipak'}</label>
                </div>
                <div class="col-xs-4 form-check-inline">
                    <input type="checkbox" name="mjvp-pickup-filter-lockers" id="mjvp-pickup-filter-lockers" class="mjvp-pickup-filter form-check-inline not_uniform" data-filter="locker" checked>
                    <label for="mjvp-pickup-filter-lockers">{l s='Lockers' mod='mijoravenipak'}</label>
                </div>
                <div class="col-xs-4">
                    <a class="mjvp-pickup-filter reset" rel="nofollow">{l s='Reset' mod='mijoravenipak'}</a>
                </div>
            </div>
            <div class="tmjs-close-modal-btn">
            </div>

{*            <h3 class="tmjs-pt-1" data-tmjs-string="seach_header">{l s='Radius' mod='mijoravenipak'}</h3>*}
{*            <div class="tmjs-d-block">*}
{*                <input id='terminal-search-radius' type="text" name="search-radius" class="tmjs-search-input">*}
{*                <span>{l s='km' mod='mijoravenipak'}</span>*}
{*            </div>*}

{*            <div class="tmjs-d-block tmjs-pt-1">*}
{*                <a href="#useMyLocation" class="tmjs-geolocation-btn">*}
{*                    <img src="{$images_url}gps.svg" width="15">*}
{*                    <span data-tmjs-string="geolocation_btn">{l s='Use my location' mod='mijoravenipak'}</span>*}
{*                </a>*}
{*            </div>*}

            <div class="tmjs-search-result tmjs-d-block tmjs-pt-2">
            </div>

        </div>
            <h3>{l s='Pickup points' mod='mijoravenipak'}</h3>
        <div class="tmjs-terminal-block">
            <ul class="tmjs-terminal-list"></ul>
        </div>
        </div>
    </div>
</div>
