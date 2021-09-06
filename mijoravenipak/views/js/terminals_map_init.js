var venipak_custom_modal = function() {
    let mjvp_map_container =  document.getElementById('mjvp-pickup-select-modal');
    let tmjs = null;

    if (typeof(mjvp_map_container) != 'undefined' && mjvp_map_container != null) {
        tmjs = new TerminalMappingMjvp('https://venipak.uat.megodata.com/ws');
        tmjs.setImagesPath(mjvp_imgs_url);
        tmjs.setTranslation(mjvp_terminal_select_translates);

        tmjs.dom.setContainerParent(document.getElementById('mjvp-pickup-select-modal'));
        // tmjs.terminals_cache = null;
        tmjs.init({
            country_code: mjvp_country_code,
            identifier: '',
            isModal: true,
            hideContainer: false,
            hideSelectBtn: false,
            postal_code: mjvp_postal_code,
            city: mjvp_city
        });

        tmjs.sub('tmjs-ready', function(data) {
            let selected_terminal = document.getElementById("mjvp-selected-terminal").value;
            let selected_location = tmjs.map.getLocationById(parseInt(selected_terminal));
            if (typeof(selected_location) != 'undefined' && selected_location != null) {
                tmjs.publish('terminal-selected', selected_location);
                document.querySelector('.tmjs-selected-terminal').innerHTML = '<span class="mjvp-tmjs-terminal-name">' + selected_location.name + '</span> <span class="mjvp-tmjs-terminal-address">(' + selected_location.address + ')</span> <span class="mjvp-tmjs-terminal-comment">' + selected_location.city + '.</span>';
            }
        });
        tmjs.sub('terminal-selected', function(data) {
            document.getElementById("mjvp-selected-terminal").value = data.id;
            mjvp_registerSelection('mjvp-selected-terminal');
            tmjs.publish('close-map-modal');
            document.querySelector('.tmjs-selected-terminal').innerHTML = '<span class="mjvp-tmjs-terminal-name">' + data.name + '</span> <span class="mjvp-tmjs-terminal-address">(' + data.address + ')</span> <span class="mjvp-tmjs-terminal-comment">' + data.city + '.</span>';
        });
    }

    window['venipak_custom_modal'].tmjs = tmjs;
}