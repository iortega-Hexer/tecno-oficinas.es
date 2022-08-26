<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2022 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
 
require_once(_PS_MODULE_DIR_.'ybc_blog/classes/ybc_blog_paggination_class.php'); 
if (!defined('_PS_VERSION_'))
    	exit;
class AdminYbcBlogStatisticsController extends ModuleAdminController
{
    public function __construct()
    {
       parent::__construct();
       $this->bootstrap = true;
       if(Tools::isSubmit('clearviewLogSubmit'))
       {
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ybc_blog_log_view` WHERE id_post IN (SELECT id_post FROM `'._DB_PREFIX_.'ybc_blog_post_shop` WHERE id_shop='.(int)$this->context->shop->id.')');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminYbcBlogStatistics').'&tab_ets=view-log&conf=1');
       }
       if(Tools::isSubmit('clearlikeLogSubmit'))
       {
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ybc_blog_log_like` WHERE id_post IN (SELECT id_post FROM `'._DB_PREFIX_.'ybc_blog_post_shop` WHERE id_shop='.(int)$this->context->shop->id.')');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminYbcBlogStatistics').'&tab_ets=like-log&conf=1');
       }
    }
    public function initContent()
    {
        parent::initContent();
    }
    public function renderList()
    {
        if(!$this->module->checkProfileEmployee($this->context->employee->id,'Statistics'))
            return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->module->name.'/views/templates/hook/error_access.tpl');
        $id_post = (int)Tools::getValue('id_post');
        $months=Tools::dateMonths();
        $now_year = date('Y')+2;
        $start_year = Db::getInstance()->getValue('SELECT MIN(YEAR(datetime_added)) FROM `'._DB_PREFIX_.'ybc_blog_post` WHERE 1 '.((int)$id_post ? ' AND id_post='.(int)$id_post :''));
        $years = array();
        if($start_year)
        {
            for($i=$start_year-2;$i<=$now_year;$i++)
            {
                $years[]=$i;
            }
        }
        $views=array();
        $likes =array();
        $comments=array();
        $year = (int)Tools::getValue('years',date('Y'));
        $month = (int)Tools::getValue('months',date('m'));
        if(!$year)
        {
            if($years)
            {
                foreach($years as $year)
                {
                    $likes[] =array(
                        0 => $year,
                        1 => $this->getCountLike($year,'','',$id_post),
                    );
                    $views[] =array(
                        0 => $year,
                        1 => $this->getCountView($year,'','',$id_post),
                    );
                    $comments[] =array(
                        0 => $year,
                        1 => $this->getCountComment($year,'','',$id_post),
                    );
                }
            }
        }
        elseif($year)
        {
            if(!$month){
                if($months)
                {
                    foreach($months as $key=> $month)
                    {
                        $likes[] =array(
                            0 => $key,
                            1 => $this->getCountLike($year,$key,'',$id_post),
                        );
                        $views[] =array(
                            0 => $key,
                            1 => $this->getCountView($year,$key,'',$id_post),
                        );
                        $comments[] =array(
                            0 => $key,
                            1 => $this->getCountComment($year,$key,'',$id_post),
                        );
                    }
                }
            }
            elseif($month)
            {
                $days = function_exists('cal_days_in_month') ? cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year) : (int)date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
                if($days)
                {
                    for($day=1; $day<=$days;$day++)
                    {
                        $likes[] =array(
                            0 => $day,
                            1 => $this->getCountLike($year,$month,$day,$id_post),
                        );
                        $views[] =array(
                            0 => $day,
                            1 => $this->getCountView($year,$month,$day,$id_post),
                        );
                        $comments[] =array(
                            0 => $day,
                            1 => $this->getCountComment($year,$month,$day,$id_post),
                        );
                    }
                }
            }
        }
        $lineChart =array( 
            array(
                'key'=> $this->module->l('Views'),
                'values'=>$views,
                'disables'=>1,
            ) 
        );
        if(Configuration::get('YBC_BLOG_ALLOW_LIKE'))
        {
           $lineChart[]= array(
                'key'=> $this->module->l('Likes'),
                'values'=>$likes,
                'disables'=>1,
            );
        }
        if(Configuration::get('YBC_BLOG_ALLOW_COMMENT'))
        {
            $lineChart[]=array(
                'key'=> $this->module->l('Comments'),
                'values'=>$comments,
                'disables'=>1,
            );
        }
        $posts= $this->module->getPostsWithFilter(' AND p.enabled=1');
        $sql = "SELECT COUNT(*) FROM `"._DB_PREFIX_."ybc_blog_log_view` lv 
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post` p ON (p.id_post=lv.id_post)
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post_shop` ps ON (p.id_post=ps.id_post AND ps.id_shop='".(int)$this->context->shop->id."')
        LEFT JOIN `"._DB_PREFIX_."ybc_blog_post_lang` pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')
        LEFT JOIN `"._DB_PREFIX_."customer` m ON (lv.id_customer=m.id_customer)";
        $total= count(Db::getInstance()->executeS($sql));
        $limit=20;
        $page = (int)Tools::getValue('page',1);
        if($page<=0)
            $page=1;
        $start= ($page-1)*$limit;
        $pagination_view = new Ybc_blog_paggination_class();
        $pagination_view->url = $this->context->link->getAdminLink('AdminYbcBlogStatistics').'&tab_ets=view-log&page=_page_';
        $pagination_view->limit=$limit;
        $pagination_view->page= $page;
        $pagination_view->total=$total;
        $sql = "SELECT lv.*,p.id_post,pl.url_alias,pl.description,pl.short_description,pl.title,m.lastname,m.firstname FROM `"._DB_PREFIX_."ybc_blog_log_view` lv 
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post` p ON (p.id_post=lv.id_post)
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post_shop` ps ON (p.id_post=ps.id_post AND ps.id_shop='".(int)$this->context->shop->id."')
        LEFT JOIN `"._DB_PREFIX_."ybc_blog_post_lang` pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')
        LEFT JOIN `"._DB_PREFIX_."customer` m ON (lv.id_customer=m.id_customer)
        ORDER BY lv.datetime_added DESC LIMIT ".(int)$start.", ".(int)$limit;
        $viewlogs=Db::getInstance()->executeS($sql);
        if($viewlogs)
        {
            foreach($viewlogs as &$log)
            {
                $browser = explode(' ',$log['browser']);
                if(isset($browser[0]))
                    $log['class'] = Tools::strtolower($browser[0]);
                else
                    $log['class']='default';
                $log['title'] = '<a title="'.$log['title'].'" target="_blank" href="'.$this->module->getLink('blog',array('id_post'=>$log['id_post'])).'">'.$log['title'].'</a>';
            }   
        }
        $sql = "SELECT * FROM `"._DB_PREFIX_."ybc_blog_log_like` lv 
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post` p ON (p.id_post=lv.id_post)
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post_shop` ps ON (p.id_post=ps.id_post AND ps.id_shop='".(int)$this->context->shop->id."')
        LEFT JOIN `"._DB_PREFIX_."ybc_blog_post_lang` pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')";
        $total= count(Db::getInstance()->executeS($sql));
        $limit=20;
        $page = (int)Tools::getValue('page',1);
        if($page<=0)
            $page=1;
        $start= ($page-1)*$limit;
        $pagination_like = new Ybc_blog_paggination_class();
        $pagination_like->url = $this->context->link->getAdminLink('AdminYbcBlogStatistics').'&tab_ets=like-log&page=_page_';
        $pagination_like->limit=$limit;
        $pagination_like->page= $page;
        $pagination_like->total=$total;
        $sql = "SELECT lv.*,p.id_post,pl.url_alias,pl.description,pl.short_description,pl.title,m.lastname,m.firstname FROM `"._DB_PREFIX_."ybc_blog_log_like` lv 
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post` p ON (p.id_post=lv.id_post)
        INNER JOIN `"._DB_PREFIX_."ybc_blog_post_shop` ps ON (p.id_post=ps.id_post AND ps.id_shop='".(int)$this->context->shop->id."')
        LEFT JOIN `"._DB_PREFIX_."ybc_blog_post_lang` pl ON (p.id_post=pl.id_post AND pl.id_lang='".(int)$this->context->language->id."')
        LEFT JOIN `"._DB_PREFIX_."customer` m ON (lv.id_customer=m.id_customer)
        ORDER BY lv.datetime_added DESC LIMIT ".(int)$start.", ".(int)$limit;
        $likelogs=Db::getInstance()->executeS($sql);
        if($likelogs)
        {
            foreach($likelogs as &$log)
            {
                $browser = explode(' ',$log['browser']);
                if(isset($browser[0]))
                    $log['class'] = Tools::strtolower($browser[0]);
                else
                    $log['class']='default';
                $log['title'] = '<a title="'.$log['title'].'" target="_blank" href="'.$this->module->getLink('blog',array('id_post'=>$log['id_post'])).'">'.$log['title'].'</a>';
            }   
        }
        if(($id_post = (int)Tools::getValue('id_post')))
        {
            $post= new Ybc_blog_post_class($id_post,$this->context->language->id);
        }
        $tab_ets = Tools::getValue('tab_ets','chart');
        if(!Validate::isCleanHtml($tab_ets))
            $tab_ets = 'chart';
        $this->context->smarty->assign(
            array(
                'months' => $months,
                'ctf_month' => $month ? : date('m'),
                'action'=> $this->context->link->getAdminLink('AdminYbcBlogStatistics'),
                'years'=>$years,
                'ctf_year' => $year ? : date('Y'),
                'lineChart' => $lineChart,
                'ctf_post' => (int)$id_post,
                'ctf_post_title' => (int)$id_post ? $post->title : '',
                'js_dir_path' => $this->module->blogDir.'views/js/',
                'likelogs'=>$likelogs,
                'viewlogs'=>$viewlogs,
                'posts' => $posts,
                'tab_ets' => $tab_ets,
                'control'=> 'statistics',
                'ybc_blog_ajax_post_url' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name.'&ajaxpostsearch=true',
                'YBC_BLOG_ALLOW_LIKE' => Configuration::get('YBC_BLOG_ALLOW_LIKE'),
                'pagination_text_view' => $pagination_view->render(),
                'pagination_text_like' => $pagination_like->render(),
                'ybc_blog_sidebar' => $this->module->renderSidebar(),
                'show_reset' => Tools::isSubmit('submitFilterChart'),
            )
        );
        return  $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'statistics.tpl');
    }
    public function getCountView($year='',$month='',$day='',$id_post=0)
    {
        return Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'ybc_blog_log_view` l, `'._DB_PREFIX_.'ybc_blog_post_shop` ps WHERE l.id_post=ps.id_post AND ps.id_shop='.(int)$this->context->shop->id.($id_post ? ' AND ps.id_post='.(int)$id_post : '').($year ? ' AND YEAR(l.datetime_added) ="'.pSQL($year).'"':'').($month ? ' AND MONTH(l.datetime_added) ="'.pSQL($month).'"':'').($day ? ' AND DAY(l.datetime_added) ="'.pSQL($day).'"':''));
    }
    public function getCountLike($year='',$month='',$day='',$id_post=0)
    {
        return Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'ybc_blog_log_like` l, `'._DB_PREFIX_.'ybc_blog_post_shop` ps WHERE l.id_post=ps.id_post AND ps.id_shop='.(int)$this->context->shop->id.($id_post ? ' AND ps.id_post='.(int)$id_post : '').($year ? ' AND YEAR(l.datetime_added) ="'.pSQL($year).'"':'').($month ? ' AND MONTH(l.datetime_added) ="'.pSQL($month).'"':'').($day ? ' AND DAY(l.datetime_added) ="'.pSQL($day).'"':''));
    }
    public function getCountComment($year='',$month='',$day='',$id_post=0)
    {
        return Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'ybc_blog_comment` c, `'._DB_PREFIX_.'ybc_blog_post_shop` ps WHERE c.id_post=ps.id_post AND ps.id_shop='.(int)$this->context->shop->id.($id_post ? ' AND ps.id_post='.(int)$id_post : '').($year ? ' AND YEAR(c.datetime_added) ="'.pSQL($year).'"':'').($month ? ' AND MONTH(c.datetime_added) ="'.pSQL($month).'"':'').($day ? ' AND DAY(c.datetime_added) ="'.pSQL($day).'"':''));
    }
}