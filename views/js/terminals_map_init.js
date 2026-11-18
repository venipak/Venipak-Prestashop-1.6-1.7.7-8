/**
 * Venipak pickup point map, built with terminal-mapping library (views/js/terminal-mapping.js).
 * Library is registered as window.TerminalMappingMjvp (TMJS_GLOBAL_NAME is set in terminal-mapping-name.js).
 */
var MJVP_TILE_SERVER_URL = 'https://osm.venipak.com/tile/{z}/{x}/{y}.png';
var MJVP_MAP_LOAD_TIMEOUT = 20000;

var venipak_custom_modal = function() {
    let mjvp_map_container = document.getElementById('mjvp-pickup-select-modal');
    let tmjs = null;

    // Filter out terminals without name or id
    if (typeof mjvp_terminals !== 'undefined' && Array.isArray(mjvp_terminals)) {
        mjvp_terminals = mjvp_terminals.filter(function(terminal) {
            return terminal.id && terminal.name;
        });
        if (mjvp_terminals.length === 0) {
            console.warn('Venipak: pickup carrier has no valid terminals (all filtered out due to missing name or ID).');
        }
    } else {
        console.warn('Venipak: pickup carrier terminal list is not a valid array. Delivery method may be unavailable.');
    }

    if (typeof(mjvp_map_container) == 'undefined' || mjvp_map_container == null) {
        return false;
    }

    if (typeof window.TerminalMappingMjvp !== 'function') {
        console.error('[MJVP]', 'Terminal mapping library (TerminalMappingMjvp) is not loaded');
        mjvp_showMapError(mjvp_map_container);
        return false;
    }

    console.log('[MJVP]', 'Building TMJS...');
    // No API mode - terminal list is given in init()
    tmjs = new TerminalMappingMjvp();
    tmjs.setImagesPath(mjvp_imgs_url);
    tmjs.setTranslation(Object.assign({}, mjvp_terminal_select_translates, {
        // container shows selected terminal only
        select_pickup_point: ''
    }));

    tmjs.dom.setContainerParent(mjvp_map_container);

    tmjs.sub('tmjs-ready', function() {
        console.log('[MJVP]', 'tmjs-ready event received');
        mjvp_addModalClass(tmjs);
        mjvp_setupMap(tmjs);

        let selected_terminal = document.getElementById("mjvp-selected-terminal").value;
        if (selected_terminal && selected_terminal != '0') {
            let selected_location = tmjs.map.getLocationById(String(selected_terminal));
            if (typeof(selected_location) != 'undefined' && selected_location != null) {
                tmjs.publish('terminal-selected', selected_location);
                mjvp_showSelectedTerminal(selected_location);
            }
        }
    });
    tmjs.sub('terminal-selected', function(data) {
        console.log('[MJVP]', 'terminal-selected event received');
        document.getElementById("mjvp-selected-terminal").value = data.id;
        mjvp_registerSelection('mjvp-selected-terminal');
        tmjs.publish('close-map-modal');
        mjvp_showSelectedTerminal(data);
    });
    tmjs.sub('modal-opened', function() {
        // Show terminals closest to delivery address, unless customer searched for other address
        if (typeof address_query === 'undefined' || !address_query) {
            return;
        }
        if (!tmjs.dom._lastSearchTerm || tmjs.dom._lastSearchTerm == address_query) {
            // customer sees which address is searched
            let input = tmjs.dom.UI.modal ? tmjs.dom.UI.modal.querySelector('.tmjs-search-input') : null;
            if (input) {
                input.value = address_query;
            }
            tmjs.dom.resetSearch();
            tmjs.dom.searchNearest(address_query);
        }
    });
    tmjs.sub('search-result', function(data) {
        // library shows found address only, add text explaining distances in list (runs after library subscriber)
        mjvp_showSearchAddress(tmjs, data && data.address ? data.address : '');
    });
    tmjs.sub('tmjs-load-error', function(data) {
        console.error('[MJVP]', 'Pickup point map failed to load:', data.error);
        // map is not available, so button opening it is hidden
        if (tmjs.dom.UI.container) {
            tmjs.dom.UI.container.style.display = 'none';
        }
        mjvp_showMapError(mjvp_map_container);
    });

    let tmjsData = {
        country_code: mjvp_country_code,
        identifier: '',
        isModal: true,
        hideContainer: false,
        hideSelectBtn: false,
        postal_code: mjvp_postal_code,
        city: mjvp_city,
        terminalList: mjvp_prepareTerminals(mjvp_terminals),
        customTileServerUrl: MJVP_TILE_SERVER_URL,
        parseLocationName: function(location) {
            return (location.display_name || location.name) + ', ' + location.address;
        },
        // loading overlay covers only pickup point block, not whole checkout page
        overlayParent: mjvp_map_container,
        loadTimeout: MJVP_MAP_LOAD_TIMEOUT
    };
    console.log('[MJVP]', 'Initiating TMJS with data', tmjsData);
    tmjs.init(tmjsData);
    // modal is already created, add class for Venipak styles (views/css/terminal-mapping-custom.css)
    mjvp_addModalClass(tmjs);

    window['venipak_custom_modal'].tmjs = tmjs;
    console.log('[MJVP]', 'TMJS initialization completed:', tmjs.containerId);
    return true;
}

