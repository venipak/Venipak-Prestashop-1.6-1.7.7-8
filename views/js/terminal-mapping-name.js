/**
 * Must be loaded right before views/js/terminal-mapping.js.
 * terminal-mapping library registers itself under this global name (window.TerminalMappingMjvp) and clears the value,
 * so other modules using the same library can set their own name and do not conflict with Venipak copy.
 */
window.TMJS_GLOBAL_NAME = 'TerminalMappingMjvp';
