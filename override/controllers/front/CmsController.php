<?php

class CmsController extends CmsControllerCore
{
	/*
    * module: prettyurls
    * date: 2020-01-08 13:47:00
    * version: 2.2.5
    */
    public function init()
	{
		$link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('cms_rewrite')));
		$cms_pattern = '/.*?content\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_pattern, $_SERVER['REQUEST_URI'], $url_array);
		if (isset($url_array[2]) && $url_array[2] != '') {
			$link_rewrite = $url_array[2];
		}
		$cms_category_rewrite 	= Tools::safeOutput(urldecode(Tools::getValue('cms_category_rewrite')));
		$cms_cat_pattern = '/.*?content\/category\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)/';
		preg_match($cms_cat_pattern, $_SERVER['REQUEST_URI'], $url_cat_array);
		if (isset($url_cat_array[2]) && $url_cat_array[2] != '') {
			$cms_category_rewrite = $url_cat_array[2];
		}
		$id_lang = $this->context->language->id;
		$id_shop = $this->context->shop->id;
		if ($link_rewrite) {
			$sql = 'SELECT tl.id_cms
					FROM '._DB_PREFIX_.'cms_lang tl
					LEFT OUTER JOIN '._DB_PREFIX_.'cms_shop t ON (t.id_cms = tl.id_cms)
					WHERE tl.link_rewrite = \''.pSQL($link_rewrite).'\' AND tl.id_lang = '.(int)$id_lang.' AND t.id_shop = '.(int)$id_shop;
			$id_cms = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_cms != '') {
				$_POST['id_cms'] = $id_cms;
				$_GET['cms_rewrite'] = '';
			}
		} elseif ($cms_category_rewrite) {
			$sql = 'SELECT id_cms_category
					FROM '._DB_PREFIX_.'cms_category_lang
					WHERE link_rewrite = \''.pSQL($cms_category_rewrite).'\' AND id_lang = '.(int)$id_lang;
			$id_cms_category = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
			if ($id_cms_category != '') {
				$_GET['cms_category_rewrite'] = '';
				$_POST['id_cms_category'] = $id_cms_category;
			}
		}
		$allow_accented_chars = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
		if ($allow_accented_chars > 0) {
			$id_cms = (int)Tools::getValue('id_cms');
			if ($id_cms <= 0) {
				$id = (int)$this->crawlDbForId($_GET['cms_rewrite']);
				if ($id > 0) {
					$_POST['id_cms'] = $id;
				}
			}
		}
		parent::init();
	}
	
	/*
    * module: prettyurls
    * date: 2020-01-08 13:47:00
    * version: 2.2.5
    */
    protected function crawlDbForId($rew)
	{
		$id_lang = $this->context->language->id;
		$id_shop = $this->context->shop->id;
		$sql = new DbQuery();
        $sql->select('`id_cms`');
        $sql->from('cms_lang');
		$sql->where('`id_lang` = '.(int)$id_lang.' AND `id_shop` = '.(int)$id_shop.' AND `link_rewrite` = "'.pSQL($rew).'"');
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}
    /*
    * module: powerfulformgenerator
    * date: 2020-03-26 18:18:18
    * version: 2.7.8
    */
    public function initContent()
    {
        parent::initContent();
        $parent_cat = new CMSCategory(1, $this->context->language->id);
        $this->context->smarty->assign('id_current_lang', $this->context->language->id);
        $this->context->smarty->assign('home_title', $parent_cat->name);
        $this->context->smarty->assign('cgv_id', Configuration::get('PS_CONDITIONS_CMS_ID'));
        if ($this->assignCase == 1) {
            if ($this->cms->indexation == 0) {
                $this->context->smarty->assign('nobots', true);
            }
            if (version_compare(Tools::substr(_PS_VERSION_, 0, 3), '1.7', '>=')) {
                $currentCms = $this->objectPresenter->present($this->cms);
                $currentCms['content'] = $this->returnContent($currentCms['content']);
                $this->context->smarty->assign(array(
                    'cms' => $currentCms,
                ));
                $this->setTemplate(
                    'cms/page',
                    array('entity' => 'cms', 'id' => $this->cms->id)
                );
            } else {
                if (isset($this->cms->id_cms_category) && $this->cms->id_cms_category) {
                    $path = Tools::getFullPath($this->cms->id_cms_category, $this->cms->meta_title, 'CMS');
                } elseif (isset($this->cms_category->meta_title)) {
                    $path = Tools::getFullPath(1, $this->cms_category->meta_title, 'CMS');
                }
                $this->cms->content = $this->returnContent($this->cms->content);
                $this->context->smarty->assign(array(
                    'cms' => $this->cms,
                    'content_only' => (int)Tools::getValue('content_only'),
                    'path' => $path,
                    'body_classes' => array($this->php_self.'-'.$this->cms->id, $this->php_self.'-'.$this->cms->link_rewrite)
                ));
                $this->setTemplate(_PS_THEME_DIR_.'cms.tpl');
            }
        } elseif ($this->assignCase == 2) {
            if (version_compare(Tools::substr(_PS_VERSION_, 0, 3), '1.7', '>=')) {
                $this->context->smarty->assign($this->getTemplateVarCategoryCms());
                $this->setTemplate('cms/category');
            } else {
                $this->context->smarty->assign(array(
                    'category' => $this->cms_category, //for backward compatibility
                    'cms_category' => $this->cms_category,
                    'sub_category' => $this->cms_category->getSubCategories($this->context->language->id),
                    'cms_pages' => CMS::getCMSPages($this->context->language->id, (int)$this->cms_category->id, true, (int)$this->context->shop->id),
                    'path' => ($this->cms_category->id !== 1) ? Tools::getPath($this->cms_category->id, $this->cms_category->name, false, 'CMS') : '',
                    'body_classes' => array($this->php_self.'-'.$this->cms_category->id, $this->php_self.'-'.$this->cms_category->link_rewrite)
                ));
                $this->setTemplate(_PS_THEME_DIR_.'cms.tpl');
            }
        }
    }
    /*
    * module: powerfulformgenerator
    * date: 2020-03-26 18:18:18
    * version: 2.7.8
    */
    private function generatePFG($id_pfg)
    {
        $renderer = new PFGRenderer($id_pfg);
        if (!$renderer->isAllowed(true)) {
            $redirect_url = $renderer->getForm()->unauth_redirect_url[Context::getContext()->language->id];
            if (!empty($redirect_url)) {
                Tools::redirect($redirect_url);
            } else {
                Controller::getController('PageNotFoundController')->run();
            }
            exit();
        }
        return $renderer->displayForm();
    }
    /*
    * module: powerfulformgenerator
    * date: 2020-03-26 18:18:18
    * version: 2.7.8
    */
    public function returnContent($contents)
    {
        preg_match_all('/\{powerfulform\:[(0-9\,)]+\}/i', $contents, $matches);
        foreach ($matches[0] as $match) {
            $explode = explode(":", $match);
            $contents = str_replace($match, $this->generatePFG(str_replace("}", "", $explode[1])), $contents);
        }
        return $contents;
    }
}