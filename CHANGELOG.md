# Changelog

## [Unreleased]
### Fixed
- fixed scoped CSS selectors in global.css to avoid style conflicts with other modules using the terminal-mapping library
- fixed minor bugs noticed on the map

### Improved
- added configurable pickup point type filter in Advanced settings (admin can select which terminal types — Pickups, Lockers — are shown on the checkout page)
- updated map colors to match current Venipak logo

## [1.2.0] - 2026-04-23
### Fixed
- pickup points template file removed new line from modal element
- fixed a routes error that could occur in certain situations
- fixed country code resolution when delivery address is not yet set, falling back to default shop country
- fixed a bug in the controller calling the translation function
- fixed "Please select a terminal" error message not disappearing after selecting a terminal
- fixed deprecated implicit `window.event` usage in delivery option change handler (caused issues in Firefox)
- fixed misleading indentation in document ready handler

### Improved
- added box-calculator library
- improved cart size calculation functionality using the box-calculator library
- optimized so that terminals are not loaded on the Cart page
- added filtering of the list of pickup points from bad values or when a list of the wrong type is received
- improved so that the delivery method is not displayed on the Checkout page if the list of pickup points is empty
- the module is adapted to work in multistore mode

## [1.1.11] - 2025-10-15
### Fixed
- fixed the "call before delivery" checkbox selection when it's the only parameter on the Checkout page
- fixed the validation function of the selected parcel locker in the PS 1.6 system when using the Paysera payment method
- fixed bulk action controller loading in Prestashop 8.2

### Improved
- adapted working on the "SuperCheckout by Knowband" Onepage Checkout page

## [1.1.10] - 2025-04-09
### Fixed
- fixed the "Set as default warehouse" parameter in Warehouse edit page
- fixed saving of Venipak order data
- fixed error when Carrier object not valid when uninstalling module
- fixed updating of order data if cart data already exists
- fixed map load on Prestashop 1.6
- fixed terminals load on Prestashop 1.6
- fixed manifest download

### Improved
- adapted working on the "SuperCheckout by Knowband" Checkout page
- added additional check just before saving Venipak data to avoid a error where an order is opened twice at the same time

## [1.1.9] - 2024-11-20
### Fixed
- fixed that the Venipak Manifests page could also be viewed by administrators with lower rights

### Improved
- added compatibility with The Checkout (thecheckout) module
- added website and module versions to the request which is sent to Venipak
- admin javascript functions have been moved to a global variable to avoid conflicts with other modules
- added an additional information message when the company name is specified in the order, but the company code is not specified
- improved that italic and bold tags can be used in the message
- added removing spaces before and after API ID value
- added postcode fix for sender

## [1.1.8] - 2024-10-08
### Improved
- added display of error messages when executing mass action
- added possibility to register shipments from/to Poland and Finland.
- added an additional error message if an error is received when registering a shipment due to the Warehouse not was created

## [1.1.7] - 2024-09-23
### Fixed
- changed the event used to initialize the map to be executed only when the page is fully loaded (this avoids the error when JS files are cached at the end of the body element and the map function is tried to be called earlier)

### Improved
- it has been improved to leave the old list of terminals if an error is received during receiving terminals

## [1.1.6] - 2024-07-10
### Fixed
- fixed a error in logs, when in the shipment volume calculation function none of the conditions are met
- fixed errors being received when an error message is received instead of the terminals list

### Improved
- added compatibility with One Page Checkout PS (onepagecheckoutps) module

## [1.1.5] - 2024-03-20
### Fixed
- fixed that the status of orders that do not have errors would not be changed during bulk shipment registration

### Improved
- improved that the error message show the ID of the order that contains the error
- reworked the working of the shipments registration bulk action on the order list

## [1.1.4] - 2024-02-05
### Fixed
- fixed warning message in Venipak Orders page, when order does not have Venipak tracking number
- fixed problem when sometimes warehouse is not assigned to the manifest
- fixed checkout terminal validation event
- fixed hooks in PS 8.x

## [1.1.3] - 2023-09-06
### Fixed
- fixed email sending, when is set on Venipak Order status

### Improved
- the module is adapted to work in the Prestashop 8.x system (tested up to Prestashop 8.1.1)

## [1.1.2] - 2023-04-07
### Fixed
- fixed pickup point carrier show in Checkout on PS 1.6.1

### Improved
- added the option to specify the company name instead of the person name on the label

## [1.1.1] - 2022-11-07
### Fixed
- fixed consignee postcode for Latvia
- fixed Order::getByCartId incompatibility for Prestashop 1.6

## [1.1.0] - 2021-11-23
### Init
- release of the completed module

## [0.1.0] - 2021-08-13
### Added
- created working main functions
