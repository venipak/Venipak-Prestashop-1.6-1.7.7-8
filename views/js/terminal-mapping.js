/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ([
/* 0 */,
/* 1 */
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),
/* 2 */
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DependencyCheck: () => (/* binding */ DependencyCheck)
/* harmony export */ });
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
var DependencyCheck = /*#__PURE__*/function () {
  function DependencyCheck(TMJS) {
    _classCallCheck(this, DependencyCheck);
    this.TMJS = TMJS;
    this.prefix = TMJS.prefix;
    this.leafletJsCdn = "https://unpkg.com/leaflet@1.6.0/dist/leaflet.js";
    this.leafletCssCdn = "https://unpkg.com/leaflet@1.6.0/dist/leaflet.css";
    this.leafletMarkerClusterJsCdn = "https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js";
    this.leafletMarkerClusterCssCdn = "https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css";
    this.leafletMarkerClusterDefaultCssCdn = "https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css";
    /* Max time (ms) to wait for a single script to load. 0 - wait without limit */
    this.timeout = 30000;
    /* How often (ms) to check if script added by someone else is already loaded */
    this.pollInterval = 100;
  }
  _createClass(DependencyCheck, [{
    key: "isLeaflet",
    value: function isLeaflet() {
      return typeof L !== "undefined";
    }
  }, {
    key: "isMarkerCluster",
    value: function isMarkerCluster() {
      return typeof L !== "undefined" && typeof L.markerClusterGroup !== 'undefined';
    }
  }, {
    key: "makeIdFromUrl",
    value: function makeIdFromUrl(url) {
      return url.split('/').pop().replace(/\./gi, '-').toLowerCase();
    }
  }, {
    key: "doNotBlockFn",
    value: function doNotBlockFn(customFn) {
      setTimeout(function () {
        customFn();
      }, 1);
    }

    /**
     * Loads script into page.
     * Callbacks are called asynchronously, only one of them and only once.
     * @param {string} url - Script URL
     * @param {Function} callback - Called when script is loaded
     * @param {Function|null} onError - Called with Error when script fails to load or loading takes longer than `this.timeout`
     * @param {Function|null} isLoaded - Optional check returning true when script is already usable. Used when script tag with same id already exists in page
     */
  }, {
    key: "loadScript",
    value: function loadScript(url, callback) {
      var _this = this;
      var onError = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      var isLoaded = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
      var script_id = this.makeIdFromUrl(url);
      var finished = false;
      var timeoutId = null;
      var pollId = null;
      var script = document.getElementById(script_id);
      var onLoad = function onLoad() {
        script.setAttribute('data-tmjs-state', 'loaded');
        finish();
      };
      var onFail = function onFail() {
        // remove failed script, so next attempt could try loading it again
        if (script.parentNode) {
          script.parentNode.removeChild(script);
        }
        finish(new Error('Failed to load script: ' + url));
      };
      var finish = function finish() {
        var error = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        if (finished) {
          return;
        }
        finished = true;
        clearTimeout(timeoutId);
        clearInterval(pollId);
        if (script) {
          script.removeEventListener('load', onLoad);
          script.removeEventListener('error', onFail);
        }
        if (error) {
          console.error(_this.prefix + error.message);
          if (typeof onError === 'function') {
            _this.doNotBlockFn(function () {
              return onError(error);
            });
          }
          return;
        }
        if (typeof callback === 'function') {
          _this.doNotBlockFn(callback);
        }
      };
      if (this.timeout > 0) {
        timeoutId = setTimeout(function () {
          finish(new Error('Script loading timed out (' + _this.timeout + 'ms): ' + url));
        }, this.timeout);
      }
      if (script) {
        // script already added (by this or other instance, or by other code)
        if (script.getAttribute('data-tmjs-state') === 'loaded' || typeof isLoaded === 'function' && isLoaded()) {
          finish();
          return;
        }
        script.addEventListener('load', onLoad);
        script.addEventListener('error', onFail);

        // script could have finished loading before listeners were added
        if (typeof isLoaded === 'function') {
          pollId = setInterval(function () {
            if (isLoaded()) {
              finish();
            }
          }, this.pollInterval);
        }
        return;
      }
      script = document.createElement("script");
      script.type = "text/javascript";
      script.id = script_id;
      script.setAttribute('data-tmjs-state', 'loading');
      script.addEventListener('load', onLoad);
      script.addEventListener('error', onFail);
      script.src = url;
      document.getElementsByTagName("body")[0].appendChild(script);
    }
  }, {
    key: "loadCSS",
    value: function loadCSS(url) {
      var cssId = this.makeIdFromUrl(url);
      if (document.getElementById(cssId)) {
        return;
      }
      var head = document.getElementsByTagName('head')[0];
      var link = document.createElement('link');
      link.id = cssId;
      link.rel = 'stylesheet';
      link.type = 'text/css';
      link.href = url;
      link.media = 'all';
      head.appendChild(link);
    }
  }, {
    key: "loadScripts",
    value: function loadScripts() {
      var _this2 = this;
      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      var onError = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      this.loadScript(this.leafletJsCdn, function () {
        if (callback && typeof callback === "function") {
          callback();
        }
      }, onError, function () {
        return _this2.isLeaflet();
      });
    }
  }, {
    key: "loadStyles",
    value: function loadStyles() {
      this.loadCSS(this.leafletCssCdn);
    }

    /**
     * Check and if needed loads Leaflet and required Leaflet plugins
     * @param {Function} callback - Function to call when Leaflet and plugins are ready
     * @param {Function} onError - Function to call with Error if Leaflet or plugins could not be loaded
     */
  }, {
    key: "loadLeaflet",
    value: function loadLeaflet() {
      var _this3 = this;
      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      var onError = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      if (this.isLeaflet()) {
        console.info(this.prefix + 'Found Leaflet version:', L.version);
        this.loadLeafletPlugins(callback, onError);
        return;
      }
      console.info(this.prefix + 'Loading Leaflet');
      this.loadScript(this.leafletJsCdn, function () {
        // do not try to load again - it would loop forever if script does not define L
        if (!_this3.isLeaflet()) {
          _this3.handleError(onError, new Error('Leaflet script loaded, but Leaflet (L) is not available'));
          return;
        }
        console.info(_this3.prefix + 'Loaded Leaflet version:', L.version);
        _this3.loadLeafletPlugins(callback, onError);
      }, onError, function () {
        return _this3.isLeaflet();
      });
      // add leaflet css
      this.loadCSS(this.leafletCssCdn);
    }

    /**
     * Check and if needed loads required Leaflet plugins
     * @param {Function} callback - Function to call when finished adding plugins
     * @param {Function} onError - Function to call with Error if plugins could not be loaded
     */
  }, {
    key: "loadLeafletPlugins",
    value: function loadLeafletPlugins() {
      var _this4 = this;
      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      var onError = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      if (this.isMarkerCluster()) {
        console.info(this.prefix + 'Leaflet.markercluster plugin found');
        if (callback && typeof callback === "function") {
          callback();
        }
        return;
      }
      console.info(this.prefix + 'Loading Leaflet.markercluster plugin');
      this.loadScript(this.leafletMarkerClusterJsCdn, function () {
        // do not try to load again - it would loop forever if script does not define plugin
        if (!_this4.isMarkerCluster()) {
          _this4.handleError(onError, new Error('Leaflet.markercluster script loaded, but plugin is not available'));
          return;
        }
        if (callback && typeof callback === "function") {
          callback();
        }
      }, onError, function () {
        return _this4.isMarkerCluster();
      });
      // add Leaflet.markercluster default css
      this.loadCSS(this.leafletMarkerClusterCssCdn);
      this.loadCSS(this.leafletMarkerClusterDefaultCssCdn);
    }
  }, {
    key: "handleError",
    value: function handleError(onError, error) {
      console.error(this.prefix + error.message);
      if (typeof onError === 'function') {
        onError(error);
      }
    }
  }]);
  return DependencyCheck;
}();

/***/ }),
/* 3 */
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DOMManipulator: () => (/* binding */ DOMManipulator)
/* harmony export */ });
/* harmony import */ var _Tools_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(4);
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }


/**
 * DOM manipulation class
 */
