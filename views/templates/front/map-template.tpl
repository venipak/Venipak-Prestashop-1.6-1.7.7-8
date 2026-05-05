<div class="tmjs-modal-content">
    <div class="tmjs-modal-body">
        <div class="tmjs-map-container">
            <div class="tmjs-map"></div>
        </div>
        <div class="tmjs-terminal-sidebar col-xl-4 col-md-6 col-sm-12">
        <div class="tmjs-terminal-finder">
            <h2 data-tmjs-string="modal_header">{l s='Pickup points map' mod='mijoravenipak'}</h2><br>
            <div class="tmjs-d-block tmjs-search-input-container">
                <input type="text" class="tmjs-search-input" placeholder="{l s='Enter address' mod='mijoravenipak'}">
                <a href="#search" class="tmjs-search-btn"><svg width="18" height="18" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M505.749,475.587l-145.6-145.6c28.203-34.837,45.184-79.104,45.184-127.317c0-111.744-90.923-202.667-202.667-202.667S0,90.925,0,202.669s90.923,202.667,202.667,202.667c48.213,0,92.48-16.981,127.317-45.184l145.6,145.6c4.16,4.16,9.621,6.251,15.083,6.251s10.923-2.091,15.083-6.251C514.091,497.411,514.091,483.928,505.749,475.587z M202.667,362.669c-88.235,0-160-71.765-160-160s71.765-160,160-160s160,71.765,160,160S290.901,362.669,202.667,362.669z" fill="currentColor"/></svg></a>
                <a href="#useMyLocation" class="tmjs-geolocation-btn">
                    <svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg"><path d="M8.32085e-05 5.61501C-0.00198385 5.7417 0.0345045 5.86602 0.10471 5.97149C0.174916 6.07696 0.275523 6.15861 0.393192 6.2056L6.39356 8.60606L8.79408 14.6069C8.84045 14.723 8.92056 14.8225 9.02406 14.8925C9.12756 14.9626 9.24969 15 9.37468 15H9.38468C9.51126 14.998 9.63425 14.9576 9.73737 14.8842C9.84049 14.8108 9.91888 14.7077 9.96216 14.5888L14.9619 0.839709C15.0029 0.727858 15.011 0.60664 14.9853 0.490333C14.9597 0.374025 14.9013 0.267471 14.8171 0.183218C14.7329 0.0989664 14.6264 0.0405251 14.5101 0.0147776C14.3938-0.0109698 14.2726-0.00295133 14.1607 0.0378886L0.411316 5.03755C0.292299 5.08094 0.189261 5.15944 0.11583 5.26267C0.0423997 5.36589 0.00203762 5.48897 8.32085e-05 5.61564V5.61501Z" fill="currentColor"/></svg>
                </a>
            </div><br>
            {assign var="mjvp_filter_count" value=0}
            {if isset($mjvp_allowed_pickup_types) && in_array(1, $mjvp_allowed_pickup_types)}{assign var="mjvp_filter_count" value=$mjvp_filter_count+1}{/if}
            {if isset($mjvp_allowed_pickup_types) && in_array(3, $mjvp_allowed_pickup_types)}{assign var="mjvp_filter_count" value=$mjvp_filter_count+1}{/if}
            {if !isset($mjvp_allowed_pickup_types) || $mjvp_filter_count > 1}
            <h3>{l s='Filter delivery points' mod='mijoravenipak'}</h3>
            <div id="filter-container" class="col-xs-12">
                {if !isset($mjvp_allowed_pickup_types) || in_array(1, $mjvp_allowed_pickup_types)}
                <div class="form-check-inline">
                    <input id="mjvp-pickup-filter-pickups" type="checkbox" name="mjvp-pickup-filter-pickups" class="mjvp-pickup-filter form-check-inline not_uniform"  data-filter="1" checked>
                    <label for="mjvp-pickup-filter-pickups">{l s='Pickups' mod='mijoravenipak'}</label>
                </div>
                {/if}
                {if !isset($mjvp_allowed_pickup_types) || in_array(3, $mjvp_allowed_pickup_types)}
                <div class="form-check-inline">
                    <input type="checkbox" name="mjvp-pickup-filter-lockers" id="mjvp-pickup-filter-lockers" class="mjvp-pickup-filter form-check-inline not_uniform" data-filter="3" checked>
                    <label for="mjvp-pickup-filter-lockers">{l s='Lockers' mod='mijoravenipak'}</label>
                </div>
                {/if}
                <div>
                    <a class="mjvp-pickup-filter reset" rel="nofollow">{l s='Reset' mod='mijoravenipak'}</a>
                </div>
            </div>
            {/if}
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
