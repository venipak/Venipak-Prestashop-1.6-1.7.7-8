<div class="tmjs-modal-content">
    <div class="tmjs-modal-body">
        <div class="tmjs-map-container">
            <div class="tmjs-map"></div>
        </div>
        <div class="tmjs-terminal-sidebar">
        <div class="tmjs-terminal-finder">
            <h2 data-tmjs-string="modal_header">{l s='Pickup points map' mod='mijoravenipak'}</h2><br>
            <h3>{l s='Filter delivery points' mod='mijoravenipak'}</h3>
            <div id="filter-container" class="col-xs-12">
                <div class="col-xs-4">
                    <a class="mjvp-pickup-filter" data-filter="pickup" rel="nofollow"><span>{l s='Pickups' mod='mijoravenipak'}</a>
                </div>
                <div class="col-xs-4">
                    <a class="mjvp-pickup-filter" data-filter="locker" rel="nofollow"><span>{l s='Lockers' mod='mijoravenipak'}</a>
                </div>
                <div class="col-xs-4">
                    <a class="mjvp-pickup-filter" data-filter="cod" rel="nofollow"><span>{l s='C.O.D delivery points' mod='mijoravenipak'}</a>
                </div>
                <div class="col-xs-4">
                    <a class="mjvp-pickup-filter reset" rel="nofollow">{l s='Reset' mod='mijoravenipak'}</a>
                </div>
            </div>
            <div class="tmjs-close-modal-btn">
            </div>

            <h3 class="tmjs-pt-2" data-tmjs-string="seach_header">{l s='Search around' mod='mijoravenipak'}</h3>
            <div class="tmjs-d-block">
                <input type="text" class="tmjs-search-input">
                <a href="#search" class="tmjs-search-btn"><img src="{$images_url}search.svg" width="18"></a>
            </div>
            <h3 class="tmjs-pt-1" data-tmjs-string="seach_header">{l s='Radius' mod='mijoravenipak'}</h3>
            <div class="tmjs-d-block">
                <input id='terminal-search-radius' type="text" name="search-radius" class="tmjs-search-input">
                <span>{l s='km' mod='mijoravenipak'}</span>
            </div>

            <div class="tmjs-d-block tmjs-pt-1">
                <a href="#useMyLocation" class="tmjs-geolocation-btn">
                    <img src="{$images_url}gps.svg" width="15">
                    <span data-tmjs-string="geolocation_btn">{l s='Use my location' mod='mijoravenipak'}</span>
                </a>
            </div>

            <div class="tmjs-search-result tmjs-d-block tmjs-pt-2">
            </div>

        </div>
        <div class="tmjs-terminal-block">
            <h3 data-tmjs-string="terminal_list_header">{l s='Pickup points list' mod='mijoravenipak'}</h3>
            <ul class="tmjs-terminal-list"></ul>
        </div>
        </div>
    </div>
</div>