var DOMManipulator = /*#__PURE__*/function () {
  /**
   * @param TMJS
   * {string} this.prefix - prefix to use with console messages
   */
  function DOMManipulator(TMJS) {
    _classCallCheck(this, DOMManipulator);
    this.TIMEOUT_DEBOUCE = 1000;
    this.TMJS = TMJS;
    this.prefix = TMJS.prefix;
    this._searchTimeoutId = null;
    this._lastSearchTerm = '';
    this._destroyed = false;
    // consumed once by the next 'list-updated' handling
    this._forceScrollTop = false;
    this.containerParent = null;
    this.modalParent = null;
    this.isModal = true;
    this.hideContainer = true;
    this.hideSelectBtn = false;

    // 'map' (default, modal with leaflet) or 'dropdown' (inline select2-like).
    this.displayMode = 'map';
    this.cssThemeRule = '';

    // extra class(es) added to .tmjs-modal-body, purely a CSS hook for consumers/themes
    this.modalBodyClass = '';
    this.UI = {
      container: null,
      modal: null,
      map: null,
      overlay: null,
      // dropdown mode
      dropdownPanel: null,
      dropdownTrigger: null
    };

    // Terminal list is virtualized: only rows in (and near) the visible part of the list are in DOM
    this.LIST_OVERSCAN = 10;
    this.LIST_MIN_ROWS = 30;
    this.LIST_DEFAULT_HEIGHTS = {
      city: 30,
      terminal: 50
    };
    this._listTerminals = [];
    this._listRows = [];
    // Measured row border box heights and vertical margins, height is NaN for not measured rows
    this._listRowHeights = new Float64Array(0);
    this._listRowMarginTops = new Float64Array(0);
    this._listRowMarginBottoms = new Float64Array(0);
    this._listOffsets = new Float64Array(1);
    this._listRowIndexById = new Map();
    this._listMeasuredSizes = new Map();
    this._listSizeStats = {
      city: {
        count: 0,
        hidden: 0,
        height: 0,
        marginTop: 0,
        marginBottom: 0
      },
      terminal: {
        count: 0,
        hidden: 0,
        height: 0,
        marginTop: 0,
        marginBottom: 0
      }
    };
    // Vertical margins of neighbour rows collapse, unless list is a flex or grid container
    this._listMarginsCollapse = true;
    this._listRange = null;
    this._listRenderedRows = [];
    this._listTopSpacer = null;
    this._listBottomSpacer = null;
    this._listWidth = 0;
    this._listPaddingTop = 0;
    this._listScrollRaf = null;
    this._listResizeObserver = null;
    this._onListScroll = null;
    this._searchTexts = new WeakMap();
    this._searchTextsJobId = 0;
    this._filterTerm = '';
    this._highlightId = null;
    this.registerSubs();
  }
  _createClass(DOMManipulator, [{
    key: "registerSubs",
    value: function registerSubs() {
      var _this = this;
      this.TMJS.sub('terminal-selected', function (data) {
        var el = _this.UI.container.querySelector('.tmjs-selected-terminal');
        if (_this.displayMode === 'dropdown') {
          el.innerHTML = _this._renderSelectedTerminalLabel(data);
          _this._renderDropdownInfo(data);
          // Auto-close dropdown when a terminal is selected via the list.
          _this.closeDropdown();
        } else {
          el.innerText = data.name;
        }
      });
      this.TMJS.sub('terminal-selected-text', function (data) {
        _this.UI.container.querySelector('.tmjs-selected-terminal').innerText = data.text;
      });
      this.TMJS.sub('geolocation', function (coords) {
        var sr = _this._searchResultEl();
        if (sr) sr.innerText = "Lat: ".concat(coords.lat, " Long: ").concat(coords.lng);
      });
      this.TMJS.sub('add-search-loader', function (data) {
        var sr = _this._searchResultEl();
        if (sr) sr.innerHTML = "<div id=\"tmjs-terminals-loader\" class=\"tmjs-loading\"></div>";
      });
      this.TMJS.sub('reset-search-result', function (data) {
        var sr = _this._searchResultEl();
        if (sr) sr.innerText = '';
      });
      this.TMJS.sub('search-result', function (data) {
        var sr = _this._searchResultEl();
        if (sr) sr.innerText = data.address;
        console.info('GEOCODE RESPONSE:', data);
      });
      this.TMJS.sub('list-updated', function (data) {
        if (_this._forceScrollTop) {
          _this._forceScrollTop = false;
          _this.showSelected(false);
          _this.scrollListToTop();
        } else {
          _this.showSelected();
        }
      });
      this.TMJS.sub('close-map-modal', function (data) {
        _this.closeModal();
      });
      this.TMJS.sub('open-map-modal', function (data) {
        _this.openModal();
      });
    }

    /**
     * Returns the search-result info element (works in both map and dropdown modes).
     */
  }, {
    key: "_searchResultEl",
    value: function _searchResultEl() {
      if (this.displayMode === 'dropdown') {
        return this.UI.dropdownPanel ? this.UI.dropdownPanel.querySelector('.tmjs-search-result') : null;
      }
      return this.UI.modal ? this.UI.modal.querySelector('.tmjs-search-result') : null;
    }

    /**
     * Escape HTML special chars to safely interpolate user-supplied text.
     */
  }, {
    key: "_escapeHtml",
    value: function _escapeHtml(str) {
      if (str === null || typeof str === 'undefined') return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /**
     * Build two-line label for the dropdown trigger when a terminal is selected.
     * - If terminal has a name: line 1 = name, line 2 = address (muted highlight color).
     * - If no name: line 1 = address, line 2 = full city group string.
     * Falls back to parseLocationName if provided and no name is set.
     */
  }, {
    key: "_renderSelectedTerminalLabel",
    value: function _renderSelectedTerminalLabel(loc) {
      if (!loc) return '';
      var name = (loc.name || '').toString().trim();
      var address = (loc.address || '').toString().trim();
      var city = (loc.city || '').toString().trim();
      var primary = '';
      var secondary = '';
      if (name) {
        primary = name;
        // Combine address + city for context (e.g. "Ūdrijos g. 1, Alytaus m., Alytaus m. sav., Alytaus apskr.")
        secondary = [address, city].filter(function (s) {
          return s && s.length;
        }).join(', ');
      } else if (typeof this.TMJS.parseLocationName === 'function') {
        // honor custom name parser when no native name is present
        var parsed = (this.TMJS.parseLocationName(loc) || '').toString().trim();
        if (parsed) {
          primary = parsed;
          secondary = city;
        }
      }
      if (!primary) {
        primary = address || city;
        secondary = primary === address ? city : '';
      }
      var html = "<span class=\"tmjs-selected-primary\">".concat(this._escapeHtml(primary), "</span>");
      if (secondary && secondary !== primary) {
        html += "<span class=\"tmjs-selected-secondary\">".concat(this._escapeHtml(secondary), "</span>");
      }
      return html;
    }

    /**
     * Render the info box attached below the dropdown trigger when a terminal is
     * selected. Uses parseLocationComment if provided, otherwise loc.comment.
     * Comment is interpreted as HTML (Omniva-style comments may contain <br>).
     */
  }, {
    key: "_renderDropdownInfo",
    value: function _renderDropdownInfo(loc) {
      if (!this.UI.dropdownInfo) return;
      var comment = '';
      if (loc) {
        if (typeof this.TMJS.parseLocationComment === 'function') {
          comment = this.TMJS.parseLocationComment(loc) || '';
        } else if (loc.comment) {
          comment = loc.comment;
        }
      }
      comment = (comment || '').toString().trim();
      if (!comment) {
        this.UI.dropdownInfo.innerHTML = '';
        this.UI.dropdownInfo.classList.add('tmjs-hidden');
        return;
      }
      this.UI.dropdownInfo.innerHTML = comment;
      this.UI.dropdownInfo.classList.remove('tmjs-hidden');
    }
  }, {
    key: "setContainerParent",
    value: function setContainerParent(el) {
      if (el instanceof HTMLElement) {
        this.containerParent = el;
        this.attachContainerToParent(this.UI.container, el);
        return this;
      }
      console.error(this.prefix + 'Container parent element not changed! Must be HTMLElement');
    }
  }, {
    key: "setModalParent",
    value: function setModalParent(el) {
      if (el instanceof HTMLElement) {
        this.modalParent = el;
        this.attachContainerToParent(this.UI.modal, el);
        return this;
      }
      console.error(this.prefix + 'Modal parent element not changed! Must be HTMLElement');
    }
  }, {
    key: "attachContainerToParent",
    value: function attachContainerToParent(child) {
      var parent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      if (!child) {
        return null;
      }
      if (parent && child) {
        return parent.appendChild(child);
      }

      // default attach to body tag
      return document.body.appendChild(child);
    }

    /**
     * Creates loading overlay (only one active overlay is allowed)
     * When attached to body overlay covers whole page, otherwise it covers only given element.
     * @param {HTMLElement} parentNode - HTML Element to attach overlay to. Defaults to body tag.
     */
  }, {
    key: "addOverlay",
    value: function addOverlay(parentNode) {
      this.removeOverlay();
      // default attach to body tag
      if (parentNode instanceof HTMLElement === false) {
        parentNode = document.body;
      }
      var isFullPage = parentNode === document.body;
      var overlayNode = document.createElement('div');
      overlayNode.className = 'tmjs-loading-overlay ' + (isFullPage ? '' : 'tmjs-loading-overlay-inline ') + this.cssThemeRule;
      overlayNode.innerHTML = '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>';
      this.UI.overlay = parentNode.appendChild(overlayNode);
      this._overlayParent = parentNode;
      this._overlayIsFullPage = isFullPage;
      if (isFullPage) {
        this.bodyOverflow(false);
      } else if (window.getComputedStyle(parentNode).position === 'static') {
        // absolute positioned overlay needs positioned parent
        parentNode.classList.add('tmjs-overlay-parent');
      }
    }

    /**
     * Removes loading overlay
     */
  }, {
    key: "removeOverlay",
    value: function removeOverlay() {
      if (!this.UI.overlay) {
        return;
      }
      if (this.UI.overlay.parentNode) {
        this.UI.overlay.parentNode.removeChild(this.UI.overlay);
      }
      this.UI.overlay = null;
      if (this._overlayIsFullPage) {
        this.bodyOverflow(true);
      } else if (this._overlayParent) {
        this._overlayParent.classList.remove('tmjs-overlay-parent');
      }
      this._overlayParent = null;
      this._overlayIsFullPage = false;
    }

    /**
     * Add or remove `overflow: hidden` style to body tag
     * 
     * true - removes style (default)
     * 
     * false - adds style
     * 
     * @param {boolean} show 
     */
  }, {
    key: "bodyOverflow",
    value: function bodyOverflow() {
      var show = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      show ? document.body.classList.remove('tmjs-hide-overflow') : document.body.classList.add('tmjs-hide-overflow');
    }
  }, {
    key: "addContainer",
    value: function addContainer(id, strings) {
      if (this.displayMode === 'dropdown') {
        this.addDropdownContainer(id, strings);
        return;
      }
      var template = "\n    <div class=\"tmjs-selected-terminal\" data-tmjs-string=\"select_pickup_point\">".concat(strings.select_pickup_point, "</div>\n    <a href=\"#tmjsmodal\" class=\"tmjs-open-modal-btn\" data-tmjs-string=\"modal_open_btn\">").concat(strings.modal_open_btn, "</a>\n    ");
      var container = this.createElement('div', {
        classList: ['tmjs-container', this.cssThemeRule, this.hideContainer ? 'tmjs-hidden' : ''],
        innerHTML: template
      });
      container.id = id;
      this.UI.container = container;
      this.attachContainerToParent(container, this.containerParent);
      this.addModal(id + '_modal', strings);
      this.attachListeners();
    }

    /**
     * Builds the inline dropdown (select2-like) container. No modal/map is rendered.
     */
  }, {
    key: "addDropdownContainer",
    value: function addDropdownContainer(id, strings) {
      // In dropdown mode the row click selects the terminal directly, so the
      // per-row "select" button is redundant and gets hidden by default.
      this.hideSelectBtn = true;
      var template = "\n      <div class=\"tmjs-dropdown\">\n        <div class=\"tmjs-dropdown-trigger\" tabindex=\"0\">\n          <span class=\"tmjs-selected-terminal\" data-tmjs-string=\"dropdown_placeholder\">".concat(strings.dropdown_placeholder, "</span>\n          <span class=\"tmjs-dropdown-arrow\"></span>\n        </div>\n        <div class=\"tmjs-dropdown-info tmjs-hidden\"></div>\n        <div class=\"tmjs-dropdown-panel tmjs-hidden\">\n          <div class=\"tmjs-dropdown-search tmjs-search-field tmjs-d-block\">\n            <input type=\"text\" class=\"tmjs-search-input\" placeholder=\"").concat(strings.dropdown_search_placeholder, "\" data-tmjs-string-attr=\"placeholder|dropdown_search_placeholder\">\n            <a href=\"#findNearest\" class=\"tmjs-search-btn\" title=\"").concat(strings.find_nearest_btn, "\"><img src=\"").concat(this.TMJS.imagePath, "search.svg\" width=\"18\"></a>\n          </div>\n          <div class=\"tmjs-geolocation tmjs-d-block tmjs-pt-1\">\n            <a href=\"#useMyLocation\" class=\"tmjs-geolocation-btn\"><img src=\"").concat(this.TMJS.imagePath, "gps.svg\" width=\"15\"><span data-tmjs-string=\"geolocation_btn\">").concat(strings.geolocation_btn, "</span></a>\n          </div>\n          <div class=\"tmjs-search-result tmjs-d-block tmjs-pt-1\"></div>\n          <ul class=\"tmjs-terminal-list tmjs-dropdown-list\"></ul>\n        </div>\n      </div>\n    ");
      var container = this.createElement('div', {
        classList: ['tmjs-container', 'tmjs-dropdown-mode', this.cssThemeRule, this.hideContainer ? 'tmjs-hidden' : ''],
        innerHTML: template
      });
      container.id = id;
      this.UI.container = container;
      this.UI.dropdownTrigger = container.querySelector('.tmjs-dropdown-trigger');
      this.UI.dropdownPanel = container.querySelector('.tmjs-dropdown-panel');
      this.UI.dropdownInfo = container.querySelector('.tmjs-dropdown-info');
      this.UI.terminalList = container.querySelector('.tmjs-terminal-list');
      this._attachVirtualList(this.UI.terminalList);
      this.attachContainerToParent(container, this.containerParent);
      this.attachDropdownListeners();
    }
  }, {
    key: "updateString",
    value: function updateString(stringName, newValue) {
      var _this2 = this;
      [this.UI.container, this.UI.modal].filter(Boolean).forEach(function (root) {
        root.querySelectorAll("[data-tmjs-string=\"".concat(stringName, "\"]")).forEach(function (el) {
          return el.innerText = newValue;
        });
        root.querySelectorAll("[data-tmjs-string-attr]").forEach(function (el) {
          var spec = el.getAttribute('data-tmjs-string-attr');
          if (!spec) return;
          var _spec$split = spec.split('|'),
            _spec$split2 = _slicedToArray(_spec$split, 2),
            attrName = _spec$split2[0],
            key = _spec$split2[1];
          if (attrName && key && key === stringName) {
            el.setAttribute(attrName, newValue);
          }
        });
      });

      // Terminal list rows that are not in DOM now were not updated, they are created again when shown
      this._listTerminals.forEach(function (loc) {
        if (loc._li && loc._li.parentNode !== _this2.UI.terminalList) {
          loc._li = null;
        }
      });
    }
  }, {
    key: "createElement",
    value: function createElement(tag) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
        innerHTML: '',
        classList: []
      };
      /**
       * @type HTMLElement
       */
      var el = document.createElement(tag);
      if (options.classList instanceof Array && options.classList.length) {
        el.className = options.classList.join(' ');
      }
      if (options.innerHTML) {
        el.innerHTML = options.innerHTML;
      }
      return el;
    }
  }, {
    key: "addModal",
    value: function addModal(id, strings) {
      var close_button_class = this.isModal ? '' : 'tmjs-hidden';
      var template = "\n      <div class=\"tmjs-modal-content\">\n\n        <div class=\"tmjs-modal-body ".concat(this.modalBodyClass, "\">\n          <div class=\"tmjs-map-container\"><div class=\"tmjs-map\"></div></div>\n          <div class=\"tmjs-terminal-sidebar\">\n            <div class=\"tmjs-terminal-finder\">\n              <h2 data-tmjs-string=\"modal_header\">").concat(strings.modal_header, "</h2>\n              <div class=\"tmjs-close-modal-btn ").concat(close_button_class, "\"></div>\n              <h3 class=\"tmjs-pt-2\" data-tmjs-string=\"seach_header\">").concat(strings.seach_header, "</h3>\n\n              <div class=\"tmjs-search-field tmjs-d-block\">\n                <input type=\"text\" class=\"tmjs-search-input\">\n                <a href=\"#search\" class=\"tmjs-search-btn\" ><img src=\"").concat(this.TMJS.imagePath, "search.svg\" width=\"18\"></a>\n              </div>\n\n              <div class=\"tmjs-geolocation tmjs-d-block tmjs-pt-1\">\n                <a href=\"#useMyLocation\" class=\"tmjs-geolocation-btn\"><img src=\"").concat(this.TMJS.imagePath, "gps.svg\" width=\"15\"><span data-tmjs-string=\"geolocation_btn\">").concat(strings.geolocation_btn, "</span></a>\n              </div>\n              <div class=\"tmjs-search-result tmjs-d-block tmjs-pt-2\"></div>\n            </div>\n\n            <div class=\"tmjs-terminal-block\">\n              <h3 data-tmjs-string=\"terminal_list_header\">").concat(strings.terminal_list_header, "</h3>\n              <ul class=\"tmjs-terminal-list\"></ul>\n            </div>\n          </div>\n        </div>\n      </div>\n    ");
      /* MJVP custom: Venipak modal template (views/templates/front/map-template.tpl) with pickup point filters */
      if (typeof mjvp_map_template === 'string' && mjvp_map_template.trim() !== '') {
        template = mjvp_map_template;
      }
      /* MJVP custom end */
      var modal = this.createElement('div', {
        classList: [this.cssThemeRule, this.isModal ? 'tmjs-modal' : 'tmjs-modal-flat', this.isModal ? 'tmjs-hidden' : ''],
        innerHTML: template
      });
      modal.id = id;

      /* if exists destroy and rebuild */
      if (this.UI.modal !== null) {
        this.UI.modal.parentNode.removeChild(this.UI.modal);
        this.UI.modal = null;
      }
      this.UI.modal = modal;
      this.UI.map = modal.querySelector('.tmjs-map');
      this.UI.terminalList = modal.querySelector('.tmjs-terminal-list');
      this._attachVirtualList(this.UI.terminalList);
      this.attachContainerToParent(modal, this.modalParent);
      //document.body.appendChild(this.UI.modal);
    }
  }, {
    key: "attachListeners",
    value: function attachListeners() {
      var _this3 = this;
      this.UI.container.querySelector('.tmjs-open-modal-btn').addEventListener('click', function (e) {
        e.preventDefault();
        _this3.openModal();
      });
      this.UI.modal.querySelector('.tmjs-close-modal-btn').addEventListener('click', function (e) {
        e.preventDefault();
        _this3.closeModal();
      });
      this.UI.modal.querySelector('.tmjs-terminal-list').addEventListener('click', function (event) {
        _this3.handleTerminalListEvents(event, _this3.findTerminalElement(event.target));
      });
      this.UI.modal.querySelector('.tmjs-search-input').addEventListener('keyup', function (e) {
        e.preventDefault();
        e.stopPropagation();
        _this3.searchNearestDebounce(e.target.value, e.keyCode == '13');
      });
      this.UI.modal.querySelector('.tmjs-search-btn').addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        _this3.searchNearest(_this3.UI.modal.querySelector('.tmjs-search-input').value);
      });
      this.UI.modal.querySelector('.tmjs-geolocation-btn').addEventListener('click', function (e) {
        e.preventDefault();
        _this3.useGeolocation();
      });
    }

    /**
     * Attach event listeners for dropdown mode UI.
     */
  }, {
    key: "attachDropdownListeners",
    value: function attachDropdownListeners() {
      var _this4 = this;
      var panel = this.UI.dropdownPanel;
      var trigger = this.UI.dropdownTrigger;
      var searchInput = panel.querySelector('.tmjs-search-input');
      var searchBtn = panel.querySelector('.tmjs-search-btn');
      var geoBtn = panel.querySelector('.tmjs-geolocation-btn');
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (panel.classList.contains('tmjs-hidden')) {
          _this4.openDropdown();
        } else {
          _this4.closeDropdown();
        }
      });
      trigger.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          _this4.openDropdown();
          return;
        }
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          _this4.openDropdown();
          // highlight the first item once the panel is open
          setTimeout(function () {
            return _this4._moveDropdownHighlight(1);
          }, 0);
          return;
        }
        if (e.key === 'Escape') {
          _this4.closeDropdown();
        }
      });
      panel.addEventListener('click', function (e) {
        // prevent panel clicks from bubbling to outside-click handler
        e.stopPropagation();
      });
      panel.querySelector('.tmjs-terminal-list').addEventListener('click', function (event) {
        _this4.handleTerminalListEvents(event, _this4.findTerminalElement(event.target));
      });

      // Mouse hover clears keyboard highlight to avoid double indicators.
      panel.querySelector('.tmjs-terminal-list').addEventListener('mousemove', function () {
        if (_this4._highlightId !== null) {
          _this4._clearDropdownHighlight();
        }
      });
      searchInput.addEventListener('keydown', function (e) {
        // Keyboard navigation through the list.
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          _this4._moveDropdownHighlight(1);
          return;
        }
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          _this4._moveDropdownHighlight(-1);
          return;
        }
        if (e.key === 'Home') {
          e.preventDefault();
          _this4._moveDropdownHighlightTo('first');
          return;
        }
        if (e.key === 'End') {
          e.preventDefault();
          _this4._moveDropdownHighlightTo('last');
          return;
        }
        if (e.key === 'Escape') {
          e.preventDefault();
          _this4.closeDropdown();
          if (_this4.UI.dropdownTrigger) _this4.UI.dropdownTrigger.focus();
          return;
        }
        if (e.key === 'Enter') {
          // If user has highlighted an item with arrows, pressing Enter selects it.
          if (_this4._highlightId !== null) {
            e.preventDefault();
            _this4.setActiveTerminal(_this4._highlightId, false);
            var selected = _this4.TMJS.map.getActiveLocation();
            if (selected) {
              _this4.TMJS.publish('terminal-selected', selected);
            }
            return;
          }
          // Otherwise fall through to keyup handler which triggers geocoding.
        }
      });

      searchInput.addEventListener('keyup', function (e) {
        e.preventDefault();
        e.stopPropagation();
        // ignore navigation keys here (handled in keydown)
        if (['ArrowDown', 'ArrowUp', 'Home', 'End', 'Escape'].indexOf(e.key) !== -1) {
          return;
        }
        if (e.keyCode == 13) {
          // Enter only triggers geocoding when nothing was highlighted.
          if (_this4._highlightId === null) {
            _this4.searchNearest(e.target.value);
          }
          return;
        }
        // Live local text filter.
        _this4._clearDropdownHighlight();
        _this4.filterDropdownList(e.target.value);
      });
      searchBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        _this4.searchNearest(searchInput.value);
      });
      geoBtn.addEventListener('click', function (e) {
        e.preventDefault();
        _this4.useGeolocation();
      });

      // Outside-click closes the dropdown.
      this._outsideClickHandler = function (e) {
        if (!_this4.UI.container.contains(e.target)) {
          _this4.closeDropdown();
        }
      };
      document.addEventListener('click', this._outsideClickHandler);
    }
  }, {
    key: "openDropdown",
    value: function openDropdown() {
      if (!this.UI.dropdownPanel) return this;
      this.UI.dropdownPanel.classList.remove('tmjs-hidden');
      this.UI.container.classList.add('tmjs-dropdown-open');
      this._clearDropdownHighlight();
      // List could not be measured while panel was hidden
      this._renderListRows(true);
      var activeLocation = this.TMJS.map ? this.TMJS.map.getActiveLocation() : null;
      if (activeLocation) {
        this._scrollListToLocation(activeLocation, 'center');
      }
      this.TMJS.publish('modal-opened', true);
      // focus the search input for convenience
      var input = this.UI.dropdownPanel.querySelector('.tmjs-search-input');
      if (input) {
        setTimeout(function () {
          return input.focus();
        }, 0);
      }
      return this;
    }
  }, {
    key: "closeDropdown",
    value: function closeDropdown() {
      if (!this.UI.dropdownPanel) return this;
      if (this.UI.dropdownPanel.classList.contains('tmjs-hidden')) return this;
      this.UI.dropdownPanel.classList.add('tmjs-hidden');
      this.UI.container.classList.remove('tmjs-dropdown-open');
      this.TMJS.publish('modal-closed', true);
      return this;
    }

    /**
     * Local case and diacritics insensitive filter over terminal list (name, address, city, comment).
     * City headers that have no matching terminals are not shown.
     */
  }, {
    key: "filterDropdownList",
    value: function filterDropdownList(searchTerm) {
      if (!this.UI.terminalList) return;
      var term = this._normalizeSearchText((searchTerm || '').trim());
      if (term !== this._filterTerm) {
        this._filterTerm = term;
        this._buildListRows();
        this.UI.terminalList.scrollTop = 0;
        this._renderListRows(true);
      }
      var totalMatches = this._listRowIndexById.size;
      var sr = this._searchResultEl();
      if (sr) {
        if (term !== '' && totalMatches === 0) {
          sr.innerText = this.TMJS.strings.no_terminals_match;
        } else {
          sr.innerText = '';
        }
      }
    }
  }, {
    key: "_normalizeSearchText",
    value: function _normalizeSearchText(text) {
      return String(text || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/ł/g, 'l');
    }
  }, {
    key: "_stripHtml",
    value: function _stripHtml(text) {
      return String(text || '').replace(/<[^>]*>/g, ' ');
    }

    // Normalized once per location, so filtering does not process all texts on every keystroke
  }, {
    key: "_getSearchText",
    value: function _getSearchText(loc) {
      var text = this._searchTexts.get(loc);
      if (typeof text === 'undefined') {
        text = this._normalizeSearchText([this._stripHtml(this._getLocationName(loc)), loc.name, loc.address, loc.city, this._stripHtml(this._getLocationComment(loc))].join('\n'));
        this._searchTexts.set(loc, text);
      }
      return text;
    }
  }, {
    key: "_getHighlightedItem",
    value: function _getHighlightedItem() {
      if (!this.UI.terminalList) return null;
      return this.UI.terminalList.querySelector('li.tmjs-terminal.tmjs-highlight');
    }
  }, {
    key: "_clearDropdownHighlight",
    value: function _clearDropdownHighlight() {
      this._highlightId = null;
      if (!this.UI.terminalList) return;
      var cur = this._getHighlightedItem();
      if (cur) cur.classList.remove('tmjs-highlight');
    }
  }, {
    key: "_setHighlightedRow",
    value: function _setHighlightedRow(rowIndex) {
      var row = this._listRows[rowIndex];
      if (!row || row.type !== 'terminal') return;
      this._highlightId = String(row.loc.id);
      var el = this._scrollListToRow(rowIndex, 'nearest');
      this._refreshListRowsState();
      if (el) {
        el.scrollIntoView({
          block: 'nearest'
        });
      }
    }
  }, {
    key: "_findTerminalRow",
    value: function _findTerminalRow(fromIndex, delta) {
      var rows = this._listRows;
      var count = rows.length;
      for (var step = 0; step < count; step++) {
        var index = ((fromIndex + delta * step) % count + count) % count;
        if (rows[index].type === 'terminal') {
          return index;
        }
      }
      return -1;
    }

    /**
     * Move the keyboard highlight by `delta` positions among visible items.
     * Wraps around at the ends. Opens the dropdown if not already open.
     * If no item is highlighted yet, starts from the currently selected
     * (active) terminal when present, otherwise from the first/last item.
     */
  }, {
    key: "_moveDropdownHighlight",
    value: function _moveDropdownHighlight(delta) {
      if (this.UI.dropdownPanel && this.UI.dropdownPanel.classList.contains('tmjs-hidden')) {
        this.openDropdown();
      }
      var count = this._listRows.length;
      if (!this._listRowIndexById.size) return;
      var current = this._highlightId !== null ? this._listRowIndexById.get(this._highlightId) : undefined;
      if (typeof current === 'undefined') {
        // Use currently active terminal as the starting point if it is visible.
        var active = this.TMJS.map ? this.TMJS.map.getActiveLocation() : null;
        current = active ? this._listRowIndexById.get(String(active.id)) : undefined;
      }
      var index;
      if (typeof current === 'undefined') {
        index = this._findTerminalRow(delta > 0 ? 0 : count - 1, delta > 0 ? 1 : -1);
      } else {
        index = this._findTerminalRow(current + delta, delta > 0 ? 1 : -1);
      }
      this._setHighlightedRow(index);
    }
  }, {
    key: "_moveDropdownHighlightTo",
    value: function _moveDropdownHighlightTo(position) {
      if (!this._listRowIndexById.size) return;
      var index = position === 'last' ? this._findTerminalRow(this._listRows.length - 1, -1) : this._findTerminalRow(0, 1);
      this._setHighlightedRow(index);
    }
  }, {
    key: "openModal",
    value: function openModal() {
      var _this5 = this;
      if (!this.isModal) {
        return this;
      }
      this.bodyOverflow(false);
      this.UI.modal.classList.remove('tmjs-hidden');
      this.TMJS.map.zoomMap();
      // Deferred: 'tmjs-hidden' removal must be applied by the browser before Leaflet can measure the container.
      setTimeout(function () {
        return _this5.TMJS.map.invalidateSize();
      }, 0);
      this.TMJS.publish('modal-opened', true);
      return this;
    }
  }, {
    key: "closeModal",
    value: function closeModal() {
      if (!this.isModal) {
        return this;
      }
      this.bodyOverflow();
      this.UI.modal.classList.add('tmjs-hidden');
      this.TMJS.publish('modal-closed', true);
      return this;
    }
  }, {
    key: "findTerminalElement",
    value: function findTerminalElement(target) {
      if (target.tagName === 'BODY') {
        return null;
      }
      if (target instanceof HTMLElement && target.classList.contains('tmjs-terminal')) {
        return target;
      }
      return target.parentElement ? this.findTerminalElement(target.parentElement) : null;
    }
  }, {
    key: "useGeolocation",
    value: function useGeolocation() {
      if (!navigator.geolocation) {
        console.log('Browser doesnt support geolocation');
      } else {
        //status.textContent = 'Locating…';
        console.log('Getting coords...');
        this.TMJS.publish('add-search-loader');
        navigator.geolocation.getCurrentPosition(this.geoLocationSuccess.bind(this), this.geoLocationError.bind(this));
      }
    }
  }, {
    key: "geoLocationSuccess",
    value: function geoLocationSuccess(position) {
      if (this._destroyed) {
        return;
      }
      this._lastSearchTerm = '';
      var referencePoint = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
      console.log('Your position', referencePoint);
      this.TMJS.map.addReferencePosition(referencePoint);
      this.TMJS.publish('geolocation', referencePoint);
      this.renderTerminalList(this.TMJS.map.addDistance(referencePoint), true);
    }
  }, {
    key: "geoLocationError",
    value: function geoLocationError() {
      var sr = this._searchResultEl();
      if (sr) sr.innerText = this.TMJS.strings.geolocation_not_supported;
      console.log('wasnt able to retrieve position');
    }

    /**
     * 
     * @param {MouseEvent} event 
     * @param {HTMLElement} data 
     */
  }, {
    key: "handleTerminalListEvents",
    value: function handleTerminalListEvents(event, data) {
      event.stopPropagation();
      event.preventDefault();

      // non terminal element
      if (!data) {
        return;
      }
      if (event.target.classList.contains('tmjs-select-btn')) {
        console.log('Trying to select terminal:', data.dataset.id);
        this.TMJS.publish('terminal-selected', this.TMJS.map.getActiveLocation());
        return;
      }

      // In dropdown mode, clicking a row immediately selects the terminal.
      if (this.displayMode === 'dropdown') {
        this.setActiveTerminal(data.dataset.id, false);
        var selected = this.TMJS.map.getActiveLocation();
        if (selected) {
          this.TMJS.publish('terminal-selected', selected);
        }
        return;
      }

      // Everything else assume as selecting terminal

      console.log('Pressed on terminal:', data.dataset.id, data.innerText);
      // seting scrollIntoView as false since we already see it.
      this.setActiveTerminal(data.dataset.id, false);
    }
  }, {
    key: "_getLocationName",
    value: function _getLocationName(loc) {
      if (typeof this.TMJS.parseLocationName === 'function') {
        return this.TMJS.parseLocationName(loc);
      }
      var name = loc.name == null ? '' : "".concat(loc.name, ", ");
      return "".concat(name).concat(loc.address);
    }
  }, {
    key: "_getLocationComment",
    value: function _getLocationComment(loc) {
      if (typeof this.TMJS.parseLocationComment === 'function') {
        return this.TMJS.parseLocationComment(loc);
      }
      return loc['comment'] || null;
    }
  }, {
    key: "_createTerminalElement",
    value: function _createTerminalElement(loc) {
      var selectBtnHidden = this.hideSelectBtn ? 'tmjs-hidden' : '';
      var locationName = this._getLocationName(loc);
      var locationComment = this._getLocationComment(loc);
      var template = "<span class=\"tmjs-terminal-name\">".concat(locationName);
      if (typeof loc.distance != 'undefined' && loc.distance !== null) {
        template += "<span class=\"tmjs-terminal-distance\"><img src=\"".concat(this.TMJS.imagePath, "gps.svg\" width=\"13\">").concat(loc.distance.toFixed(2), " km.</span>");
      }
      template += "</span><div class=\"tmjs-terminal-info\"><p class=\"tmjs-terminal-comment\">";
      if (locationComment) {
        template += "<span class=\"tmjs-info-icon\" style=\"--tmjs-info-icon-url:url('".concat(this.TMJS.imagePath, "info.svg')\"></span>").concat(locationComment);
      }
      template += "</p><a href=\"#terminalSelected\" class=\"tmjs-select-btn ".concat(selectBtnHidden, "\" data-tmjs-string=\"select_btn\">").concat(this.TMJS.strings.select_btn, "</a>\n      </div>\n      ");
      var li = this.createElement('li', {
        classList: ['tmjs-terminal'],
        innerHTML: template
      });
      li.dataset.id = loc.id;
      return li;
    }

    /**
     * Renders terminal list. Only list rows that are in (or near) the visible part of the list are added to DOM,
     * other rows are created when list is scrolled to them.
     * @param {Array} terminals - terminals in the order they should be listed
     * @param {boolean} force - recreate list elements (eg. after distances changed)
     */
  }, {
    key: "renderTerminalList",
    value: function renderTerminalList(terminals) {
      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      this._listTerminals = terminals;
      if (force) {
        terminals.forEach(function (loc) {
          loc._li = null;
        });
        this._listMeasuredSizes.clear();
      }
      this._filterTerm = '';
      this._highlightId = null;
      this._buildListRows();
      if (this.UI.terminalList) {
        this.UI.terminalList.scrollTop = 0;
        this._renderListRows(true);
      }
      if (this.displayMode === 'dropdown') {
        this._prepareSearchTexts();
      }
      this.TMJS.publish('list-updated', this.UI.terminalList);
      return this;
    }

    /**
     * Prepares filter texts while browser is idle, so the first filtering does not need to do it for all terminals.
     */
  }, {
    key: "_prepareSearchTexts",
    value: function _prepareSearchTexts() {
      var _this6 = this;
      var terminals = this._listTerminals;
      var jobId = ++this._searchTextsJobId;
      var schedule = function schedule(callback) {
        if (typeof window.requestIdleCallback === 'function') {
          window.requestIdleCallback(callback, {
            timeout: 2000
          });
        } else {
          setTimeout(callback, 50);
        }
      };
      var offset = 0;
      var prepareChunk = function prepareChunk() {
        if (jobId !== _this6._searchTextsJobId || _this6._destroyed) {
          return;
        }
        var started = Date.now();
        while (offset < terminals.length && Date.now() - started < 20) {
          _this6._getSearchText(terminals[offset++]);
        }
        if (offset < terminals.length) {
          schedule(prepareChunk);
        }
      };
      schedule(prepareChunk);
    }
  }, {
    key: "setActiveTerminal",
    value: function setActiveTerminal(id) {
      var scrollIntoView = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
      if (this.TMJS.map.isActive(id)) {
        console.log('Allready active');
        if (scrollIntoView) {
          this._scrollListToLocation(this.TMJS.map.getActiveLocation(), 'nearest', true);
        }
        return;
      }
      var location = this.TMJS.map.getLocationById(id);
      if (!location) {
        console.log('Location has no List element associated');
        return;
      }
      console.log(location);
      if (this.UI.modal && !this.UI.modal.classList.contains('tmjs-hidden')) {
        // zoomToMarker() can be replaced by modules, so marker must be in cluster layer before it
        this.TMJS.map.loadMarker(location._marker);
        this.TMJS.map.zoomToMarker(location._marker);
      }
      this.TMJS.map.setActiveLocation(location);
      this._refreshListRowsState();
      if (scrollIntoView) {
        this._scrollListToLocation(location, 'nearest', true);
      }
    }
  }, {
    key: "scrollIntoView",
    value: function scrollIntoView(el) {
      el.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'start'
      });
    }
  }, {
    key: "scrollListToTop",
    value: function scrollListToTop() {
      if (this.UI.terminalList) {
        this.UI.terminalList.scrollTop = 0;
        this._renderListRows();
      }
    }
  }, {
    key: "_attachVirtualList",
    value: function _attachVirtualList(list) {
      var _this7 = this;
      this._detachVirtualList();
      if (!list) {
        return;
      }
      this._onListScroll = function () {
        return _this7._scheduleListRender();
      };
      list.addEventListener('scroll', this._onListScroll, {
        passive: true
      });
      if (typeof ResizeObserver !== 'undefined') {
        // Covers list becoming visible (eg. modal or dropdown opened) and width changes
        this._listResizeObserver = new ResizeObserver(function () {
          return _this7._scheduleListRender();
        });
        this._listResizeObserver.observe(list);
      } else {
        window.addEventListener('resize', this._onListScroll);
      }
    }
  }, {
    key: "_detachVirtualList",
    value: function _detachVirtualList() {
      if (this._listScrollRaf) {
        cancelAnimationFrame(this._listScrollRaf);
        this._listScrollRaf = null;
      }
      if (this._listResizeObserver) {
        this._listResizeObserver.disconnect();
        this._listResizeObserver = null;
      }
      if (this._onListScroll) {
        if (this.UI.terminalList) {
          this.UI.terminalList.removeEventListener('scroll', this._onListScroll);
        }
        window.removeEventListener('resize', this._onListScroll);
        this._onListScroll = null;
      }
    }
  }, {
    key: "_scheduleListRender",
    value: function _scheduleListRender() {
      var _this8 = this;
      if (this._listScrollRaf) {
        return;
      }
      this._listScrollRaf = requestAnimationFrame(function () {
        _this8._listScrollRaf = null;
        _this8._renderListRows();
      });
    }

    /**
     * Builds flat list of city header and terminal rows from terminals that match current filter.
     */
  }, {
    key: "_buildListRows",
    value: function _buildListRows() {
      var _this9 = this;
      var rows = [];
      var indexById = new Map();
      var term = this._filterTerm;
      var city = null;
      this._listTerminals.forEach(function (loc) {
        if (term !== '' && _this9._getSearchText(loc).indexOf(term) === -1) {
          return;
        }
        var locCity = String(loc.city == null ? '' : loc.city).toLowerCase();
        if (city !== locCity) {
          city = locCity;
          rows.push({
            type: 'city',
            key: 'c:' + locCity,
            loc: loc
          });
        }
        indexById.set(String(loc.id), rows.length);
        rows.push({
          type: 'terminal',
          key: 't:' + loc.id,
          loc: loc
        });
      });
      this._listRows = rows;
      this._listRowIndexById = indexById;
      this._listRowHeights = new Float64Array(rows.length).fill(NaN);
      this._listRowMarginTops = new Float64Array(rows.length);
      this._listRowMarginBottoms = new Float64Array(rows.length);
      rows.forEach(function (row, index) {
        var size = _this9._listMeasuredSizes.get(row.key);
        if (size) {
          _this9._setListRowSize(index, size);
        }
      });
      this._listRange = null;
      this._computeListOffsets();
    }
  }, {
    key: "_setListRowSize",
    value: function _setListRowSize(index, size) {
      this._listRowHeights[index] = size.height;
      this._listRowMarginTops[index] = size.marginTop;
      this._listRowMarginBottoms[index] = size.marginBottom;
    }
  }, {
    key: "_getListRowSize",
    value: function _getListRowSize(index) {
      var height = this._listRowHeights[index];
      if (height !== height) {
        return this._getEstimatedRowSize(this._listRows[index].type);
      }
      return {
        height: height,
        marginTop: this._listRowMarginTops[index],
        marginBottom: this._listRowMarginBottoms[index]
      };
    }
  }, {
    key: "_getEstimatedRowSize",
    value: function _getEstimatedRowSize(type) {
      var stats = this._listSizeStats[type];
      if (!stats.count) {
        // When measured rows of this type take no space (eg. display: none), others probably do not take it too
        return {
          height: stats.hidden ? 0 : this.LIST_DEFAULT_HEIGHTS[type],
          marginTop: 0,
          marginBottom: 0
        };
      }
      return {
        height: stats.height / stats.count,
        marginTop: stats.marginTop / stats.count,
        marginBottom: stats.marginBottom / stats.count
      };
    }

    // Space between two neighbour rows, CSS collapses their vertical margins in block layout
  }, {
    key: "_joinMargins",
    value: function _joinMargins(marginBottom, marginTop) {
      if (!this._listMarginsCollapse) {
        return marginBottom + marginTop;
      }
      return Math.max(marginBottom, marginTop, 0) + Math.min(marginBottom, marginTop, 0);
    }

    /**
     * Calculates offset of every row as if all rows were in DOM. Offset is the height of top spacer
     * that puts the row (with its top margin) to the same place.
     */
  }, {
    key: "_computeListOffsets",
    value: function _computeListOffsets() {
      var rows = this._listRows;
      var count = rows.length;
      var estimated = {
        city: this._getEstimatedRowSize('city'),
        terminal: this._getEstimatedRowSize('terminal')
      };
      var offsets = new Float64Array(count + 1);
      // Bottom border edge and bottom margin of the last row that takes space
      var bottom = 0;
      var lastMarginBottom = null;
      var emptyFrom = -1;
      for (var i = 0; i < count; i++) {
        var height = this._listRowHeights[i];
        var marginTop = this._listRowMarginTops[i];
        var marginBottom = this._listRowMarginBottoms[i];
        if (height !== height) {
          var size = estimated[rows[i].type];
          height = size.height;
          marginTop = size.marginTop;
          marginBottom = size.marginBottom;
        }
        if (!height && !marginTop && !marginBottom) {
          // Row takes no space (eg. display: none), margins of its neighbours collapse through it
          if (emptyFrom === -1) {
            emptyFrom = i;
          }
          continue;
        }
        var top = lastMarginBottom === null ? marginTop : bottom + this._joinMargins(lastMarginBottom, marginTop);
        offsets[i] = top - marginTop;
        if (emptyFrom !== -1) {
          offsets.fill(offsets[i], emptyFrom, i);
          emptyFrom = -1;
        }
        bottom = top + height;
        lastMarginBottom = marginBottom;
      }
      var end = lastMarginBottom === null ? 0 : bottom + lastMarginBottom;
      if (emptyFrom !== -1) {
        offsets.fill(end, emptyFrom, count);
      }
      offsets[count] = end;
      this._listOffsets = offsets;
    }
  }, {
    key: "_updateListSpacers",
    value: function _updateListSpacers() {
      var offsets = this._listOffsets;
      var start = this._listRange[0];
      // Margins of the last rendered row do not collapse with the bottom spacer
      var renderedEnd = offsets[start];
      for (var i = this._listRange[1] - 1; i >= start; i--) {
        var size = this._getListRowSize(i);
        if (size.height || size.marginTop || size.marginBottom) {
          renderedEnd = offsets[i] + size.marginTop + size.height + size.marginBottom;
          break;
        }
      }
      this._setListSpacerHeight(this._listTopSpacer, offsets[start]);
      this._setListSpacerHeight(this._listBottomSpacer, offsets[this._listRows.length] - renderedEnd);
    }
  }, {
    key: "_setListSpacerHeight",
    value: function _setListSpacerHeight(spacer, height) {
      // Important, so styles of modules for list rows can not change it
      spacer.style.setProperty('height', Math.max(0, height) + 'px', 'important');
    }

    /**
     * Returns index of the row at given distance from the list content top.
     */
  }, {
    key: "_findListRow",
    value: function _findListRow(position) {
      var offsets = this._listOffsets;
      var low = 0;
      var high = this._listRows.length - 1;
      while (low < high) {
        var middle = low + high + 1 >> 1;
        if (offsets[middle] <= position) {
          low = middle;
        } else {
          high = middle - 1;
        }
      }
      return Math.max(0, low);
    }
  }, {
    key: "_isListVisible",
    value: function _isListVisible() {
      var list = this.UI.terminalList;
      return !!list && list.getClientRects().length > 0 && list.clientHeight > 0;
    }
  }, {
    key: "_createListSpacer",
    value: function _createListSpacer() {
      var spacer = document.createElement('li');
      spacer.className = 'tmjs-list-spacer';
      spacer.setAttribute('aria-hidden', 'true');
      return spacer;
    }
  }, {
    key: "_getListRowElement",
    value: function _getListRowElement(row) {
      if (row.type === 'city') {
        if (!row.el) {
          row.el = this.createElement('li', {
            classList: ['tmjs-city']
          });
          /* MJVP custom: city name is shown as is (without toLocaleUpperCase()) */
          row.el.innerText = String(row.loc.city == null ? '' : row.loc.city);
        }
        return row.el;
      }
      var loc = row.loc;
      /* check if we allready have html object, otherwise create new one */
      if (Object.prototype.toString.call(loc._li) != '[object HTMLLIElement]') {
        loc._li = this._createTerminalElement(loc);
      }
      return loc._li;
    }
  }, {
    key: "_refreshListRowsState",
    value: function _refreshListRowsState() {
      var _this10 = this;
      var activeLocation = this.TMJS.map ? this.TMJS.map.getActiveLocation() : null;
      var activeId = activeLocation ? String(activeLocation.id) : null;
      this._listRenderedRows.forEach(function (_ref) {
        var row = _ref.row,
          el = _ref.el;
        if (row.type !== 'terminal') {
          return;
        }
        var id = String(row.loc.id);
        el.classList.toggle('tmjs-active', id === activeId);
        el.classList.toggle('tmjs-highlight', id === _this10._highlightId);
      });
      // Active row has different height
      this._measureListRows();
    }

    /**
     * Adds to DOM only rows that are in (or near) the visible part of the list.
     * @param {boolean} force - rebuild DOM even if visible rows did not change
     */
  }, {
    key: "_renderListRows",
    value: function _renderListRows() {
      var force = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      var list = this.UI.terminalList;
      if (!list) {
        return;
      }
      var isVisible = this._isListVisible();
      if (isVisible && (force || list.clientWidth !== this._listWidth)) {
        var style = window.getComputedStyle(list);
        var marginsCollapse = !/flex|grid/.test(style.display);
        this._listPaddingTop = parseFloat(style.paddingTop) || 0;
        if (list.clientWidth !== this._listWidth || marginsCollapse !== this._listMarginsCollapse) {
          // Row sizes depend on list width and layout
          this._listWidth = list.clientWidth;
          this._listMarginsCollapse = marginsCollapse;
          this._listMeasuredSizes.clear();
          this._listRowHeights.fill(NaN);
          this._computeListOffsets();
          force = true;
        }
      }
      var rows = this._listRows;
      var start = 0;
      var end = Math.min(rows.length, this.LIST_MIN_ROWS);
      if (isVisible) {
        var top = Math.max(0, list.scrollTop - this._listPaddingTop);
        start = Math.max(0, this._findListRow(top) - this.LIST_OVERSCAN);
        end = Math.min(rows.length, this._findListRow(top + list.clientHeight) + 1 + this.LIST_OVERSCAN);
      }
      if (!force && this._listRange && this._listRange[0] === start && this._listRange[1] === end) {
        return;
      }
      this._listRange = [start, end];
      if (!this._listTopSpacer) {
        this._listTopSpacer = this._createListSpacer();
        this._listBottomSpacer = this._createListSpacer();
      }
      this._updateListSpacers();
      var nodes = [];
      if (this._listOffsets[start] > 0) {
        nodes.push(this._listTopSpacer);
      }
      this._listRenderedRows = [];
      for (var i = start; i < end; i++) {
        var el = this._getListRowElement(rows[i]);
        this._listRenderedRows.push({
          row: rows[i],
          index: i,
          el: el
        });
        nodes.push(el);
      }
      nodes.push(this._listBottomSpacer);

      // Elements that stay in the list are not touched, so they keep focus and hover state
      var keep = new Set(nodes);
      Array.from(list.childNodes).forEach(function (node) {
        if (!keep.has(node)) {
          list.removeChild(node);
        }
      });
      var next = list.firstChild;
      nodes.forEach(function (node) {
        if (node === next) {
          next = next.nextSibling;
        } else {
          list.insertBefore(node, next);
        }
      });
      this._refreshListRowsState();
    }

    /**
     * Measures rendered rows. When sizes differ from estimated ones, row offsets are recalculated
     * and list is scrolled so that the visible rows stay in place.
     */
  }, {
    key: "_measureListRows",
    value: function _measureListRows() {
      var _this11 = this;
      if (!this._listRenderedRows.length || !this._isListVisible()) {
        return;
      }
      var list = this.UI.terminalList;
      var anchorPosition = Math.max(0, list.scrollTop - this._listPaddingTop);
      var anchorIndex = this._findListRow(anchorPosition);
      var anchorShift = anchorPosition - this._listOffsets[anchorIndex];
      var changed = false;
      this._listRenderedRows.forEach(function (_ref2) {
        var row = _ref2.row,
          index = _ref2.index,
          el = _ref2.el;
        var style = window.getComputedStyle(el);
        var shown = style.display !== 'none';
        var size = {
          height: shown ? el.getBoundingClientRect().height : 0,
          marginTop: shown ? parseFloat(style.marginTop) || 0 : 0,
          marginBottom: shown ? parseFloat(style.marginBottom) || 0 : 0
        };
        var height = _this11._listRowHeights[index];
        if (height === height && Math.abs(height - size.height) < 0.5 && Math.abs(_this11._listRowMarginTops[index] - size.marginTop) < 0.5 && Math.abs(_this11._listRowMarginBottoms[index] - size.marginBottom) < 0.5) {
          return;
        }
        if (!_this11._listMeasuredSizes.has(row.key)) {
          var stats = _this11._listSizeStats[row.type];
          if (size.height || size.marginTop || size.marginBottom) {
            stats.count++;
            stats.height += size.height;
            stats.marginTop += size.marginTop;
            stats.marginBottom += size.marginBottom;
          } else {
            stats.hidden++;
          }
        }
        _this11._listMeasuredSizes.set(row.key, size);
        _this11._setListRowSize(index, size);
        changed = true;
      });
      if (!changed) {
        return;
      }
      this._computeListOffsets();
      this._updateListSpacers();
      var scrollTop = this._listOffsets[anchorIndex] + anchorShift + this._listPaddingTop;
      if (list.scrollTop > 0 && Math.abs(scrollTop - list.scrollTop) >= 1) {
        list.scrollTop = scrollTop;
      }
      // Visible rows could change after offsets were corrected
      this._scheduleListRender();
    }

    /**
     * Scrolls list so the row is visible and returns its element.
     * @param {number} rowIndex
     * @param {string} align - 'nearest', 'center' or 'top'
     */
  }, {
    key: "_scrollListToRow",
    value: function _scrollListToRow(rowIndex) {
      var align = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'nearest';
      var list = this.UI.terminalList;
      if (!list || rowIndex < 0 || rowIndex >= this._listRows.length || !this._isListVisible()) {
        return null;
      }

      // Offsets of rows that were not rendered yet are estimated, so correct position after they are measured
      for (var pass = 0; pass < 3; pass++) {
        var rowTop = this._listOffsets[rowIndex] + this._listPaddingTop;
        var rowBottom = this._listOffsets[rowIndex + 1] + this._listPaddingTop;
        var viewTop = list.scrollTop;
        var viewHeight = list.clientHeight;
        var target = viewTop;
        if (align === 'center') {
          target = rowTop - (viewHeight - (rowBottom - rowTop)) / 2;
        } else if (align === 'top' || rowTop < viewTop) {
          target = rowTop;
        } else if (rowBottom > viewTop + viewHeight) {
          target = rowBottom - viewHeight;
        }
        target = Math.max(0, target);
        var isRendered = this._listRange && rowIndex >= this._listRange[0] && rowIndex < this._listRange[1];
        if (isRendered && Math.abs(target - viewTop) < 1) {
          break;
        }
        list.scrollTop = target;
        this._renderListRows();
      }
      var rendered = this._listRenderedRows.find(function (item) {
        return item.index === rowIndex;
      });
      return rendered ? rendered.el : null;
    }
  }, {
    key: "_scrollListToLocation",
    value: function _scrollListToLocation(location) {
      var align = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'nearest';
      var smooth = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
      if (!location) {
        return;
      }
      var rowIndex = this._listRowIndexById.get(String(location.id));
      if (typeof rowIndex === 'undefined') {
        return;
      }
      var el = this._scrollListToRow(rowIndex, align);
      if (el && smooth) {
        // Row is already visible in the list, this scrolls page (or other parents) if needed
        this.scrollIntoView(el);
      }
    }
  }, {
    key: "searchNearestDebounce",
    value: function searchNearestDebounce(search) {
      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      clearTimeout(this._searchTimeoutId);
      /* if enter is pressed no need to wait */
      if (force) {
        this.searchNearest(search);
        return;
      }
      this._searchTimeoutId = setTimeout(this.searchNearest.bind(this), this.TIMEOUT_DEBOUCE, search);
    }
  }, {
    key: "resetSearch",
    value: function resetSearch() {
      this._lastSearchTerm = '';
      this.renderTerminalList(this.TMJS.map.resetDistance(), true);
      this.TMJS.map.removeReferencePosition();
      this.TMJS.publish('reset-search-result');
      var activeLocation = this.TMJS.map.getActiveLocation();
      if (activeLocation) {
        this.TMJS.map.loadMarker(activeLocation._marker);
        this.TMJS.map.zoomToMarker(activeLocation._marker);
      }
    }
  }, {
    key: "searchNearest",
    value: function searchNearest(search) {
      var _this12 = this;
      clearTimeout(this._searchTimeoutId);
      /* reset dropdown if search is empty */
      if (!search.length) {
        this.resetSearch();
        return;
      }
      if (search === this._lastSearchTerm) {
        console.log('Search term hasnt changed');
        return;
      }
      this.TMJS.publish('add-search-loader');
      this._lastSearchTerm = search;
      var queryParams = {
        sourceCountry: this.TMJS.country_code ? this.TMJS.country_code : false,
        singleLine: search,
        category: '',
        outFields: 'Postal',
        maxLocations: 1,
        forStorage: 'false',
        f: 'pjson'
      };
      var query = (0,_Tools_js__WEBPACK_IMPORTED_MODULE_0__.makeQueryParams)(queryParams);
      fetch("https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?" + query).then(function (response) {
        if (!response.ok) throw new Error(response.status);
        return response.json();
      }).then(function (json) {
        return _this12.updateDistanceByCandidate(json);
      })["catch"](function (error) {
        return _this12.candidateError(error);
      });
    }
  }, {
    key: "candidateError",
    value: function candidateError(error) {
      console.log(error);
    }
  }, {
    key: "updateDistanceByCandidate",
    value: function updateDistanceByCandidate(json) {
      if (this._destroyed) {
        return false;
      }
      if (typeof json.candidates === 'undefined' || !json.candidates.length) {
        var sr = this._searchResultEl();
        if (sr) sr.innerText = this.TMJS.strings.no_cities_found;
        console.log('Response had no candidates');
        return false;
      }
      var candidates = json.candidates;
      var referencePoint = {
        lat: candidates[0].location.y,
        lng: candidates[0].location.x
      };
      this.TMJS.map.addReferencePosition(referencePoint);
      this._forceScrollTop = true;
      this.renderTerminalList(this.TMJS.map.addDistance(referencePoint), true);
      this.TMJS.publish('search-result', candidates[0]);
      return true;
    }
  }, {
    key: "showSelected",
    value: function showSelected() {
      var scrollIntoView = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      if (this.TMJS.map._activeLocation) {
        this._refreshListRowsState();
        if (scrollIntoView) {
          this._scrollListToLocation(this.TMJS.map._activeLocation, 'nearest', true);
        }
      }
    }

    /**
     * Removes container, modal and overlay from DOM and detaches document level listeners.
     */
  }, {
    key: "destroy",
    value: function destroy() {
      var _this13 = this;
      this._destroyed = true;
      clearTimeout(this._searchTimeoutId);
      this._detachVirtualList();
      if (this._outsideClickHandler) {
        document.removeEventListener('click', this._outsideClickHandler);
        this._outsideClickHandler = null;
      }
      if (this.isModal && this.UI.modal && !this.UI.modal.classList.contains('tmjs-hidden')) {
        this.bodyOverflow(true);
      }
      this.removeOverlay();
      [this.UI.container, this.UI.modal].forEach(function (el) {
        if (el && el.parentNode) {
          el.parentNode.removeChild(el);
        }
      });
      Object.keys(this.UI).forEach(function (key) {
        return _this13.UI[key] = null;
      });
      return this;
    }
  }]);
  return DOMManipulator;
}();

