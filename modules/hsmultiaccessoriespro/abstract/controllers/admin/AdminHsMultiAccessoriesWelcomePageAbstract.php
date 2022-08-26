<?php
/**
 * Multi Accessories for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once dirname(__FILE__) . '/AbstractAdminHsMultiAccessoriesCommon.php';

/**
 * Controller of admin page - Multi Accessories (Abstract).
 */
class AdminHsMultiAccessoriesWelcomePageAbstract extends AbstractAdminHsMultiAccessoriesCommon
{
    protected $change_logs = array(
        'v.4.5.0' => array(
            '[Fixed] Show wrong main product combination price when opening product page.',
            '[Fixed] Error 500 from Google Search Console API',
            '[Fixed] Can not save accessory name if it is too long',
            '[Changed] Change the label name on the module setting page',
        ),
        'v.4.4.0' => array(
            '[Fixed] Error undefined value setting_buy_together on the cart summary page',
            '[Fixed] Error undefined value ajaxCart on PrestaShop 1.7.x',
            '[Added] Add a new tab: Quickly fix common errors on module settings page',
            '[Improvement] Make module compatible with PS 1.7.8.4',
            '[Feature] Add a button to fix the error:  Missing accessory images at the front end.',
            '[Feature] Add a button to fix the error: Does not show the settings block on the module settings page.',
            '[Updated] Update user guide.'
        ),
        'v.4.3.2' => array(
            '[Fixed] Conflict module style with PrestaShop version 1.7.8.x',
            '[Fixed] Strange character for accessory name due to wrong charset setting',
            '[Improvement] Show the specific price based on the available cart rule',
            '[Improvement] Update user guide'
        ),
        'v.4.3.1' => array(
            '[Fixed] Fix the error undefined the product object when importing products from file CSV.',
            '[Fixed] Show wrong the product combination name in the price table.',
            '[Fixed] Does not delete the main product that has customization when deleting an accessory in the cart.',
            '[Added] Delete all related discounts of an accessory before adding a new cart rule for multi-products and accessory',
            '[Added] Remove js file multi_accessories.js',
            '[Feature] Add the button plus/minus for the accessory quantity box'
        ),
        'v.4.3.0' => array(
            '[Fixed] Wrong cart quantity (+1) when changing accessory quantity from 1 to 0 at the cart summary page.',
            '[Fixed] Module does not work on Safari browser',
            '[Fixed] Wrong currency format from version 1.7.6',
            '[Added] Work with a theme that has not quantity input.',
            '[Added] Show confirmation message before deleting all old accessories at the module setting page.',
            '[Feature] Replace an accessory with another one for all products.',
            '[Feature] Show Package content in the block accessories with package product.'
        ),
        'v.4.2.0' => array(
            '[Fixed] Update translation for the French language',
            '[Fixed] Does not add accessories to cart on PrestaShop version 1.7',
            '[Fixed] Does not show the total price in the product list when turning on the URL friendly.',
            '[Fixed] Show the specific price based on the accessory combination.',
            '[Fixed] Break the layout in the product edit page on PrestaShop version 1.7',
            '[Feature] Add discount for multi main products and an accessory.',
            '[Feature] Add the setting "Buy main product & accessories" for the accessory group.',
        ),
        'v.4.1.0' => array(
            '[Fixed] Problem of refreshing the customization fields.',
            '[Fixed] Does not add an accessory to cart when clicking on the icon "Add to cart" in the block accessories.',
            '[Improvement] Remove hard code on the accessory thumbnail image.',
            '[Improvement] Remove default image, customization for the drop-down type if none selected accessory.',
            '[Improvement] Show message "Data saved" after changing product setting, assigning accessories, removing discount at the back office.',
            '[Improvement] Improve performance by optimizing sql query.',
            '[Improvement] Update user guide.',
            '[Feature] Change accessories quantity when changing main product quantity.',
            '[Feature] Copy related products (accessories) to Multi accessories module.'
        ),
        'v.4.0.1' => array(
            '[Fixed] Undefined value PriceTable.instance in the product list page (Prestashop version 1.5 & 1.6)',
        ),
        'v.4.0.0' => array(
            '[Feature] Support accessories customization.',
            '[Feature] Disable button Add to cart if a product has accessory customization in the product list page (Prestashop version 1.6).',
            '[Fixed] Show wrong accessory price when changing accessory quantity.',
            '[Fixed] Show wrong accessory combination price when changing accessory combination.'
        ),
        'v.3.2.1' => array(
            '[Fixed] Error 500 when filter product & accessory in the setting module page',
        ),
        'v.3.2.0' => array(
            '[Fixed] Show wrong accessory combination image',
            '[Fixed] Delete accessories does not correct if multi products have the same an accessory.',
            '[Fixed] Automatically enabled the button Add to cart in the case main product is out of stock and deny orders when out of stock (PS 1.7.x).',
            '[Fixed] Undefined value is_available_when_out_of_stock',
            '[Fixed] Does not update new accessory price if it is equal to 0.',
            '[Fixed] The currency format is wrong',
        ),
        'v.3.1.0' => array(
            '[Fixed] Get wrong available accessory quantity',
            '[Added] Delete accessories when delete an accessory group.',
            '[Added] Delete accessories when delete a product or product attribute.',
        ),
        'v.3.0.2' => array(
            '[Fixed][Front end] Does not add each accessory to basket on Internet Exploder browser (PrestaShop version 1.7)',
            '[Fixed][Back office] Does not add a new accessory again after removing it (Prestashop version 1.5, 1.6)',
            '[Fixed][Back office] Count wrong total accessories in the case turn off the option "Display NoWhere Visibility products at the front end".',
        ),
        'v.3.0.1' => array(
            '[Fixed] Break tabs in the edit product page on PrestaShop version 1.7.4',
            '[Added] An option to Show|hide NoWhere Visibility products at the front end.',
        ),
        'v.3.0.0' => array(
            '[Fixed] Cannot sort group accessories',
            '[Fixed] Break the layout of block product setting on 1.7.3.x',
            '[Fixed] Show wrong total price (main product & accessories) at the product list page.',
            '[Fixed] Does not add main product & accessories to cart when move the block accessories above the button Add to cart on PrestaShop 1.7.x',
            '[Added] Change accessory name, cart rule name when change product name',
            '[Added] Module footer (Copyright, documentation, rate us, need help)',
            '[Added] PrestaTrust',
            '[Improvement] Redesign block accessories for PrestaShop 1.7.x',
            '[Improvement] Show message "Data saved" when change discount value, accessory name, quantity... at the back office',
            '[Improvement] Redesign block accessories at the front end for PrestaShop 1.7.x',
            '[Improvement] Move the option "Collapse/expand accessory groups" from general setting to group.',
            '[Feature] Add the option: Show | hide the group accessory name at the front end.',
            '[Feature] Add the option: Delete all accessories and main product when delete the main product.',
            '[Feature] Add the option: Delete all accessories and main product when delete an accessory.',
            '[Feature] Sort accessories in the setting page.',
            '[Feature] Show image fancy box in the case display type of group is dropdown.',
        ),
        'v.2.10.5' => array(
            '[Fixed] Does not duplicate accessory discounts when duplicate a product.',
        ),
        'v.2.10.4' => array(
            '[Fixed] Count wrong accessories in the case delete accessory group.',
            '[Fixed] Load js, css files based on setting'
        ),
        'v.2.10.3' => array(
            '[Fixed] Error tableDnD is not a function',
            '[Fixed] Error is_available_when_out_of_stock undefined'
        ),
        'v.2.10.2' => array(
            '[Improvement] Copy discount values when copying accessories from another product.',
            '[Improvement] Show total price (main product price + required accessories price) instead of the main product price at the product list page.',
            '[Fixed] Show the price of accessory with different customer groups.'
        ),
        'v.2.10.1' => array(
            '[Added] Display accessories & combinations out of stock at the front end.',
            '[Added] Show icon warning if combination of accessory is out of in the case display type of group is dropdown.',
            '[Added] Show icon warning if accessory & combination is out of stock but backordering is allowed in the case display type of group is dropdown.',
            '[Fixed] Error 500 when filter accessories by categories.'
        ),
        'v.2.10.0' => array(
            '[Fixed] Does not add accessories to cart in the case dropdown style (1.7.x)',
            '[Fixed] Doesn not install module on php 5.4',
            '[Improvement] Add multi accessories for multi products(PS 1.6 & 1.7).',
            '[Improvement] Delete main product & all accessories when delete an accessory in the basket.'
        ),
        'v.2.9.2' => array(
            '[Fixed] Does not add accessories in the case enable SSL at the back office',
            '[Fixed] Replace all accessories image by main product\'s image when customer changes the product combination, quantity ...',
            '[Fixed] Does not show icon warning out of stock in the case display type of group is dropdown.'
        ),
        'v.2.9.1' => array(
            '[Improvement] Make module compatible with PS 1.7.x.',
            '[Fixed] Error idCombination undefined with product without combination.',
            '[Add] Frontend - Collapse / Expand accessory groups'
        ),
        'v.2.9.0' => array(
            '[Feature] Add default accessory along with main product everywhere',
            '[Feature] Delete all accessories when delete main product item on basket',
            '[Added] An option to NOT show out-of-stock item at front-end',
            '[Fixed] Does not show input quantity in case display style radio and check box when the option "Allow your customers to change item quantity" is No',
            '[Fixed] Show group & accessories if the group is active',
            '[Fixed] Adding wrong accessories quantity in case display style is drop down',
            '[Fixed] Show message out of stock with in accessories in the cart',
            '[Fixed] Cannot sort accessories'
        ),
        'v.2.8.15' => array(
            '[Fixed] Delete accessory & main product cart rule after deleting cart rule'
        ),
        'v.2.8.14' => array(
            '[Fixed] Add quantity to cart does not correct in the case display style is dropdown.'
        ),
        'v.2.8.13' => array(
            '[Added] Display changelog in welcome page.',
            '[Fixed] Error when duplicate product'
        ),
        'v.2.8.12' => array(
            '[Fixed] Cross-out line on old price is not applicable',
            '[Fixed] Can\'t display right price of accessories if they don\'t have combinations',
            '[Fixed] Quantity of accessories are not based on main product'
        ),
        'v.2.8.11' => array(
            '[Fixed] Discount does not work in single shop (MS is not activated)',
            '[Fixed] Cannot access to Configuration page in PrestaShop 1.5.3.1',
            '[Changed] Accept min quantity and default quantity as 0'
        ),
        'v.2.8.10' => array(
            '[Fixed] Add discounts to shops which are enabled Multi Accessories Pro module only',
            '[Fixed] Hook "hookActionAdminProductsFormModifier" is not supported by PS1.5',
            '[Fixed] Strange character for accessory name due to wrong charset setting',
            '[Fixed] Remove discount amount',
        ),
        'v.2.8.9' => array(
            '[Fixed] Product\'s combination does not show when display style is "Dropdown" and option "Show images" is disabled',
            '[Fixed] Cannot change default option when enable Buy main product & accessories together',
            '[Fixed] Do not force visitors to buy accessory in case of radio / drop down style',
            '[Added] Display combination for selected accessories in price table',
        ),
        'v.2.8.8' => array(
            '[Fixed] Remove ui.tooltip - which is not in use and leads to js conflict.',
            '[Fixed] Add amount discount for accessory with or without tax based on current configuration',
            '[Fixed] Cannot copy accessories due to js error'
        ),
        'v.2.8.7' => array(
            '[Fixed] Can not sort groups if we move a group to 2nd, 3rd... position',
            '[Fixed] Disable behavior that scrolling to price table when option price table is disabled',
            '[Fixed] Empty accessory tab when there is no accessory products',
            '[Changed] Improve performance by optimizing the way to get product images',
            '[Fixed] Amount discount is not applied on PrestaShop version 1.6.0.9',
            '[Changed] Improve functions of changing discounts at back office',
            '[Added] Copy accessories from other products',
            '[Added] Specify custom quantity by admin'
        ),
        'v.2.8.6' => array(
            '[Fixed] Improve function updating main price so that it\'s stable on all versions of PrestaShop',
            '[Added] Support hook \'product tab\' and hook \'product tab content\'',
            '[Fixed] Price is rounded in a wrong way on PS 1.6.0.9'
        ),
        'v.2.8.5' => array(
            '[Fixed] Duplicated image type when re-installing the module',
            '[Fixed] Conflicts between hsmultiaccessoriespro and cronjob module',
            '[Fixed] There is no combination at Front-end and Back-end in case product has combinations but no combination is marked as default',
        ),
        'v.2.8.4' => array(
            '[Added] Allow adding discount for accessory items',
            '[Fixed] Fix break price into 2 lines on price table on mobile view',
            '[Fixed] If accessory has no combination, at front-end, price table always displays price is 0',
        ),
        'v.2.8.3' => array(
            '[Added] Show image in dropdown',
            '[Changed] Auto scroll to price table when accessory list is too long',
        ),
        'v.2.8.2' => array(
            '[Fixed] Get wrong accessory image',
            '[Fixed] Add 2 pieces of the main item to basket - conflict with theme Transformer',
            '[Fixed] Change main price when changing main product\'s combinations',
        ),
        'v.2.8.1' => array(
            '[Fix] Make accessories sortable when add new accessories',
            '[Fix] Does not update the unit price in case changing combination of main product, which has not accessory',
            '[Fix] Undefined index: combination',
        ),
        'v.2.8.0' => array(
            '[Feature] Make accessory groups sortable',
            '[Feature] Make accessories sortable',
        ),
        'v.2.7.0' => array(
            'Search accessories in multi shop',
            'Redesign layout of Multi Accessories tab in product page',
            'Show icon warning if accessory is out of stock but backordering is allowed',
        ),
        'v.2.6.0' => array(
            '[FEATURE] Introduce new option to show accessories as radios',
            '[FEATURE] Display style is set by accessory group',
            '[FEATURE] An ability to generate accessory images manually',
            '[FEATURE] Duplicate accessories when duplicating a product',
            '[IMPROVEMENT] Out-of-stock accessories are not allowed to check out',
            '[IMPROVEMENT] Show description when hovering on an accessory',
            '[IMPROVEMENT] Update product price on changing combinations even it does not have any accessory',
            '[IMPROVEMENT] Combination name is editable',
        ),
        'v.2.5.0' => array(
            'Generate product images manually',
            'Duplicate accessories when customer duplicate product',
            'Support edit name accessories',
        ),
        'v.2.4.0' => array(
            'FEATURE Work with combinations',
        ),
        'v.2.3.0' => array(
            'FEATURE Work with specific prices',
            'FEATURE Custom quantity for each accessory',
            'IMPROVEMENT Display accessory images in Fancybox',
            'FEATURE Custom quantity for each accessory',
        ),
        'v.2.2.0' => array(
            'FEATURE An ability to add each accessory to shopping cart',
            'IMPROVEMENT Display cost of main item and selected accessories together',
        ),
    );

