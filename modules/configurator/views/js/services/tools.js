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
/**
 * Package de méthodes utilitaires
 */
CONFIGURATOR.Tools = (function(){
	/**
	 * PRIVATE
	 */
	
	/**
	 * PUBLIC
	 */
    var self = {};

	// Redéfinition de parseInt de ecmascript
	self.parseInt = function(number, default_number) {
		var base = 10;
		default_number = parseInt(default_number,base) || 0;
		return parseInt(number,base) || default_number;
	};

	// Permet de mettre le 1er caractère en majuscule
	self.ucfirst = function(string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	};

	/**
	 * RETURN PUBLIC PROPERTIES AND METHODS
	 */
    return self;
})();