/***/ }),
/* 4 */
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   generateId: () => (/* binding */ generateId),
/* harmony export */   makeQueryParams: () => (/* binding */ makeQueryParams)
/* harmony export */ });
/**
 * Generates random 6 number string
 */
function generateId() {
  return Math.random().toString(36).substr(2, 6);
}
function makeQueryParams(queryParams) {
  var keys = Object.keys(queryParams);
  if (!keys.length) {
    return null;
  }
  return keys.map(function (key) {
    if (queryParams[key] !== false && queryParams[key] !== null && queryParams[key] !== '') {
      return key + '=' + queryParams[key];
    }
    return false;
  }).filter(function (el) {
    return el !== false;
  }).join('&');
}

/***/ }),
/* 5 */
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Map: () => (/* binding */ Map)
/* harmony export */ });
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
var Map = /*#__PURE__*/function () {
  function Map(root, TMJS) {
    _classCallCheck(this, Map);
    this.TMJS = TMJS;
    this.prefix = TMJS.prefix;
    this._tileServerUrl = 'https://www.terminalmappingjs.com/tile/{z}/{x}/{y}.png';
    if (TMJS.customTileServerUrl !== null) {
      this._tileServerUrl = TMJS.customTileServerUrl;
    }
    this._attribution = "\n      &copy; <a href=\"https://www.mijora.lt\">Mijora</a>\n      | Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors,\n      <a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>\n    ";
    if (TMJS.customTileAttribution !== null) {
      this._attribution = TMJS.customTileAttribution;
    }

    /* default map center Lithuania Kaunas */
    this._defaultMapPos = [54.890926, 23.919338];
    this._map = null;
    this._icons = {};
    this._activeIcons = {};
    this.locations = [];
    this._activeLocation = null;
    this._dummyMarker = null;
    this._referenceMarker = null;

    /* markers are added to cluster in chunks, so browser does not freeze with many terminals */
    this.MARKERS_CHUNK_SIZE = 1000;
    this.MARKERS_CHUNK_TIME = 50;
    this.MARKERS_CHUNK_DELAY = 10;
    this._markersLoadId = 0;
    this._markersLoadTimeout = null;
    this.markersLoaded = false;

    /* zoom levels for map */
    this.ZOOM_DEFAULT = 8;
    this.ZOOM_SELECTED = 13;
    this.ZOOM_MAX = 18;
    this.ZOOM_MIN = 4;

    // No leaflet needed in non-map (dropdown) display mode.
    // Distance calculations and location list management work without leaflet.
    if (!root || typeof L === 'undefined') {
      return;
    }

    /* for Icon creation */
    this.Icon = L.Icon.extend({
      options: {
        iconSize: [30, 40],
        iconAnchor: [15, 40],
        popupAnchor: [0, -40]
      }
    });

    // create map
    this.setupLeafletMap(root);
  }
  _createClass(Map, [{
    key: "loadIcons",
    value: function loadIcons() {
      var _this = this;
      this._icons["default"] = new this.Icon({
        iconUrl: this.TMJS.imagePath + 'default_icon.svg'
      });
      this._icons.reference = new this.Icon({
        iconUrl: this.TMJS.imagePath + 'reference_icon.svg'
      });
      if (this.TMJS.apiMode) {
        fetch(this.TMJS.api_server_url + 'parcel_machines_images').then(function (response) {
          return response.json();
        }).then(function (json) {
          json.result.parcel_machines_images.forEach(function (item) {
            return _this._icons[item.identifier] = new _this.Icon({
              iconUrl: item.svg
            });
          });
          // Markers could be created before icons were loaded (they get to cluster layer only after map gets its first view)
          if (_this.locations.length > 0) {
            _this.refreshMarkerIcons();
          }
        })["catch"](function (err) {
          return console.error(_this.prefix + 'Failed to load icons.', err);
        });
      }
    }

    /**
     * @param {string} identifier
     * @param {string} iconUrl
     * @param {object} options - Leaflet L.Icon options (iconSize, iconAnchor, popupAnchor, etc.), overrides defaults for this icon only
     */
  }, {
    key: "createIcon",
    value: function createIcon(identifier, iconUrl) {
      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      if (!this.Icon) {
        return this;
      }
      this._icons[identifier] = new this.Icon(Object.assign({}, options, {
        iconUrl: iconUrl
      }));
      return this;
    }
  }, {
    key: "getIcon",
    value: function getIcon() {
      var identifier = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      if (Object.prototype.hasOwnProperty.call(this._icons, identifier)) {
        return this._icons[identifier];
      }
      return this._icons["default"];
    }

    /**
     * Icon used for the selected (active) terminal marker.
     * Identifier 'default' is used for all terminals that have no active icon for their own identifier.
     * @param {string} identifier
     * @param {string} iconUrl
     * @param {object} options - Leaflet L.Icon options, same as in createIcon()
     */
  }, {
    key: "createActiveIcon",
    value: function createActiveIcon(identifier, iconUrl) {
      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      if (!this.Icon) {
        return this;
      }
      this._activeIcons[identifier] = new this.Icon(Object.assign({}, options, {
        iconUrl: iconUrl
      }));
      return this;
    }
  }, {
    key: "getActiveIcon",
    value: function getActiveIcon() {
      var identifier = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return this._getCreatedActiveIcon(identifier) || this.getIcon(identifier);
    }

    /**
     * Icon created with createActiveIcon() for identifier (or for 'default'), null when there is none.
     */
  }, {
    key: "_getCreatedActiveIcon",
    value: function _getCreatedActiveIcon() {
      var identifier = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      if (Object.prototype.hasOwnProperty.call(this._activeIcons, identifier)) {
        return this._activeIcons[identifier];
      }
      return this._activeIcons["default"] || null;
    }
  }, {
    key: "refreshMarkerIcons",
    value: function refreshMarkerIcons() {
      var _this2 = this;
      if (!this._map) {
        return;
      }
      // Icon is set even if it is the same object, its options could be changed
      this.locations.forEach(function (terminal) {
        if (terminal._marker) {
          terminal._marker.setIcon(_this2.getIcon(terminal.identifier));
        }
      });
      if (this._dummyMarker && this._activeLocation) {
        this._dummyMarker.setIcon(this.getActiveIcon(this._activeLocation.identifier));
      }
      this.updateActiveMarkerClass();
    }
  }, {
    key: "setupLeafletMap",
    value: function setupLeafletMap(rootEl) {
      var _this3 = this;
      this._map = L.map(rootEl, {
        zoomControl: false,
        minZoom: this.ZOOM_MIN,
        maxZoom: this.ZOOM_MAX
      });
      new L.Control.Zoom({
        position: 'bottomright'
      }).addTo(this._map);
      this.loadIcons();
      L.tileLayer(this._tileServerUrl, {
        attribution: this._attribution
      }).addTo(this._map);
      this._markerLayer = L.markerClusterGroup({
        //zoomToBoundsOnClick: false
      });
      this._activeMarkerLayer = L.markerClusterGroup({
        //zoomToBoundsOnClick: false
      });
      this._map.addLayer(this._markerLayer);
      this._map.addLayer(this._activeMarkerLayer);
      this._activeMarkerLayer.on('click', function (e) {
        _this3._map.setView(e.layer.getLatLng(), _this3._map.getZoom());
        _this3.TMJS.dom.setActiveTerminal(e.layer.options.terminalId);
      });
      this._markerLayer.on('click', function (e) {
        _this3._map.setView(e.layer.getLatLng(), _this3._map.getZoom());
        _this3.TMJS.dom.setActiveTerminal(e.layer.options.terminalId);
      });
      this._markerLayer.on('animationend', function () {
        _this3.updateActiveMarkerClass();
      });
      if (!this.TMJS.dom.isModal) {
        this._map.setView(this._defaultMapPos, this.ZOOM_DEFAULT);
      }
      return this;
    }
  }, {
    key: "sortByCity",
    value: function sortByCity(a, b) {
      var result = a.city.toLocaleLowerCase().localeCompare(b.city.toLocaleLowerCase());
      var aName = a['name'] || null;
      if (typeof this.TMJS.parseLocationName === 'function') {
        aName = this.TMJS.parseLocationName(a);
      }
      var bName = b['name'] || null;
      if (typeof this.TMJS.parseLocationName === 'function') {
        bName = this.TMJS.parseLocationName(b);
      }
      if (result == 0 && bName != null) {
        result = aName.toLocaleLowerCase().localeCompare(bName.toLocaleLowerCase());
      } else if (result == 0) {
        result = a.address.toLocaleLowerCase().localeCompare(b.address.toLocaleLowerCase());
      }
      return result;
    }

    /**
     * Sorts locations the same way as sortByCity(), but much faster with many locations:
     * compared texts are prepared once per location and cities are sorted only once.
     */
  }, {
    key: "sortLocationsByCity",
    value: function sortLocationsByCity(locations) {
      var _this4 = this;
      var collator = new Intl.Collator();
      var hasNameParser = typeof this.TMJS.parseLocationName === 'function';
      var toLower = function toLower(value) {
        return (value == null ? '' : String(value)).toLocaleLowerCase();
      };
      var cityGroups = new window.Map();
      locations.forEach(function (loc) {
        var city = toLower(loc.city);
        var name = hasNameParser ? _this4.TMJS.parseLocationName(loc) : loc['name'] || null;
        if (!cityGroups.has(city)) {
          cityGroups.set(city, []);
        }
        cityGroups.get(city).push({
          loc: loc,
          name: name == null ? null : toLower(name),
          address: toLower(loc.address)
        });
      });
      var compareInCity = function compareInCity(a, b) {
        if (b.name != null) {
          return collator.compare(a.name == null ? '' : a.name, b.name);
        }
        return collator.compare(a.address, b.address);
      };
      var index = 0;
      Array.from(cityGroups.keys()).sort(collator.compare).forEach(function (city) {
        cityGroups.get(city).sort(compareInCity).forEach(function (item) {
          locations[index++] = item.loc;
        });
      });
      return locations;
    }
  }, {
    key: "sortByDistance",
    value: function sortByDistance(a, b) {
      return a.distance - b.distance;
    }
  }, {
    key: "setLocations",
    value: function setLocations(locations) {
      // Own deep copies, as library adds its own properties to locations.
      // Marker and list element of already used locations can not be copied.
      this.locations = JSON.parse(JSON.stringify(locations.map(function (location) {
        if (!location || !location._marker && !location._li) {
          return location;
        }
        var copy = Object.assign({}, location);
        delete copy._marker;
        delete copy._li;
        return copy;
      })));
      this.sortLocationsByCity(this.locations);
      if (this._map) {
        this.updateMapMarkers();
      }
    }
  }, {
    key: "addMarker",
    value: function addMarker(latLong, id) {
      var identifier = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
      var className = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
      return L.marker(latLong, {
        icon: this.getIcon(identifier),
        terminalId: id,
        tmjs_map: this,
        className: className
      }).bindPopup(this.markerPopUpInfo);
    }

    /**
    * Finds location by id and puts location comment into popup.
    * TODO: Could be too resource intensive, maybe better rework addMarker to have exclusive parameter for popup text.
    */
  }, {
    key: "markerPopUpInfo",
    value: function markerPopUpInfo(e) {
      var defaultText = '';
      if (typeof e.options.terminalId !== 'undefined') {
        var location = e.options.tmjs_map.getLocationById(e.options.terminalId);
        if (typeof e.options.tmjs_map.TMJS.parseMapTooltip === 'function') {
          return e.options.tmjs_map.TMJS.parseMapTooltip(location, e.getLatLng());
        }
        if (location) {
          var text = location.comment ? location.comment : location.address;
          var distance = location.distance ? " ( ".concat(location.distance.toFixed(2), " km )") : '';
          return "".concat(text, " [ ").concat(e.options.terminalId, " ]").concat(distance);
        }
        defaultText = e.options.terminalId + '<br/>';
      }
      if (typeof e.options.tmjs_map.TMJS.parseMapTooltip === 'function') {
        return e.options.tmjs_map.TMJS.parseMapTooltip(null, e.getLatLng());
      }
      var latLng = e.getLatLng();
      if (!latLng) {
        return '';
      }
      return "".concat(defaultText, "Lat: ").concat(latLng.lat, " Long: ").concat(latLng.lng);
    }
  }, {
    key: "addReferencePosition",
    value: function addReferencePosition(coords) {
      if (!this._map) {
        return;
      }
      this.removeReferencePosition();
      this._referenceMarker = this.addMarker(coords, this.TMJS.strings.your_position, 'reference');
      this._map.addLayer(this._referenceMarker);
      this._map.setView(coords, this.ZOOM_SELECTED);
    }
  }, {
    key: "removeReferencePosition",
    value: function removeReferencePosition() {
      if (!this._map) {
        return;
      }
      if (this._referenceMarker) {
        this._map.removeLayer(this._referenceMarker);
      }
    }
  }, {
    key: "updateMapMarkers",
    value: function updateMapMarkers() {
      var _this5 = this;
      // only update markers if we have terminal list
      if (!this.locations.length) {
        return this;
      }
      this.stopMarkersLoading();
      if (this._markerLayer !== null) {
        this._markerLayer.clearLayers();
      }

      /* add markers to marker layer and link icon in locations list */
      var markers = [];
      this.locations.forEach(function (location) {
        location._marker = _this5.addMarker(location.coords, location.id, location.identifier);
        markers.push(location._marker);
      });

      // Update default center
      this._defaultMapPos = L.latLngBounds(markers.map(function (marker) {
        return marker.getLatLng();
      })).getCenter();

      // Leaflet adds cluster layer to map only when map gets its first view (eg. modal is opened).
      // Until then cluster layer would only collect markers and then cluster all of them at once.
      var loadId = ++this._markersLoadId;
      this._map.whenReady(function () {
        return _this5.addMarkersInChunks(markers, loadId);
      });
      return this;
    }

    /**
     * Adds markers to cluster layer in small parts, letting browser to handle user actions between them.
     * When all markers are added, `map-markers-loaded` event is published.
     * @param {Array} markers
     * @param {number} loadId - loading is stopped when stopMarkersLoading() is called or other loading is started
     */
  }, {
    key: "addMarkersInChunks",
    value: function addMarkersInChunks(markers) {
      var _this6 = this;
      var loadId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : ++this._markersLoadId;
      var offset = 0;
      this.markersLoaded = false;
      var addChunk = function addChunk() {
        _this6._markersLoadTimeout = null;
        if (loadId !== _this6._markersLoadId || !_this6._map) {
          return;
        }
        var started = Date.now();
        while (offset < markers.length && Date.now() - started < _this6.MARKERS_CHUNK_TIME) {
          _this6._markerLayer.addLayers(markers.slice(offset, offset + _this6.MARKERS_CHUNK_SIZE));
          offset += _this6.MARKERS_CHUNK_SIZE;
        }
        if (offset < markers.length) {
          _this6._markersLoadTimeout = setTimeout(addChunk, _this6.MARKERS_CHUNK_DELAY);
          return;
        }
        _this6.markersLoaded = true;
        _this6.updateActiveMarkerClass();
        _this6.TMJS.publish('map-markers-loaded', _this6);
      };
      addChunk();
    }
  }, {
    key: "stopMarkersLoading",
    value: function stopMarkersLoading() {
      this._markersLoadId++;
      if (this._markersLoadTimeout) {
        clearTimeout(this._markersLoadTimeout);
        this._markersLoadTimeout = null;
      }
    }
  }, {
    key: "updateActiveMarkerClass",
    value: function updateActiveMarkerClass() {
      if (this._dummyMarker && this._dummyMarker._icon) {
        // there is dummy with icon
        this._dummyMarker._icon.classList.add('tmjs-active-marker');
      }
      // hide marker in general layer
      if (this._activeLocation && this._activeLocation._marker._icon) {
        this._activeLocation._marker._icon.classList.add('tmjs-active-marker-hidden');
      }
    }
  }, {
    key: "setActiveLocation",
    value: function setActiveLocation(location) {
      if (!this._map) {
        this._activeLocation = location;
        return;
      }
      this.removeActiveMarkerAnimations();
      this._activeLocation = location;
      this.updateActiveMarkerLayer();
      this.updateActiveMarkerClass();
    }
  }, {
    key: "zoomToActiveMarker",
    value: function zoomToActiveMarker() {
      if (!this._map) {
        return;
      }
      this.loadMarker(this._activeLocation._marker);
      this.zoomToMarker(this._activeLocation._marker);
    }
  }, {
    key: "zoomMap",
    value: function zoomMap() {
      if (!this._map) {
        return;
      }
      if (this._activeLocation) {
        this._map.setView(this._activeLocation.coords, this.ZOOM_MAX);
        return;
      }
      this._map.setView(this._defaultMapPos, this.ZOOM_DEFAULT);
    }
  }, {
    key: "zoomToMarker",
    value: function zoomToMarker(marker) {
      if (!this._map || !marker) {
        return;
      }
      this.loadMarker(marker);
      // Clusters are not final while markers are still being added (or cluster layer is not on the map yet)
      if (!this.markersLoaded || !marker.__parent) {
        this._map.setView(marker.getLatLng(), this.ZOOM_MAX);
        return;
      }
      this._markerLayer.zoomToShowLayer(marker);
    }

    /**
     * While markers are still being added to cluster layer in parts, adds given marker right away.
     * Call it before using cluster layer methods for the marker (eg. zoomToShowLayer()).
     * @param {L.Marker} marker
     */
  }, {
    key: "loadMarker",
    value: function loadMarker(marker) {
      if (marker && this._markersLoadTimeout !== null && !this._markerLayer.hasLayer(marker)) {
        this._markerLayer.addLayer(marker);
      }
      return this;
    }
  }, {
    key: "updateActiveMarkerLayer",
    value: function updateActiveMarkerLayer() {
      if (this._dummyMarker) {
        this._map.removeLayer(this._dummyMarker);
      }
      this._dummyMarker = this.addMarker(this._activeLocation._marker._latlng, this._activeLocation.id, this._activeLocation.identifier, 'tmjs-active-marker');
      // Without active icons dummy marker keeps icon given by addMarker() (modules can override it)
      var activeIcon = this._getCreatedActiveIcon(this._activeLocation.identifier);
      if (activeIcon) {
        this._dummyMarker.setIcon(activeIcon);
      }
      this._map.addLayer(this._dummyMarker);
      console.log('Current dummy:', this._dummyMarker);
    }
  }, {
    key: "handleActiveMarkers",
    value: function handleActiveMarkers(location) {
      // no need to do anything if its already active
      if (this._activeLocation == location._marker) {
        return;
      }

      // move current active
      if (this._activeLocation) {
        this._markerLayer.addLayer(this._activeLocation._marker);
        this._activeMarkerLayer.clearLayers();
      }
      // move new active
      this._markerLayer.removeLayer(location._marker);
      this._activeMarkerLayer.addLayer(location._marker);
      console.log('active now', this._activeMarkerLayer.getLayers(), this._activeLocation);
      // add active-marker class
      location._marker._icon.classList.add('tmjs-active-marker');
    }
  }, {
    key: "removeActiveMarkerAnimations",
    value: function removeActiveMarkerAnimations() {
      if (!this.TMJS.dom.UI.modal) {
        return;
      }
      this.TMJS.dom.UI.modal.querySelectorAll('.tmjs-active-marker-hidden').forEach(function (el) {
        return el.classList.remove('tmjs-active-marker-hidden');
      });
    }
  }, {
    key: "getLocationById",
    value: function getLocationById(id) {
      if (!this.locations) {
        return undefined;
      }
      return this.locations.find(function (loc) {
        return loc.id === id;
      });
    }
  }, {
    key: "resetDistance",
    value: function resetDistance() {
      this.locations.forEach(function (loc) {
        loc.distance = null;
      });
      this.sortLocationsByCity(this.locations);
      return this.locations;
    }
  }, {
    key: "addDistance",
    value: function addDistance(origin) {
      var _this7 = this;
      this.locations.forEach(function (loc) {
        loc.distance = _this7.calculateDistance(origin, loc.coords);
      });
      this.locations.sort(this.sortByDistance);
      return this.locations;
    }
  }, {
    key: "deg2rad",
    value: function deg2rad(degress) {
      return degress * Math.PI / 180;
    }
  }, {
    key: "rad2deg",
    value: function rad2deg(radians) {
      return radians * 180 / Math.PI;
    }
  }, {
    key: "calculateDistance",
    value: function calculateDistance(loc1, loc2) {
      var distance = null;
      if (loc1.lat == loc2.lat && loc1.lng == loc2.lng) {
        return 0;
      } else {
        var theta = loc1.lng - loc2.lng;
        var dist = Math.sin(this.deg2rad(loc1.lat)) * Math.sin(this.deg2rad(loc2.lat)) + Math.cos(this.deg2rad(loc1.lat)) * Math.cos(this.deg2rad(loc2.lat)) * Math.cos(this.deg2rad(theta));
        dist = Math.acos(dist);
        dist = this.rad2deg(dist);
        distance = dist * 60 * 1.1515 * 1.609344;
      }
      return distance;
    }
  }, {
    key: "isActive",
    value: function isActive(id) {
      return this._activeLocation && this._activeLocation.id == id;
    }
  }, {
    key: "getActiveLocation",
    value: function getActiveLocation() {
      return this._activeLocation;
    }
  }, {
    key: "getLeafletMap",
    value: function getLeafletMap() {
      return this._map;
    }

    // Needed after the map container becomes visible/resized outside Leaflet's own knowledge (e.g. modal opening from display: none).
  }, {
    key: "invalidateSize",
    value: function invalidateSize() {
      if (this._map) {
        this._map.invalidateSize();
      }
      return this;
    }
  }, {
    key: "destroy",
    value: function destroy() {
      this.stopMarkersLoading();
      if (this._map) {
        this._map.remove();
        this._map = null;
      }
      this._dummyMarker = null;
      this._referenceMarker = null;
      return this;
    }
  }]);
  return Map;
}();

