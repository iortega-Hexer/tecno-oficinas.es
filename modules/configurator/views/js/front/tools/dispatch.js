/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author DMConcept <support@dmconcept.fr>
 *  @copyright 2015 DMConcept
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

var CONFIGURATOR = CONFIGURATOR || {};
CONFIGURATOR.TOOLS = CONFIGURATOR.TOOLS || {};

/**
 * Object that allows to registered 'on the fly' new elements
 * First step: Registering
 *  CONFIGURATOR.TOOLS.dispatch.registerObject('key_word_for_object', MyObjectFunction);
 * Second step: when getAssociatedObject is called with 'key_word_for_object' 
 *  initialise new MyObjectFunction
 *  @param {Array} objectsInit array of initial objects to be registered
 */
CONFIGURATOR.TOOLS.dispatch = function(objectsInit){
    //
    // Contains all objects that have been registered
    // 'key'    => Object's element_type
    // 'value'  => Object's constructor, must be a function and will be called 
    //              using the 'new' key word
    //
    var registeredObjects = objectsInit || [];
    
    return {
        /**
         * Registers the given 'object' with 'key'
         * @param {string} key  Object's element_type
         * @param {type} object Associated Object
         */
        registerObject: function(key, object) {
            if (key && typeof(key) === 'string' && object) {
                registeredObjects[key] = object;
            }
        },
        /**
         * 
         * @param {type} key
         * @returns {String|Object} "'Unknown key ' + key" if key has not been registered
         *                          The new object constructor otherwise
         */
        getAssociatedObject: function(key) {
            var obj = "Unknown key: " + key;

            if(registeredObjects[key]){
                obj = registeredObjects[key];
            }

            return obj;
        }
    };  
};