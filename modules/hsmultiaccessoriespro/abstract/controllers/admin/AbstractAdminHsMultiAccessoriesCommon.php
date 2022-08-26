<?php
/**
 * Point of sale for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Controller of admin page - Multi Accessories (Abstract).
 */

class AbstractAdminHsMultiAccessoriesCommon extends ModuleAdminController
{
    /**
     * @see AdminControllerCore::init()
     */
    public function init()
    {
        parent::init();
        $this->initTranslations();
    }

    /**
     * Initinalize all translations.
     */
    protected function initTranslations()
    {
    }

    /**
     * Overriding translating function, so that we point directly to getModuleTranslation().
     *
     * @see parent::l()
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getModuleTranslation($this->module, $string, $class);
    }
}