/***/ })
/******/ 	]);
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TerminalMapping: () => (/* binding */ TerminalMapping)
/* harmony export */ });
/* harmony import */ var _styles_main_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(1);
/* harmony import */ var _modules_DependencyCheck_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(2);
/* harmony import */ var _modules_DOMManipulator_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(3);
/* harmony import */ var _modules_Map_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(5);
/* harmony import */ var _modules_Tools_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(4);
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }





var TerminalMapping = /*#__PURE__*/function () {
  function TerminalMapping(api_server_url) {
    var _this = this;
    _classCallCheck(this, TerminalMapping);
    /* Terminal Mapping version */
    this.version = '1.5.0';
    this._isDebug = false;
    this.prefix = '[TMJS] ';

    // Default path to images
    this.imagePath = '';
    this.apiMode = true;

    // Terminal API server URL
    if (typeof api_server_url == 'undefined') {
      // console.error(this.prefix + 'Terminal server API MUST be provided');
      this.apiMode = false;
      // return;
    }

    // Location name and comment parsers
    this.parseLocationName = null;
    this.parseLocationComment = null;
    this.parseMapTooltip = null;
    this.api_server_url = null;
    if (this.apiMode) {
      this.api_server_url = api_server_url + '/';
    }

    // Parcels country and identifier
    this.country_code = null;
    this.identifier = null;
    this.containerId = 'tmjs_' + (0,_modules_Tools_js__WEBPACK_IMPORTED_MODULE_4__.generateId)();

    // Default strings and proxy to detect changes
    this.strings = new Proxy({
      modal_header: 'Terminal map',
      terminal_list_header: 'Terminal list',
      seach_header: 'Search around',
      search_btn: 'Find',
      modal_open_btn: 'Select terminal',
      geolocation_btn: 'Use my location',
      your_position: 'Distance calculated from this point',
      nothing_found: 'Nothing found',
      no_cities_found: 'There were no cities found for your search term',
      geolocation_not_supported: 'Geolocation is not supported',
      select_pickup_point: 'Select a pickup point',
      // Unused strings
      search_placeholder: 'Enter postcode/address',
      workhours_header: 'Workhours',
      contacts_header: 'Contacts',
      no_pickup_points: 'No points to select',
      select_btn: 'select',
      back_to_list_btn: 'reset search',
      no_information: 'No information',
      // Dropdown mode strings
      dropdown_placeholder: 'Choose a pickup point...',
      dropdown_search_placeholder: 'Type to filter or search address',
      find_nearest_btn: 'Find nearest',
      no_terminals_match: 'No terminals match your filter'
    }, {
      set: function set(obj, prop, value) {
        // update DOM
        _this.dom.updateString(prop, value);

        // default functionality
        obj[prop] = value;
        return true;
      }
    });
    this.subscribers = {};
    this._destroyed = false;

    // init() loading state: null (not started), 'loading', 'ready', 'failed'
    this._loadState = null;
    this._loadTimeoutId = null;
    this.depend = new _modules_DependencyCheck_js__WEBPACK_IMPORTED_MODULE_1__.DependencyCheck(this);
    this.dom = new _modules_DOMManipulator_js__WEBPACK_IMPORTED_MODULE_2__.DOMManipulator(this);
    this.map = null;
  }
  _createClass(TerminalMapping, [{
    key: "init",
    value: function init() {
      var _this2 = this;
      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
        _ref$country_code = _ref.country_code,
        country_code = _ref$country_code === void 0 ? null : _ref$country_code,
        _ref$identifier = _ref.identifier,
        identifier = _ref$identifier === void 0 ? null : _ref$identifier,
        _ref$postal_code = _ref.postal_code,
        postal_code = _ref$postal_code === void 0 ? null : _ref$postal_code,
        _ref$city = _ref.city,
        city = _ref$city === void 0 ? null : _ref$city,
        _ref$receiver_address = _ref.receiver_address,
        receiver_address = _ref$receiver_address === void 0 ? null : _ref$receiver_address,
        _ref$isModal = _ref.isModal,
        isModal = _ref$isModal === void 0 ? true : _ref$isModal,
        _ref$modalParent = _ref.modalParent,
        modalParent = _ref$modalParent === void 0 ? null : _ref$modalParent,
        _ref$hideContainer = _ref.hideContainer,
        hideContainer = _ref$hideContainer === void 0 ? false : _ref$hideContainer,
        _ref$hideSelectBtn = _ref.hideSelectBtn,
        hideSelectBtn = _ref$hideSelectBtn === void 0 ? false : _ref$hideSelectBtn,
        _ref$cssThemeRule = _ref.cssThemeRule,
        cssThemeRule = _ref$cssThemeRule === void 0 ? 'tmjs-default-theme' : _ref$cssThemeRule,
        _ref$terminalList = _ref.terminalList,
        terminalList = _ref$terminalList === void 0 ? [] : _ref$terminalList,
        _ref$customTileServer = _ref.customTileServerUrl,
        customTileServerUrl = _ref$customTileServer === void 0 ? null : _ref$customTileServer,
        _ref$customTileAttrib = _ref.customTileAttribution,
        customTileAttribution = _ref$customTileAttrib === void 0 ? null : _ref$customTileAttrib,
        _ref$parseLocationNam = _ref.parseLocationName,
        parseLocationName = _ref$parseLocationNam === void 0 ? null : _ref$parseLocationNam,
        _ref$parseLocationCom = _ref.parseLocationComment,
        parseLocationComment = _ref$parseLocationCom === void 0 ? null : _ref$parseLocationCom,
        _ref$parseMapTooltip = _ref.parseMapTooltip,
        parseMapTooltip = _ref$parseMapTooltip === void 0 ? null : _ref$parseMapTooltip,
        _ref$displayMode = _ref.displayMode,
        displayMode = _ref$displayMode === void 0 ? 'map' : _ref$displayMode,
        _ref$modalBodyClass = _ref.modalBodyClass,
        modalBodyClass = _ref$modalBodyClass === void 0 ? '' : _ref$modalBodyClass,
        _ref$overlayParent = _ref.overlayParent,
        overlayParent = _ref$overlayParent === void 0 ? null : _ref$overlayParent,
        _ref$loadTimeout = _ref.loadTimeout,
        loadTimeout = _ref$loadTimeout === void 0 ? 30000 : _ref$loadTimeout;
      this.country_code = country_code;
      this.identifier = identifier;
      this.dom.isModal = isModal;
      this.dom.hideContainer = hideContainer;
      this.dom.hideSelectBtn = hideSelectBtn;
      this.dom.cssThemeRule = cssThemeRule;
      this.dom.displayMode = displayMode === 'dropdown' ? 'dropdown' : 'map';
      this.dom.modalBodyClass = modalBodyClass;
      this.customTileServerUrl = customTileServerUrl;
      this.customTileAttribution = customTileAttribution;
      if (parseLocationName !== null) {
        this.parseLocationName = parseLocationName;
      }
      if (parseLocationComment) {
        this.parseLocationComment = parseLocationComment;
      }
      if (parseMapTooltip) {
        this.parseMapTooltip = parseMapTooltip;
      }
      if (modalParent) {
        this.dom.setModalParent(modalParent);
      }
      console.info(this.prefix + 'Initializing Terminal Mapping');
      this._loadState = 'loading';
      this.dom.addOverlay(overlayParent);
      this.dom.addContainer(this.containerId, this.strings);
      loadTimeout = parseInt(loadTimeout, 10);
      if (isNaN(loadTimeout) || loadTimeout < 0) {
        loadTimeout = 0;
      }
      this.depend.timeout = loadTimeout;
      var isLoading = function isLoading() {
        return !_this2._destroyed && _this2._loadState === 'loading';
      };
      var stopLoading = function stopLoading(state) {
        _this2._loadState = state;
        clearTimeout(_this2._loadTimeoutId);
        _this2._loadTimeoutId = null;
        _this2.dom.removeOverlay();
      };
      var completeLoad = function completeLoad(terminals) {
        if (!isLoading()) {
          return;
        }
        try {
          _this2.setTerminals(terminals);
          _this2.dom.renderTerminalList(_this2.map.locations);
        } catch (error) {
          failLoad(error);
          return;
        }
        console.info(_this2.prefix + 'Terminals loaded');
        stopLoading('ready');
        _this2.publish('tmjs-ready', _this2);
      };

      // loading failed - remove overlay so it does not block the page and let integrator know
      var failLoad = function failLoad(error) {
        if (!isLoading()) {
          return;
        }
        if (!(error instanceof Error)) {
          error = new Error(String(error));
        }
        console.error(_this2.prefix + 'Terminal Mapping failed to load: ' + error.message);
        stopLoading('failed');
        _this2.publish('tmjs-load-error', {
          error: error,
          tmjs: _this2
        });
      };
      if (loadTimeout > 0) {
        this._loadTimeoutId = setTimeout(function () {
          failLoad(new Error('Loading timed out (' + loadTimeout + 'ms)'));
        }, loadTimeout);
      }
      var finishLoad = function finishLoad() {
        if (!isLoading()) {
          return;
        }
        if (!_this2.apiMode) {
          completeLoad(terminalList);
          return;
        }

        // Get terminal list in API mode
        var params = (0,_modules_Tools_js__WEBPACK_IMPORTED_MODULE_4__.makeQueryParams)({
          'q[country_code_eq]': country_code,
          'q[identifier_eq]': identifier,
          'country_code': country_code,
          'postal_code': postal_code,
          'city': city,
          'receiver_address': receiver_address
        });
        fetch(_this2.api_server_url + 'parcel_machines' + (params ? '?' + params : '')).then(function (response) {
          if (!response.ok) {
            throw new Error('Terminal API responded with status ' + response.status);
          }
          return response.json();
        }).then(function (json) {
          if (!isLoading()) {
            return;
          }
          if (!json || !json.result || !Array.isArray(json.result.parcel_machines)) {
            throw new Error('Terminal API returned unexpected response');
          }
          var terminals = json.result.parcel_machines.map(function (terminal) {
            terminal['coords'] = {
              lat: terminal.y_cord,
              lng: terminal.x_cord
            };
            return terminal;
          });
          completeLoad(terminals);
        })["catch"](failLoad);
      };
      if (this.dom.displayMode === 'dropdown') {
        // Dropdown mode does not need leaflet/map UI.
        this.map = new _modules_Map_js__WEBPACK_IMPORTED_MODULE_3__.Map(null, this);
        finishLoad();
        return;
      }

      // load check for leaflet and plugins first
      this.depend.loadLeaflet(function () {
        if (!isLoading()) {
          return;
        }
        try {
          _this2.map = new _modules_Map_js__WEBPACK_IMPORTED_MODULE_3__.Map(_this2.dom.UI.map, _this2);
        } catch (error) {
          failLoad(error);
          return;
        }
        finishLoad();
      }, failLoad);
    }

    /**
     * Returns init() loading state: null (init not called), 'loading', 'ready' or 'failed'
     * @returns {string|null}
     */
  }, {
    key: "getLoadState",
    value: function getLoadState() {
      return this._loadState;
    }
  }, {
    key: "setImagesPath",
    value: function setImagesPath() {
      var path = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      this.imagePath = path;
      return this;
    }
  }, {
    key: "setTranslation",
    value: function setTranslation() {
      var newStrings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      this.strings = Object.assign(this.strings, newStrings);
      console.info(this.prefix + 'Translation updated');
      return this;
    }
  }, {
    key: "setTerminals",
    value: function setTerminals(terminals) {
      this.map.setLocations(terminals);
      return this;
    }
  }, {
    key: "invalidateMapSize",
    value: function invalidateMapSize() {
      if (this.map) {
        this.map.invalidateSize();
      }
      return this;
    }

    /**
     * Removes instance elements from DOM, Leaflet map and all event subscribers.
     * Instance can not be used after this, create new one if needed.
     */
  }, {
    key: "destroy",
    value: function destroy() {
      if (this._destroyed) {
        return this;
      }
      this.publish('tmjs-destroy', this);
      this._destroyed = true;
      clearTimeout(this._loadTimeoutId);
      this._loadTimeoutId = null;
      this.dom.destroy();
      if (this.map) {
        this.map.destroy();
      }
      this.subscribers = {};
      return this;
    }
  }, {
    key: "setParseLocationName",
    value: function setParseLocationName() {
      var parseLocationName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      this.parseLocationName = parseLocationName;
      return this;
    }
  }, {
    key: "setParseLocationComment",
    value: function setParseLocationComment() {
      var parseLocationComment = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      this.parseLocationComment = parseLocationComment;
      return this;
    }
  }, {
    key: "setParseMapTooltip",
    value: function setParseMapTooltip() {
      var parseMapTooltip = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      this.parseMapTooltip = parseMapTooltip;
      return this;
    }
  }, {
    key: "sub",
    value: function sub(eventName, callback) {
      if (!this.subscribers[eventName]) {
        this.subscribers[eventName] = [];
      }
      this.subscribers[eventName].push(callback);
      return this;
    }
  }, {
    key: "unsub",
    value: function unsub(eventName, reference) {
      if (!this.subscribers[eventName]) {
        return true;
      }
      this.subscribers[eventName] = this.subscribers[eventName].filter(function (callback) {
        return callback !== reference;
      });
    }
  }, {
    key: "publish",
    value: function publish(eventName, data) {
      if (!this.subscribers[eventName]) {
        return;
      }
      this.subscribers[eventName].forEach(function (callback) {
        return callback(data);
      });
    }
  }]);
  return TerminalMapping;
}();
var tmjsGlobalName = typeof window.TMJS_GLOBAL_NAME === 'string' && window.TMJS_GLOBAL_NAME.trim() ? window.TMJS_GLOBAL_NAME.trim() : 'TerminalMapping';
window.TMJS_GLOBAL_NAME = undefined;
window[tmjsGlobalName] = TerminalMapping;
})();

/******/ })()
;