    /**
     * Show result to view.
     *
     * @var type json
     */
    protected $ajax_json = array(
        'success' => false,
    );

    /**
     * All css files which should be loaded.
     *
     * @var array
     */
    protected $module_media_css = array(
        'welcome_page.css',
        'icon-menu-pos.css',
    );

    /**
     * Define link of addon page.
     *
     * @var string
     */
    protected $link_to_addon_page = 'http://addons.prestashop.com/en/ratings.php';

    /**
     * Define link of orestamonster page.
     *
     * @var string
     */
    protected $link_to_prestamonster = 'https://addons.prestashop.com/en/2_community-developer?contributor=613198';

    /**
     * construct.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('Administration');
        $this->toolbar_title[] = $this->module->tab_admin_welcome_page['AdminHsMultiAccessoriesWelcomePagePro'];
    }

    public function renderView()
    {
        Configuration::updateValue($this->module->getKeyWelcomePage(), 1);
        $this->context->smarty->assign(array(
            'module_name' => $this->module->name,
            'module_display_name' => $this->module->displayName,
            'module_version' => $this->module->version,
            'change_logs' => $this->change_logs,
            'link_to_addon_page' => $this->link_to_addon_page,
            'link_to_prestamonster' => $this->link_to_prestamonster,
            'is_prestashop_16' => $this->module->isPrestashop16(),
            'link_module_homepage' => $this->context->link->getAdminLink($this->module->class_controller_admin_group),
        ));
        return parent::renderView();
    }
    
    /**
     * Set Media file include when controller called.
     */
    public function setMedia($is_new_theme = false)
    {
        if ($this->module->isPrestashop17()) {
            parent::setMedia($is_new_theme);
        } else {
            parent::setMedia();
        }
        if (!empty($this->module_media_css) && is_array($this->module_media_css)) {
            $css_files = array();
            foreach ($this->module_media_css as $css_file) {
                $css_file = $this->module->getCssPath() . $css_file;
                if (version_compare(_PS_VERSION_, '1.6') === -1) {
                    $css_file = $css_file . '?' . $this->module->name . '=' . $this->module->version;
                }
                $css_files[] = $css_file;
            }
            $this->addCSS($css_files);
        }
    }