function mjvp_removeMap() {
    if ( ! window['venipak_custom_modal'] ) {
        return false;
    }
    if ( typeof window['venipak_custom_modal'].tmjs === 'undefined' ) {
        return false;
    }

    let tmjs = window['venipak_custom_modal'].tmjs;
    console.log('[MJVP]', 'Removing TMJS:', tmjs.containerId);
    // removes container, modal, overlay, map and stops unfinished loading
    tmjs.destroy();
    delete window['venipak_custom_modal'].tmjs;

    let error = document.querySelector('#mjvp-pickup-select-modal .mjvp-map-error');
    if (error) {
        error.remove();
    }

    return true;
}

function mjvp_getTmjs() {
    if (typeof window['venipak_custom_modal'] === 'undefined' || !window['venipak_custom_modal'].tmjs) {
        return null;
    }
    return window['venipak_custom_modal'].tmjs;
}

/**
 * Prepares Venipak terminal list for terminal-mapping library.
 * @param {Array} terminals - terminals from module (mjvp_terminals)
 * @returns {Array}
 */
function mjvp_prepareTerminals(terminals) {
    let prepared = [];
    if (!Array.isArray(terminals)) {
        return prepared;
    }

    terminals.forEach(function(terminal) {
        if (!terminal || !terminal.id || !terminal.name) {
            return;
        }
        if (terminal.lat == 0 || terminal.lng == 0 || !terminal.terminal) {
            return;
        }

        // Name is shown without its first part. Same object is sent to server when terminal is selected,
        // so name is changed only once.
        if (typeof terminal.mjvp_full_name === 'undefined') {
            terminal.mjvp_full_name = terminal.name;
            terminal.name = terminal.name.split(', ').slice(1).join(', ');
        }

        prepared.push(Object.assign({}, terminal, {
            // library compares ids with values from list element data attribute (string)
            id: String(terminal.id),
            identifier: 'venipak',
            coords: {
                lat: terminal.lat,
                lng: terminal.lng
            }
        }));
    });

    return prepared;
}

/**
 * Venipak specific map setup, when map is ready
 */
function mjvp_setupMap(tmjs) {
    tmjs.map.createIcon('venipak', mjvp_imgs_url + 'map-pin.svg', {
        iconSize: [40, 40],
        iconAnchor: [20, 20],
        popupAnchor: [0, 0]
    });
    tmjs.map.refreshMarkerIcons();

    // After sorting by distance, keep terminals of the same city together (cities ordered by closest terminal)
    let addDistance = tmjs.map.addDistance.bind(tmjs.map);
    tmjs.map.addDistance = function(origin) {
        this.locations = mjvp_groupLocationsByCity(addDistance(origin));
        return this.locations;
    };
}

function mjvp_groupLocationsByCity(locations) {
    let cities = [];
    let byCity = {};
    locations.forEach(function(location) {
        if (!Object.prototype.hasOwnProperty.call(byCity, location.city)) {
            byCity[location.city] = [];
            cities.push(location.city);
        }
        byCity[location.city].push(location);
    });

    let grouped = [];
    cities.forEach(function(city) {
        grouped = grouped.concat(byCity[city]);
    });
    return grouped;
}

