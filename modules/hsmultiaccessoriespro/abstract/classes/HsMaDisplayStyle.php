<?php
/**
 * Display style of the module
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaDisplayStyle
{
    /**
     * Display style is checkbox.
     */
    const CHECKBOX = 0;

    /**
     * Display style is dropdown.
     */
    const DROPDOWN = 1;

    /**
     * Display style is radio.
     */
    const RADIO = 2;

    /**
     * Use default setting display style.
     */
    const USE_DEFAULT = 3;
    
    /**
     * By default: Collapse / Expand accessory groups: None
     */
    const DISPLAY_GROUPS_NONE = 0;
    
    /**
     * Collapse / Expand accessory groups: Expand all accessory groups
     */
    const DISPLAY_GROUPS_EXPAND = 1;
    
    /**
     * Collapse / Expand accessory groups: Expand the first accessory group
     */
    const DISPLAY_GROUPS_EXPAND_FIRST = 2;
    /**
     * Collapse / Expand accessory groups: Collapse all accessory groups
     */
    const DISPLAY_GROUPS_COLLAPSE = 3;
}
