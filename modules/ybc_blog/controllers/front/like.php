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

if (!defined('_PS_VERSION_'))
	exit;
class Ybc_blogLikeModuleFrontController extends ModuleFrontController
{
    public function init()
	{
            $json = array();
            $id_post = (int)Tools::getValue('id_post');
            $module = new Ybc_blog();
            if(!$module->itemExists('post','id_post',$id_post))
            {
                $json['error'] = $this->module->l('This post does not exist');
                die(Tools::jsonEncode($json));
            }
            if(!(int)Configuration::get('YBC_BLOG_ALLOW_LIKE'))
            {
                $json['error'] = $this->module->l('You are not allowed to like the post');
                die(Tools::jsonEncode($json));
            }
            if(!(int)Configuration::get('YBC_BLOG_GUEST_LIKE') && !$this->context->customer->id)
            {
                $json['error'] = $this->module->l('You need to log in to like the post');
                die(Tools::jsonEncode($json));
            }   
            $ip = Tools::getRemoteAddr();
            $browser= $this->module->getDevice();
            if(!$this->module->isLikedPost($id_post))
            {
                if($this->context->cookie->liked_posts)
                {
                    $likedPosts = @unserialize($this->context->cookie->liked_posts);
                    $likedPosts[]=$id_post;
                    $this->context->cookie->liked_posts = @serialize($likedPosts);
                    $this->context->cookie->write();
                }
                else
                {
                    $likedPosts=array();
                    $likedPosts[]=$id_post;
                    $this->context->cookie->liked_posts = @serialize($likedPosts);
                    $this->context->cookie->write();
                }
                $sql='INSERT INTO `'._DB_PREFIX_.'ybc_blog_log_like`(ip,id_post,browser,id_customer,datetime_added) VALUES ("'.pSQL($ip).'","'.(int)$id_post.'","'.pSQL($browser).'","'.(int)Context::getContext()->customer->id.'","'.pSQL(date('Y-m-d H:i:s')).'")';
                Db::getInstance()->execute($sql);
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_post` SET likes= likes+1 WHERE id_post='.(int)$id_post);
                $json['likes'] = Db::getInstance()->getValue('SELECT likes FROM `'._DB_PREFIX_.'ybc_blog_post` WHERE id_post='.(int)$id_post);
                $json['success'] = $this->module->l('Successfully liked the post');
                $json['liked']=true;
                die(Tools::jsonEncode($json));
            }
            else
            {
                if($this->context->customer->logged)
                {
                    Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ybc_blog_log_like` WHERE id_customer="'.(int)$this->context->customer->id.'"');
                }
                if($this->context->cookie->liked_posts)
                {
                    $likedPosts = @unserialize($this->context->cookie->liked_posts);
                    foreach($likedPosts as $key=>$val)
                    {
                        if($val==$id_post)
                            unset($likedPosts[$key]);
                    }
                    $this->context->cookie->liked_posts = @serialize($likedPosts);
                    $this->context->cookie->write();
                }
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ybc_blog_post` SET likes= likes-1 WHERE id_post='.(int)$id_post);
                $json['likes'] = Db::getInstance()->getValue('SELECT likes FROM `'._DB_PREFIX_.'ybc_blog_post` WHERE id_post='.(int)$id_post);
                $json['success'] = $this->module->l('Successfully unliked the post');
                $json['liked']=false;
            }
            //$json['error'] = $this->module->l('You have liked this post');
            die(Tools::jsonEncode($json));
	}
}