function mjvp_addModalClass(tmjs) {
    if (tmjs.dom.UI.modal) {
        tmjs.dom.UI.modal.classList.add('mjvp-map-modal');
    }
}

function mjvp_showSelectedTerminal(location) {
    let el = document.querySelector('#mjvp-pickup-select-modal .tmjs-selected-terminal');
    if (!el) {
        return;
    }
    el.innerHTML = '<span class="mjvp-tmjs-terminal-name">' + location.name + '</span> <span class="mjvp-tmjs-terminal-address">(' + location.address + ')</span> <span class="mjvp-tmjs-terminal-comment">' + location.city + '.</span>';
}

function mjvp_showSearchAddress(tmjs, address) {
    let el = tmjs.dom.UI.modal ? tmjs.dom.UI.modal.querySelector('.tmjs-search-result') : null;
    if (!el || !address) {
        return;
    }
    let label = 'Distance calculated from this point';
    if (typeof mjvp_terminal_select_translates !== 'undefined' && mjvp_terminal_select_translates.your_position) {
        label = mjvp_terminal_select_translates.your_position;
    }
    let addressEl = document.createElement('span');
    addressEl.className = 'mjvp-search-address';
    addressEl.textContent = address;
    el.textContent = label + ': ';
    el.appendChild(addressEl);
}

function mjvp_showMapError(container) {
    if (!container || container.querySelector('.mjvp-map-error')) {
        return;
    }
    let message = 'Pickup points map could not be loaded. Please refresh the page or choose another delivery method.';
    if (typeof mjvp_terminal_select_translates !== 'undefined' && mjvp_terminal_select_translates.map_load_error) {
        message = mjvp_terminal_select_translates.map_load_error;
    }
    let error = document.createElement('div');
    error.className = 'alert alert-warning mjvp-map-error';
    error.textContent = message;
    container.appendChild(error);
}

/**
 * Updates terminals shown in map and list
 * @param {Array} terminals - terminals from module
 */
function mjvp_setMapTerminals(terminals) {
    let tmjs = mjvp_getTmjs();
    mjvp_terminals = Array.isArray(terminals) ? terminals : [];
    if (!tmjs || !tmjs.map) {
        return;
    }

    let locations = mjvp_prepareTerminals(mjvp_terminals);
    if (locations.length === 0) {
        // setTerminals() does not change markers for empty list
        tmjs.map.stopMarkersLoading();
        if (tmjs.map._markerLayer) {
            tmjs.map._markerLayer.clearLayers();
        }
        tmjs.map.locations = [];
        tmjs.dom.renderTerminalList([], true);
        return;
    }

    tmjs.setTerminals(locations);
    let refMarker = tmjs.map._referenceMarker;
    if (refMarker && refMarker._latlng) {
        tmjs.dom.renderTerminalList(tmjs.map.addDistance(refMarker._latlng), true);
    } else {
        tmjs.dom.renderTerminalList(tmjs.map.locations, true);
    }
}

/**
 * Loads terminals by selected filters and shows them in map
 * @param {Object} selectedFilters
 */
function mjvp_filterTerminals(selectedFilters) {
    let tmjs = mjvp_getTmjs();
    let overlayParent = null;
    if (tmjs && tmjs.dom.UI.modal) {
        overlayParent = tmjs.dom.UI.modal.querySelector('.tmjs-modal-content');
    }
    // without parent overlay would cover whole page
    if (overlayParent) {
        tmjs.dom.addOverlay(overlayParent);
    }

    $.ajax({
        type: "POST",
        url: mjvp_front_controller_url + "?ajax=1&submitFilterTerminals=1&action=filter",
        dataType: "json",
        data: {
            'filter_keys' : selectedFilters
        },
        success: function (res) {
            if (res && typeof res.mjvp_terminals != "undefined") {
                mjvp_setMapTerminals(res.mjvp_terminals);
            }
        },
        error: function (jqXHR, status, error) {
            console.error('[MJVP]', 'Failed to filter terminals:', status, error);
        },
        complete: function () {
            // instance could be destroyed and created again while waiting for response
            if (tmjs && overlayParent) {
                tmjs.dom.removeOverlay();
            }
        }
    });
}