    /**
     * Initinalize all translations.
     */
    protected function initTranslations()
    {
        parent::initTranslations();
        $source = basename(__FILE__, '.php');
        $this->context->smarty->assign(array(
            'welcome_to' => $this->l('Welcome to', $source),
            'an_awesome_PrestaShop_solution_provided_by_prestamonster' => $this->l('An awesome PrestaShop solution provided by PrestaMonster.', $source),
            'change_log' => $this->l('Change log', $source),
            'read_more' => $this->l('Read more...', $source),
            'thank_you_and_take_me_to' => sprintf($this->l('Thank you, and take me to %s', $source), $this->module->displayName),
            'share_your_reviews' => $this->l('Share your reviews', $source),
            'and_as_a_result_you_will_get_more_values_from_us' => $this->l('And as a result, you will get more values from us.', $source),
            'add_your' => $this->l('Add your', $source),
            'on' => $this->l('on', $source),
            'addons_url' => $this->l('addons.prestashop.com', $source),
            'to_help_us_improve' => sprintf($this->l('to help us improve %s continuously.', $source), $this->module->displayName),
            'star' => $this->l('★★★★★', $source),
            'just_log_into' => $this->l('Just log into', $source),
            'prestashop_addons' => $this->l('Prestashop Addons', $source),
            'with_your_credentials' => $this->l('with your credentials, then visit this page and look for the right order number.', $source),
            'looking_for_even_better_prestashop_modules' => $this->l('Looking for even better PrestaShop modules?', $source),
            'take_a_look_at_all_modules_developed_by' => $this->l('Take a look at all modules developed by ', $source),
            'prestamonster' => $this->l('PrestaMonster', $source),
        ));
    }
}
