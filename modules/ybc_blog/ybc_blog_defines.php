<?php
/**
 * 2007-2019 ETS-Soft
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
 *  @copyright  2007-2019 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
	exit;
class Ybc_blog_defines extends Module
{
    public static $categoryFields = array(
        array(
            'name' => 'id_category',
            'primary_key' => true
        ),
        array(
            'name'=>'id_parent',
        ),
        array(
            'name' => 'title',
            'multi_lang' => true
        ),
        array(
            'name' => 'meta_title',
            'multi_lang' => true
        ),
        array(
            'name' => 'description',            
            'multi_lang' => true
        ),
        array(
            'name' => 'url_alias',
            'multi_lang'=>true,
        ),
        array(
            'name' => 'meta_keywords',
            'multi_lang' => true
        ),
        array(
            'name' => 'meta_description',
            'multi_lang' => true
        ), 
        array(
            'name' => 'image'
        ),
        array(
            'name' => 'thumb'
        ),
        array(
            'name' => 'enabled',
            'default' => 1
        ),
        array(
            'name' => 'sort_order',
            'default' => 1
        )
    );
    public static $postFields = array(
        array(
            'name' => 'id_post',
            'primary_key' => true
        ),        
        array(
            'name' => 'title',
            'multi_lang' => true
        ),
        array(
            'name' => 'meta_title',
            'multi_lang' => true
        ),
        array(
            'name' => 'meta_description',
            'multi_lang' => true
        ),
        array(
            'name' => 'meta_keywords',
            'multi_lang' => true
        ),
        array(
            'name' => 'products'
        ),
        array(
            'name' => 'description',            
            'multi_lang' => true
        ),
        array(
            'name' => 'short_description',            
            'multi_lang' => true
        ),
        array(
            'name' => 'url_alias',
            'multi_lang'=>true,
        ), 
        array(
            'name' => 'image'
        ),
        array(
            'name' => 'thumb'
        ),
        array(
            'name' => 'enabled',
            'default' => 1
        ),
        array(
            'name' => 'datetime_active',
        ),
        array(
            'name' => 'is_featured',
            'default' => 1
        ),
        array(
            'name' => 'sort_order',
            'default' => 1
        ),
        array(
            'name' => 'click_number',
            'default' => 0
        ),
        array(
            'name' => 'likes',
            'default' => 0
        )
    );
    public static $commentFields = array(
        array(
            'name' => 'id_comment',
            'primary_key' => true
        ),        
        array(
            'name' => 'subject'
        ),
        array(
            'name' => 'comment'
        ),
        array(
            'name' => 'reply'
        ),
        array(
            'name' => 'rating'
        ),
        array(
            'name' => 'approved'
        ),
        array(
            'name' => 'reported'
        )
    );
    public static $galleryFields = array(
        array(
            'name' => 'id_gallery',
            'primary_key' => true
        ),        
        array(
            'name' => 'title',
            'multi_lang' => true
        ),
        array(
            'name' => 'description',
            'multi_lang' => true
        ),
        array(
            'name' => 'sort_order',
            'default' => 1
        ),
        array(
            'name' => 'image'
        ),
        array(
            'name' => 'thumb'
        ),
        array(
            'name' => 'enabled',
            'default' => 1
        ),
        array(
            'name' => 'is_featured',
            'default' => 1
        )
    );
    public static  $slideFields = array(
        array(
            'name' => 'id_slide',
            'primary_key' => true
        ),        
        array(
            'name' => 'caption',
            'multi_lang' => true
        ),        
        array(
            'name' => 'sort_order',
            'default' => 1
        ),
        array(
            'name' => 'image'
        ),
        array(
            'name' => 'url',
            'multi_lang'=>true
        ),
        array(
            'name' => 'enabled',
            'default' => 1
        )
    );
    public $subTabs=array();
    public $configs = array(); 
    public $configs_seo=array();
    public $socials=array();
    public $configs_email=array();
    public $configs_homepage=array();
    public $configs_sidebar =array();
    public $configs_sitemap=array();
    public $configs_image = array();
    public $rss =array();
    public $customer_settings=array();
    public $alias;
    public $friendly;
    public function __construct()
	{
        $this->name= 'ybc_blog';
	    $this->context = Context::getContext();
        $this->alias = Configuration::get('YBC_BLOG_ALIAS',$this->context->language->id);
        $this->friendly = (int)Configuration::get('YBC_BLOG_FRIENDLY_URL') && (int)Configuration::get('PS_REWRITING_SETTINGS') ? true : false; 
        $this->customer_settings=array(
            'YBC_BLOG_ALLOW_CUSTOMER_AUTHOR' => array(
                'label' => $this->l('Allow customer (community author) to submit post','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,
                'desc' => $this->l('A blog management area named "My blog posts" will be available in “My account” on the front office. When author log into their account','ybc_blog_defines'),
                'values' => array(
                    array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes','ybc_blog_defines')
                    ),
                    array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No','ybc_blog_defines')
                    )
                ),
            ),
            'YBC_BLOG_GROUP_CUSTOMER_AUTHOR'=>array(
                'label' => $this->l('Customer groups','ybc_blog_defines'),
                'type' => 'checkbox', 
                'values' => array(
                    'query' => $this->getGroups(), 
                    'id' => 'value',
                    'name' => 'label' 
                ), 
                'desc'=> $this->l('Select customers group who can submit posts','ybc_blog_defines'),
                'form_group_class' =>'setting_customer_author',
                'default' => Configuration::get('PS_CUSTOMER_GROUP'),
            ),
            'YBC_BLOG_AUTHOR_PRIVILEGES'=>array(
                'label' => $this->l('Customer (community author) privileges','ybc_blog_defines'),
                'type' => 'checkbox', 
                'default' =>'upload_avatar_information,add_new,edit_blog,delete_blog,manage_comments,reply_comments', 
            'values' => array(
                'query' => array(
                        array(
                            'value'=>'add_new',
                            'label'=>$this->l('Add new blog posts','ybc_blog_defines'),
                        ),
                        array(
                            'value'=>'edit_blog',
                            'label'=>$this->l('Edit his/her blog posts','ybc_blog_defines'),
                        ),
                        array(
                            'value'=>'delete_blog',
                            'label'=>$this->l('Delete his/her blog posts','ybc_blog_defines'),
                        ),
                        array(
                            'value'=>'manage_comments',
                            'label'=>$this->l('Manage comments from customers on his/her blog posts (edit/delete)','ybc_blog_defines'),
                        ),
                        array(
                            'value'=>'reply_comments',
                            'label'=>$this->l('Reply customer comments on his/her blog posts','ybc_blog_defines'),
                        ),
                    ), 
                    'id' => 'value',
                    'name' => 'label' 
                ), 
                'form_group_class' =>'setting_customer_author'
            ),
            'YBC_BLOG_STATUS_POST'=>array(
                'label'=>$this->l('Set blog post status when customer (community author) submit a new post','ybc_blog_defines'),
                'type'=>'radio',
                'values' => array(
                    array(
                        'value'=>'active',
                        'id'=>'active',
                        'label'=>$this->l('Active immediately','ybc_blog_defines'),
                    ),
                    array(
                        'value'=>'waiting_approval',
                        'id'=>'waiting_approval',
                        'label'=>$this->l('Waiting approval from Administrator','ybc_blog_defines'),
                    ), 
                ), 
                'default'=>'waiting_approval',
                'form_group_class' =>'setting_customer_author'
            ),
            'YBC_BLOG_CATEGOGY_CUSTOMER' =>array(
                'type' => 'blog_categories',
                'label' => $this->l('Blog post categories','ybc_blog_defines'),
                'required' => true,
                'html_content' =>true,
                'categories' =>true, 
                'default'=>'1',
                'selected_categories' => Configuration::get('YBC_BLOG_CATEGOGY_CUSTOMER') ? explode(',',Configuration::get('YBC_BLOG_CATEGOGY_CUSTOMER')):array(),
                'form_group_class' =>'setting_customer_author' ,
                'desc' => $this->l('Select blog post categories which customer (community author) can submit posts to','ybc_blog_defines'), 
            ),
            //
            );
        $this->subTabs = array(
            array(
                'class_name' => 'AdminYbcBlogPost',
                'tab_name' => $this->l('Posts','ybc_blog_defines'),
                'icon'=>'icon icon-AdminPriceRule',
            ),
            array(
                'class_name' => 'AdminYbcBlogCategory',
                'tab_name' => $this->l('Categories','ybc_blog_defines'),
                'icon' => 'icon icon-AdminCatalog',
            ),
            array(
                'class_name' => 'AdminYbcBlogComment',
                'tab_name' => $this->l('Comments','ybc_blog_defines'),
                'icon' => 'icon icon-comments',
            ),
            array(
                'class_name' => 'AdminYbcBlogPolls',
                'tab_name' => $this->l('Polls','ybc_blog_defines'),
                'icon' => 'icon icon-polls',
            ),
            array(
                'class_name' => 'AdminYbcBlogSlider',
                'tab_name' => $this->l('Slider','ybc_blog_defines'),
                'icon' => 'icon icon-AdminParentModules',
            ),
            array(
                'class_name' => 'AdminYbcBlogGallery',
                'tab_name' => $this->l('Photo gallery','ybc_blog_defines'),
                'icon' =>'icon icon-AdminDashboard',
            ),
            array(
                'class_name' => 'AdminYbcBlogAuthor',
                'tab_name' => $this->l('Authors','ybc_blog_defines'),
                'icon' => 'icon icon-user',
            ),
            array(
                'class_name' => 'AdminYbcBlogStatistics',
                'tab_name' => $this->l('Statistics','ybc_blog_defines'),
                'icon' =>'icon icon-chart',
            ),
            array(
                'class_name' => 'AdminYbcBlogSetting',
                'tab_name' => $this->l('Global settings','ybc_blog_defines'),
                'icon' => 'icon icon-AdminAdmin',
            ),
        );
        $this->configs_sitemap=array(
            'YBC_BLOG_ENABLE_SITEMAP' => array(
                'label' => $this->l('Enable sitemap','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOC_SITEMAP_PAGES'=>array(
                'label' => $this->l('Pages to be included in sitemap','ybc_blog_defines'),
                'type' => 'checkbox',                                   
				'values' => array(
                     'query' => array(
                        array(
                            'id'=>'main_blog',
                            'label'=> $this->l('Main blog page','ybc_blog_defines'),
                            'value'=>'main_blog',
                        ),
                        array(
                            'id'=>'single_post',
                            'label'=> $this->l('Single post','ybc_blog_defines'),
                            'value'=>'single_post',
                        ),
                        array(
                            'id'=>'latest_post',
                            'label'=> $this->l('Latest posts','ybc_blog_defines'),
                            'value'=>'latest_post',
                        ),
                        array(
                            'id'=>'pupular_post',
                            'label'=> $this->l('Popular posts','ybc_blog_defines'),
                            'value'=>'pupular_post',
                        ),
                        array(
                            'id'=>'featured_post',
                            'label'=> $this->l('Featured posts','ybc_blog_defines'),
                            'value'=>'featured_post',
                        ),
                        array(
                            'id'=>'category',
                            'label'=> $this->l('Blog post categories','ybc_blog_defines'),
                            'value'=>'category',
                        ),
                        array(
                            'id'=>'authors',
                            'label'=> $this->l('Authors','ybc_blog_defines'),
                            'value'=>'authors',
                        )
                     ), 
                     'id' => 'id',
		             'name' => 'label'                                                               
                ),  
                'default' => 'main_blog,single_post,latest_post,pupular_post,featured_post,category,authors', 
                'form_group_class' =>'sitemap_setting',
            ),
        );
        $this->configs_image=array(
            'YBC_BLOG_IMAGE_BLOG_THUMB' => array(
                'label' => $this->l('Blog post thumbnail image (260 x 180 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(260,180),
            ), 
            'YBC_BLOG_IMAGE_BLOG' => array(
                'label' => $this->l('Blog post main image (1920 x 750 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(1920,750),
            ),
            'YBC_BLOG_IMAGE_CATEGORY_THUMB' => array(
                'label' => $this->l('Category thumbnail image (300 x 170 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(300,170),
            ),
            'YBC_BLOG_IMAGE_CATEGORY' => array(
                'label' => $this->l('Category image (1920 x 750 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(1920,750),
            ),  
            'YBC_BLOG_IMAGE_SLIDER' => array(
                'label' => $this->l('Slider image (800 x 470 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(800,470),
            ),
            'YBC_BLOG_IMAGE_GALLERY_THUHMB' => array(
                'label' => $this->l('Gallery thumbnail image (180 x 180 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(180,180),
            ),
            'YBC_BLOG_IMAGE_GALLERY' => array(
                'label' => $this->l('Gallery main image (600 X 600 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(600,600),
            ),
            'YBC_BLOG_IMAGE_AVATA' => array(
                'label' => $this->l('Author avatar image (100 x 100 is recommended)','ybc_blog_defines'),
                'type' => 'image',
                'default' => array(100,100),
            ),
            'YBC_BLOG_ENABLE_CUSTOMER_UPLOAD_AVATA' => array(
                'label' => $this->l('Allow customer to upload avatar','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_IMAGE_AVATA_DEFAULT' => array(
                'label' => $this->l('Default avatar','ybc_blog_defines'),
                'type' => 'file',
            ), 
        );
        $this->rss=array(
            'YBC_BLOG_ENABLE_RSS' => array(
                'label' => $this->l('Enable RSS feed','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOC_RSS_DISPLAY'=>array(
                'label' => $this->l('Display RSS icon on','ybc_blog_defines'),
                'type' => 'checkbox',                                   
				'values' => array(
                     'query' => array(
                        array(
                            'id'=>'side_bar',
                            'label'=> $this->l('Sidebar','ybc_blog_defines'),
                            'value'=>'side_bar',
                        ),
                        array(
                            'id'=>'custom_hook',
                            'label'=> $this->l('Custom hook','ybc_blog_defines'),
                            'value'=>'custom_hook',
                        )
                     ), 
                     'id' => 'id',
		             'name' => 'label'                                                               
                ),  
                'default' => 'side_bar,custom_hook', 
                'form_group_class' =>'rss_setting',
                'desc' => $this->l('Insert this line <b>{hook h="blogRssSidebar"}</b> into template .tpl file where you want to display RSS icon','ybc_blog_defines'),
            ),
            'YBC_BLOC_RSS_TYPE'=>array(
                'label' => $this->l('Data to feed','ybc_blog_defines'),
                'type' => 'checkbox',                                   
				'values' => array(
                     'query' => array(
                        array(
                            'id'=>'latest_posts',
                            'label'=> $this->l('Latest posts','ybc_blog_defines'),
                            'value'=>'latest_posts',
                        ),
                        array(
                            'id'=>'popular_posts',
                            'label'=> $this->l('Popular posts','ybc_blog_defines'),
                            'value'=>'popular_posts',
                        ),
                        array(
                            'id'=>'featured_posts',
                            'label'=> $this->l('Featured posts','ybc_blog_defines'),
                            'value'=>'featured_posts',
                        ),
                        array(
                            'id'=>'category',
                            'label'=> $this->l('Blog post categories','ybc_blog_defines'),
                            'value'=>'category',
                        ),
                        array(
                            'id'=>'authors',
                            'label'=> $this->l('Authors','ybc_blog_defines'),
                            'value'=>'authors',                        )
                     ), 
                     'id' => 'id',
		             'name' => 'label'                                                               
                ),  
                'default' => 'authors,category,featured_posts,popular_posts,latest_posts', 
                'form_group_class' =>'rss_setting',
            )
            
        );
        $this->configs = array(
            'YBC_BLOG_LAYOUT' => array(
                //GENERAL
                'label' => $this->l('Blog layout','ybc_blog_defines'),
                'type' => 'select',                                     
				'options' => array(
        			 'query' => array( 
                            array(
                                'id_option' => 'large_grid', 
                                'name' => $this->l('Large box and grid','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'large_list', 
                                'name' => $this->l('Large box and list','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'grid', 
                                'name' => $this->l('Grid','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'list', 
                                'name' => $this->l('List','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'list',
                'desc' => $this->l('Layout type for post listing pages such as main blog page, blog category pages, author pages, etc.','ybc_blog_defines'),
            ),            
            'YBC_BLOG_CUSTOM_COLOR' => array(
                'label' => $this->l('Main color','ybc_blog_defines'),
                'type' => 'color',
                'default' => '#2fb5d2',
                'desc' => $this->l('Used for buttons, link, highlight text, etc.','ybc_blog_defines'),
            ),
            'YBC_BLOG_CUSTOM_COLOR_HOVER' => array(
                'label' => $this->l('Hover color','ybc_blog_defines'),
                'type' => 'color',
                'default' => '#00cefd',
                'desc' => $this->l('Used for buttons, link, highlight text, etc.','ybc_blog_defines'),
            ),
            'YBC_BLOG_TEXT_READMORE' => array(
                'label' => $this->l('\'\'Read more\'\' text','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'Read more',
                'lang'=>true,
                'desc' => $this->l('Leave blank to hide the “Read more” link for blog posts','ybc_blog_defines'),
            ), 
            'YBC_BLOG_MAIN_PAGE_POST_TYPE' => array(
                'label' => $this->l('Posts will be displayed on main blog page','ybc_blog_defines'),
                'type' => 'select',                                     
				'options' => array(
        			 'query' => array( 
                            array(
                                'id_option' => 'latest', 
                                'name' => $this->l('Latest posts','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'featured', 
                                'name' => $this->l('Featured posts','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'latest',
                'desc' => $this->l('You can display neither latest blog posts nor featured blog posts on main blog page.','ybc_blog_defines')
            ),
            'YBC_BLOG_DATE_FORMAT' => array(
                'label' => $this->l('Date format','ybc_blog_defines'),
                'type' => 'text',
                'default' => $this->context->language->date_format_lite,
                //'width' => 200,
                'desc' => $this->l('Default: "F jS Y". For more reference, please check http://php.net/manual/en/function.date.php','ybc_blog_defines')             
            ), 
            'YBC_BLOG_DISPLAY_TYPE' => array(
                'label' => $this->l('Slider type','ybc_blog_defines'),
                'type' => 'select',  
                'tab' => 'slider',                                   
				'options' => array(
        			 'query' => array( 
                            array(
                                'id_option' => 'carousel', 
                                'name' => $this->l('Slick slider','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'nivo', 
                                'name' => $this->l('Nivo slider','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'carousel'
            ),            
            'YBC_BLOG_SLIDER_AUTO_PLAY' => array(
                'label' => $this->l('Autoplay slider','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,    
                'tab' => 'slider', 
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),           
            ),
            'YBC_BLOG_SLIDER_SPEED' => array(
                'label' => $this->l('Slide delay time','ybc_blog_defines'),
                'type' => 'text',
                'default' => 5000,       
                'validate' => 'isunsignedInt',         
                'suffix' => 'ms',
                'tab' => 'slider',
                'required' => true,
            ),
            'YBC_BLOG_SLIDER_DISPLAY_CAPTION' => array(
                'label' => $this->l('Display caption','ybc_blog_defines'),
                'type' => 'switch',
                'tab' => 'slider',
                'default' => 1, 
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),               
            ),
            'YBC_BLOG_SLIDER_DISPLAY_NAV' => array(
                'label' => $this->l('Display control buttons','ybc_blog_defines'),
                'type' => 'switch',
                'tab' => 'slider',
                'default' => 1, 
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),               
            ),
            'YBC_BLOG_SLIDER_DISPLAY_THUMBNAIL' => array(
                'label' => $this->l('Display thumbnail images','ybc_blog_defines'),
                'type' => 'switch',
                'tab' => 'slider',
                'form_group_class'=>'display_thumb_slider',
                'default' => 1, 
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),               
            ),
            'YBC_BLOG_SHOW_SLIDER' => array(
                'label' => $this->l('Enable image slider','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1, 
                'tab' => 'slider',  
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ) , 
            //GALLERY            
            'YBC_BLOG_GALLERY_SLIDESHOW_ENABLED' => array(
                'label' => $this->l('Enable gallery popup slideshow','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'gallery',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
                'desc' => $this->l('Play gallery photos on popup slider using PrettyPhoto - a Javascript slideshow plug-in','ybc_blog_defines'),
            ),
            'YBC_BLOG_GALLERY_SKIN' => array(
                'label' => $this->l('Gallery slideshow effect','ybc_blog_defines'),
                'type' => 'select',                                    
				'options' => array(
        			 'query' => array( 
                            array(
                                'id_option' => 'default', 
                                'name' => $this->l('Default','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'dark_square', 
                                'name' => $this->l('Dark Square','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'dark_rounded', 
                                'name' => $this->l('Dark Rounded','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'facebook', 
                                'name' => $this->l('Facebook','ybc_blog_defines')
                            ),  
                            array(
                                'id_option' => 'light_square', 
                                'name' => $this->l('Light Square','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'light_rounded', 
                                'name' => $this->l('Light Rounded','ybc_blog_defines')
                            ),                                  
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'light_square',                
                'tab' => 'gallery',
            ),
            'YBC_BLOG_GALLERY_AUTO_PLAY' => array(
                'label' => $this->l('Autoplay gallery slideshow','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,                
                'tab' => 'gallery',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_GALLERY_SPEED' => array(
                'label' => $this->l('Slideshow delay time','ybc_blog_defines'),
                'type' => 'text',
                'default' => 5000,    
                'validate' => 'isunsignedInt',            
                'tab' => 'gallery',
                'suffix' => 'ms',
                'required' => true,  
            ),
            'YBC_BLOG_GALLERY_PER_PAGE' => array(
                'label' => $this->l('Number of image per page','ybc_blog_defines'),
                'type' => 'text',
                //'width' => 200,
                'required' => true,
                'default' => 24,
                'validate' => 'isunsignedInt',                
                'tab' => 'gallery',             
            ), 
            'YBC_BLOG_GALLERY_PER_ROW' => array(
                'label' => $this->l('Number of photo displayed per row','ybc_blog_defines'),
                'type' => 'select',
                'required' => true,
                'default' => 4,
                'validate' => 'isunsignedInt',                
                'tab' => 'gallery',
                'class'=>'col-lg-3',
                'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => '2', 
                                'name' => $this->l('2')
                            ),
                            array(
                                'id_option' => '3', 
                                'name' => $this->l('3')
                            ),
                            array(
                                'id_option' => '4', 
                                'name' => $this->l('4')
                            ),
                            array(
                                'id_option' => '6', 
                                'name' => $this->l('6')
                            ),
                            array(
                                'id_option' => '12', 
                                'name' => $this->l('12')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),             
            ), 
            //BLOG SINGLE PAGE 
            
            //POST FEATURES
            'YBC_BLOG_ALLOW_LIKE' => array(
                'label' => $this->l('Enable blog post likes','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,                
                'tab' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_GUEST_LIKE' => array(
                'label' => $this->l('Allow guests to like posts','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,                
                'tab' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ALLOW_COMMENT' => array(
                'label' => $this->l('Comments system','ybc_blog_defines'),
                'type' => 'radio',
                'values' => array(  
                    array(
                        'value' => '1', 
                        'id'=>'YBC_BLOG_ALLOW_COMMENT_1',
                        'label' => $this->l('Default comment system (recommended)','ybc_blog_defines'),
                    ),
                    array(
                        'value' => '2', 
                        'id'=>'YBC_BLOG_ALLOW_COMMENT_2',
                        'label' => $this->l('Facebook comment','ybc_blog_defines'),
                    ),
                     array(
                        'value' => '0', 
                        'id'=>'YBC_BLOG_ALLOW_COMMENT_0',
                        'label' => $this->l('Do not allow customer to comment','ybc_blog_defines'),
                    ),
                ),
                'default' => 1,                
                'tab' => 'comment',
            ),
            'YBC_BLOG_ALLOW_GUEST_COMMENT' => array(
                'label' => $this->l('Allow guests to comment','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_USE_CAPCHA' => array(
                'label' => $this->l('Use captcha security for comment','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_COMMENT_AUTO_APPROVED' => array(
                'label' => $this->l('Auto-approve comments','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_MAX_COMMENT' => array(
                'label' => $this->l('Maximum number of latest comments displayed','ybc_blog_defines'),
                'type' => 'text',
                'default' => 0,
                'validate' => 'isunsignedInt',
                'required' => true,
                'desc' => $this->l('Set 0 if you want to show all comments of each post','ybc_blog_defines'),                
                'tab' => 'comment',
                'form_group_class' => 'comment'
            ),
            'YBC_BLOG_ALLOW_REPORT' => array(
                'label' => $this->l('Allow customer to report a comment as abused','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ALLOW_GUEST_REPORT' => array(
                'label' => $this->l('Allow guest to report a comment as abused','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ALLOW_RATING' => array(
                'label' => $this->l('Allow visitor to rate a post when submitting a comment','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_DEFAULT_RATING' => array(
                'label' => $this->l('Default rating','ybc_blog_defines'),
                'type' => 'text',
                'default' => 5,
                'validate' => 'isunsignedInt',
                'required' => true,                
                'tab' => 'comment',
                'form_group_class' => 'comment'
            ),   
            'YBC_BLOG_ALLOW_REPLY_COMMENT' => array(
                'label' => $this->l('Allow customers to reply to comments','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_ALLOW_EDIT_COMMENT' => array(
                'label' => $this->l('Allow customers to edit their comments','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_ALLOW_DELETE_COMMENT' => array(
                'label' => $this->l('Allow customers to delete their comments','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),  
            'YBC_BLOG_DISPLAY_GDPR_NOTIFICATION' => array(
                'label' => $this->l('Require customers to accept Privacy policy before submitting comment','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,                
                'tab' => 'comment',
                'form_group_class' => 'comment',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_TEXT_GDPR_NOTIFICATION' => array(
                'label' => $this->l('Label of Privacy policy notification','ybc_blog_defines'),
                'type' => 'text',
                'default' => $this->l('I agree with the use of cookie and personal data according to EU GDPR. See more at','ybc_blog_defines'),                
                'tab' => 'comment',
                'lang'=>true,
                'form_group_class' => 'comment',
            ), 
            'YBC_BLOG_TEXT_GDPR_NOTIFICATION_TEXT_MORE' => array(
                'label' => $this->l('Title of Privacy policy link','ybc_blog_defines'),
                'type' => 'text',
                'default' => $this->l('View more detail here','ybc_blog_defines'),                
                'tab' => 'comment',
                'lang'=>true,
                'form_group_class' => 'comment',
            ), 
            'YBC_BLOG_TEXT_GDPR_NOTIFICATION_URL_MORE' => array(
                'label' => $this->l('Privacy policy link URL','ybc_blog_defines'),
                'type' => 'text',
                'default' => $this->l('#','ybc_blog_defines'),                
                'tab' => 'comment',
                'lang'=>true,
                'form_group_class' => 'comment',
            ),    
            'YBC_BLOG_COMMENT_PER_PAGE' => array(
                'label' => $this->l('Number of comments displayed per page','ybc_blog_defines'),
                'type' => 'text',
                'required' => true,
                'default' => 20,
                'validate' => 'isunsignedInt',                
                'tab' => 'comment',             
            ),   
            'YBC_BLOG_ENABLE_POLLS' => array(
                'label' => $this->l('Enable poll feature (allow customers to vote and leave feedback for your posts)','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'polls',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_POLLS_TEXT' => array(
                'label' => $this->l('Title','ybc_blog_defines'),
                'type' => 'text',
                'default' => 8,
                'lang'=>true,           
                'tab' => 'polls',    
                'default'=>$this->l('Was this blog post helpful to you?','ybc_blog_defines'),
                'required' => true,         
            ),
            'YBC_BLOG_ENABLE_POLLS_GUESTS' => array(
                'label' => $this->l('Allow guests to vote','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'polls',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ENABLE_POLLS_CAPCHA' => array(
                'label' => $this->l('Use captcha security for polls','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'polls',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),   
            'YBC_BLOG_POLLS_FEEDBACK_NEED' => array(
                'label' => $this->l('Is feedback required?','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'tab' => 'polls',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_POLLS_TEXT_MAXIMUM' => array(
                'label' => $this->l('Maximum content length of feedback','ybc_blog_defines'),
                'type' => 'text',
                'default' => 8,    
                'tab' => 'polls',  
                'required2'=>true, 
                'default'=>500,         
            ), 
            'YBC_BLOG_CAPTCHA_TYPE' => array(
                'label' => $this->l('CAPTCHA type'),
                'type' => 'select',                                     
				'options' => array(
        			 'query' => array(                             
                            array(
                                'id_option' => 'image', 
                                'name' => $this->l('Image CAPTCHA','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'google', 
                                'name' => $this->l(' Google reCAPTCHA2','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'google3', 
                                'name' => $this->l(' Google reCAPTCHA3','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name',  
                ),    
                'default' => 'image',                
                'tab' => 'general',
            ), 
            'YBC_BLOG_CAPTCHA_SITE_KEY' => array(
                'label' => $this->l('Site key','ybc_blog_defines'),
                'type' => 'text',
                'tab' => 'general',  
                'required2'=>true,        
            ), 
            'YBC_BLOG_CAPTCHA_SECRET_KEY' => array(
                'label' => $this->l('Secret key','ybc_blog_defines'),
                'type' => 'text', 
                'tab' => 'general',  
                'required2'=>true,     
            ), 
            'YBC_BLOG_CAPTCHA_SITE_KEY3' => array(
                'label' => $this->l('Site key','ybc_blog_defines'),
                'type' => 'text',
                'tab' => 'general',  
                'required2'=>true,        
            ), 
            'YBC_BLOG_CAPTCHA_SECRET_KEY3' => array(
                'label' => $this->l('Secret key','ybc_blog_defines'),
                'type' => 'text', 
                'tab' => 'general',  
                'required2'=>true,     
            ), 
            'YBC_BLOG_RTL_MODE' => array(
                'label' => $this->l('RTL mode'),
                'type' => 'select',                                     
				'options' => array(
        			 'query' => array(                             
                            array(
                                'id_option' => 'auto', 
                                'name' => $this->l('Auto detect','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'rtl', 
                                'name' => $this->l('RTL','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'ltr', 
                                'name' => $this->l('LTR','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name',  
                ),    
                'default' => 'auto',                
                'tab' => 'general',
            ), 
            'YBC_BLOG_ADMIN_FORDER' =>array(
                'label'=> $this->l('Admin folder','ybc_blog_defines'),
                'type'=>'text',
                'tab' => 'general',
                'required' => true,
                'desc' => $this->l('Enter your website admin directory (appeared in back office URLs). This value is used to generate correct URLs to your back office, which will be used in the email sent to admin','ybc_blog_defines')
            ), 
            'YBC_BLOG_DISPLAY_SNIPPET' => array(
                'label' => $this->l('Display review snippet on blog posts','ybc_blog_defines'), 
                'type' => 'switch',
                'default' => 0,                 
                'tab' => 'general',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No')
					)
				),
            ),                                        
        );
        $this->configs_homepage=array(         
                'YBC_BLOG_SHOW_LATEST_BLOCK_HOME' => array(
                    'label' => $this->l('Display latest posts','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 1,  
                    'form_group_class' => 'homepage_new',
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),              
                ),
                'YBC_BLOG_LATEST_POST_NUMBER_HOME' => array(
                    'label' => $this->l('Maximum number of latest posts displayed','ybc_blog_defines'),
                    'required2' => true,
                    'type' => 'text',
                    //'width' => 200,
                    'default' => 5,                         
                ),
                'YBC_BLOG_SHOW_POPULAR_BLOCK_HOME' => array(
                    'label' => $this->l('Display popular posts','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 1,  
                    'form_group_class'=>'homepage_popular',
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),              
                ),
                'YBC_BLOG_POPULAR_POST_NUMBER_HOME' => array(
                    'label' => $this->l('Maximum number of popular posts displayed','ybc_blog_defines'),
                    'type' => 'text',
                    'required2' => true,
                    //'width' => 200,
                    'default' => 5,                           
                ),
                'YBC_BLOG_SHOW_FEATURED_BLOCK_HOME' => array(
                    'label' => $this->l('Display featured posts','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 1, 
                    'form_group_class'=>'homepage_featured',
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),               
                ),
                'YBC_BLOG_FEATURED_POST_NUMBER_HOME' => array(
                    'label' => $this->l('Maximum number of featured posts displayed','ybc_blog_defines'),
                    'type' => 'text',
                    'required2' => true,
                    //'width' => 200,
                    'default' => 5,                         
                ),
                 
                'YBC_BLOG_SHOW_CATEGORY_BLOCK_HOME'=>array(
                    'label' => $this->l('Display specific post categories on home page','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 1, 
                    'form_group_class'=>'homepage_categories',
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),
                ),
                'YBC_BLOG_SHOW_CATEGORIES_BLOCK_HOME'=>array(
                ),
                'YBC_BLOG_CATEGORY_POST_NUMBER_HOME' => array(
                    'label' => $this->l('Maximum number of posts displayed on Specific blog category sections','ybc_blog_defines'),
                    'type' => 'text',
                    'required2' => true,
                    'default' => 5,                           
                ),
                'YBC_BLOG_SHOW_GALLERY_BLOCK_HOME' => array(
                    'label' => $this->l('Display featured gallery images','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 1,   
                    'form_group_class'=>'homepage_gallery',
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),             
                ),
                'YBC_BLOG_GALLERY_BLOCK_HOME_SLIDER_ENABLED' => array(
                    'label' => $this->l('Enable carousel slider for featured gallery block','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 0,  
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),              
                ),
                'YBC_BLOG_GALLERY_POST_NUMBER_HOME' => array(
                    'label' => $this->l('Maximum number of images displayed on featured gallery','ybc_blog_defines'),
                    'type' => 'text',
                    //'width' => 200,
                    'required' => true,
                    'default' => 12,
                    'required2' => true,
                    'validate' => 'isunsignedInt',                           
                ),
                'YBC_BLOG_HOME_POST_TYPE' => array(
                    'label' => $this->l('How to display post blocks on home page','ybc_blog_defines'),
                    'type' => 'select',                                      
    				'options' => array(
            			 'query' => array( 
                                array(
                                    'id_option' => 'default', 
                                    'name' => $this->l('Grid','ybc_blog_defines')
                                ),
                                array(
                                    'id_option' => 'carousel', 
                                    'name' => $this->l('Carousel slider','ybc_blog_defines')
                                ),
                            ),                             
                         'id' => 'id_option',
            			 'name' => 'name'  
                    ),    
                    'default' => 'carousel',                
                ),
                'YBC_BLOG_HOME_PER_ROW' => array(
                    'label' => $this->l('Number of posts per row','ybc_blog_defines'),
                    'type' => 'select',
                    'default' => 4,  
                    'validate' => 'isunsignedInt',              
                    'class'=>'col-lg-3',
                    'options' => array(
            			 'query' => array(                            
                                array(
                                    'id_option' => '2', 
                                    'name' => $this->l('2')
                                ),
                                array(
                                    'id_option' => '3', 
                                    'name' => $this->l('3')
                                ),
                                array(
                                    'id_option' => '4', 
                                    'name' => $this->l('4')
                                ),
                                array(
                                    'id_option' => '6', 
                                    'name' => $this->l('6')
                                ),
                            ),                             
                         'id' => 'id_option',
            			 'name' => 'name'  
                    ),
                ), 
                'YBC_BLOG_HOME_DISPLAY_DESC' => array(
                    'label' => $this->l('Display post excerpt','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 1,  
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),              
                ),
                'YBC_BLOG_DISPLAY_BUTTON_ALL_HOMEPAGE' => array(
                    'label' => $this->l('Display \'View all\' button on post blocks on home page','ybc_blog_defines'),
                    'type' => 'switch',
                    'default' => 0,  
                    'values' => array(
    					array(
    						'id' => 'active_on',
    						'value' => 1,
    						'label' => $this->l('Yes','ybc_blog_defines')
    					),
    					array(
    						'id' => 'active_off',
    						'value' => 0,
    						'label' => $this->l('No','ybc_blog_defines')
    					)
    				),              
                ),  
        );
        $this->configs_postlistpage=array(
            'YBC_BLOG_POST_PAGE_DISPLAY_DESC' => array(
                'label' => $this->l('Display post excerpt for related post for post blocks','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,  
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No')
					)
				),              
            ), 
            'YBC_BLOG_ITEMS_PER_PAGE' => array(
                'label' => $this->l('Number of posts per page on main page','ybc_blog_defines'),
                'type' => 'text',
                //'width' => 200,
                'validate' => 'isunsignedInt',
                'required' => true,
                'default' => 12,                             
            ),
            'YBC_BLOG_ITEMS_PER_PAGE_INNER' => array(
                'label' => $this->l('Number of posts per page on inner pages','ybc_blog_defines'),
                'type' => 'text',
                //'width' => 200,
                'validate' => 'isunsignedInt',
                'required' => true,
                'default' => 12,                          
            ),  
            'YBC_BLOG_POST_EXCERPT_LENGTH' => array(
                'label' => $this->l('Post excerpt length','ybc_blog_defines'),
                'type' => 'text',
                //'width' => 200,
                'validate' => 'isunsignedInt',
                'required' => true,
                'default' => 120,                           
            ), 
            'YBC_BLOG_POST_SORT_BY' => array(
                'label' => $this->l('Sort by','ybc_blog_defines'),
                'type' => 'select',
                'default' => 'id_post',              
                'class'=>'col-lg-3',
                'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => 'id_post', 
                                'name' => $this->l('Latest post','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'sort_order', 
                                'name' => $this->l('Sort order','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'click_number', 
                                'name' => $this->l('Most popular','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),
            ),
            'YBC_BLOG_AUTO_LOAD' => array(
                'label' => $this->l('Enable infinite scroll for post listing page','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_LAZY_LOAD' => array(
                'label' => $this->l('Enable Lazy load effect for blog images','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_CATEGORY_ENABLE_POST_SLIDESHOW' => array(
                'label' => $this->l('Click on the category image to display full size image on a slideshow popup','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
        );
        $this->configs_postpage=array(
            'YBC_BLOG_SHOW_RELATED_PRODUCTS' => array(
                'label' => $this->l('Display related products','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_RELATED_PRODUCTS_TYPE' => array(
                'label' => $this->l('How to display related products','ybc_blog_defines'),
                'type' => 'select',                                        
				'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => 'carousel', 
                                'name' => $this->l('Carousel slider','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'default', 
                                'name' => $this->l('Grid','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'carousel',                
                'form_group_class'=>'related_product'
            ),
            'YBC_BLOG_RELATED_PRODUCT_ROW' => array(
                'label' => $this->l('Number of related products displayed per row','ybc_blog_defines'),
                'type' => 'select',
                'default' => 4,  
                'validate' => 'isunsignedInt',              
                'class'=>'col-lg-3',
                'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => '2', 
                                'name' => $this->l('2')
                            ),
                            array(
                                'id_option' => '3', 
                                'name' => $this->l('3')
                            ),
                            array(
                                'id_option' => '4', 
                                'name' => $this->l('4')
                            ),
                            array(
                                'id_option' => '6', 
                                'name' => $this->l('6')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),
                'form_group_class'=>'related_product'
            ),
            'YBC_BLOG_DISPLAY_RELATED_POSTS' => array(
                'label' => $this->l('Display related posts','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
                'desc' => $this->l('Blog posts with the same post tags or same category','ybc_blog_defines'),
            ),
            'YBC_BLOG_RELATED_POST_NUMBER' => array(
                'label' => $this->l('Maximum number of related posts displayed','ybc_blog_defines'),
                'type' => 'text',
                'default' => 5,  
                'validate' => 'isunsignedInt',              
                'class'=>'col-lg-3',
                'required2' => true,
                'form_group_class'=>'related_post',
                'desc' => $this->l('Leave blank to display all','ybc_blog_defines'),
            ),
            'YBC_RELATED_POSTS_TYPE' => array(
                'label' => $this->l('How to display related posts','ybc_blog_defines'),
                'type' => 'select',                                        
				'options' => array(
        			 'query' => array(                             
                            array(
                                'id_option' => 'carousel', 
                                'name' => $this->l('Carousel slider','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'default', 
                                'name' => $this->l('Grid','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name',  
                ),    
                'default' => 'carousel',                
                'form_group_class'=>'related_post'
            ), 
            'YBC_BLOG_RELATED_POST_ROW' => array(
                'label' => $this->l('Number of related posts displayed per row','ybc_blog_defines'),
                'type' => 'select',
                'default' => 3,  
                'validate' => 'isunsignedInt',              
                'class'=>'col-lg-3',
                'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => '2', 
                                'name' => $this->l('2')
                            ),
                            array(
                                'id_option' => '3', 
                                'name' => $this->l('3')
                            ),
                            array(
                                'id_option' => '4', 
                                'name' => $this->l('4')
                            ),
                            array(
                                'id_option' => '6', 
                                'name' => $this->l('6')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),
                'form_group_class'=>'related_post'
            ),  
            'YBC_BLOG_ENABLE_POST_SLIDESHOW' => array(
                'label' => $this->l('Click on the blog post image to display full size image on a slideshow popup','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_SHOW_POST_VIEWS' => array(
                'label' => $this->l('Display post views','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_SHOW_POST_DATE' => array(
                'label' => $this->l('Display post publish date','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_SHOW_POST_TAGS' => array(
                'label' => $this->l('Display post tags','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_SHOW_POST_CATEGORIES' => array(
                'label' => $this->l('Display post categories','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),  
            'YBC_BLOG_SHOW_POST_AUTHOR' => array(
                'label' => $this->l('Display post author','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_AUTHOR_INFORMATION' => array(
                'label' => $this->l('Display author information','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,                
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
        ); 
        $this->configs_categorypage=array(
            'YBC_BLOG_DISPLAY_CATEGORY_PAGE' => array(
                'label' => $this->l('Display related posts on category page','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_CATEGORY_POST_TYPE' => array(
                'label' => $this->l('How to display post block','ybc_blog_defines'),
                'type' => 'select',                                      
				'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => 'carousel', 
                                'name' => $this->l('Carousel slider','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'default', 
                                'name' => $this->l('Grid','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'carousel',                
            ),
            'YBC_BLOG_NUMBER_POST_IN_CATEGORY' => array(
                'label' => $this->l('Maximum number of related posts displayed','ybc_blog_defines'),
                'type' => 'text',
                'default' => 6,
                'required2' => true,
                'desc'=> $this->l('Leave blank to display all related posts for each product category','ybc_blog_defines'),

            ), 
            'YBC_BLOG_RELATED_CATEGORY_ROW' => array(
                'label' => $this->l('Number of related posts displayed per row','ybc_blog_defines'),
                'type' => 'select',
                'default' => 3,  
                'validate' => 'isunsignedInt',              
                'class'=>'col-lg-3',
                'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => '2', 
                                'name' => $this->l('2')
                            ),
                            array(
                                'id_option' => '3', 
                                'name' => $this->l('3')
                            ),
                            array(
                                'id_option' => '4', 
                                'name' => $this->l('4')
                            ),
                            array(
                                'id_option' => '6', 
                                'name' => $this->l('6')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),
            ),
            'YBC_BLOG_CATEGORY_PAGE_DISPLAY_DESC' => array(
                'label' => $this->l('Display post excerpt for related posts','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
        );
        $this->configs_productpage=array(
            'YBC_BLOG_DISPLAY_PRODUCT_PAGE' => array(
                'label' => $this->l('Display related posts on product page','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_PRODUCT_POST_TYPE' => array(
                'label' => $this->l('How to display post block on product page','ybc_blog_defines'),
                'type' => 'select',                                      
				'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => 'carousel', 
                                'name' => $this->l('Carousel slider','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'default', 
                                'name' => $this->l('Grid','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'carousel',                
            ),
            'YBC_BLOG_NUMBER_POST_IN_PRODUCT' => array(
                'label' => $this->l('Maximum number of related posts displayed','ybc_blog_defines'),
                'type' => 'text',
                'default' =>8,
                'required2' => true,
                'desc' => $this->l('Leave blank to display all related posts for each product on product page','ybc_blog_defines'),
            ), 
            'YBC_BLOG_RELATED_POST_ROW_IN_PRODUCT' => array(
                'label' => $this->l('Number of related posts displayed per row','ybc_blog_defines'),
                'type' => 'select',
                'default' => 4,  
                'validate' => 'isunsignedInt',              
                'class'=>'col-lg-3',
                'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => '2', 
                                'name' => $this->l('2')
                            ),
                            array(
                                'id_option' => '3', 
                                'name' => $this->l('3')
                            ),
                            array(
                                'id_option' => '4', 
                                'name' => $this->l('4')
                            ),
                            array(
                                'id_option' => '6', 
                                'name' => $this->l('6')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),
            ),
            'YBC_BLOG_PRODUCT_PAGE_DISPLAY_DESC' => array(
                'label' => $this->l('Display post excerpt for related post','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
        );
        $this->configs_sidebar=array(
            'YBC_BLOG_SHOW_LATEST_NEWS_BLOCK' => array(
                'label' => $this->l('Display latest news block','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,   
                'form_group_class' =>'sidebar_new',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ),
            'YBC_BLOG_LATES_POST_NUMBER' => array(
                'label' => $this->l('Maximum number of newest posts displayed','ybc_blog_defines'),
                'type' => 'text',
                'required2' => true,
                //'width' => 200,
                'default' => 5,    
                'validate' => 'isunsignedInt',                         
            ),
            'YBC_BLOG_SHOW_POPULAR_POST_BLOCK' => array(
                'label' => $this->l('Display popular posts block','ybc_blog_defines'),
                'type' => 'switch',
                'form_group_class' =>'sidebar_popular',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),                
            ),
            'YBC_BLOG_PUPULAR_POST_NUMBER' => array(
                'label' => $this->l('Maximum number of popular posts displayed','ybc_blog_defines'),
                'type' => 'text',
                'required2' => true,
                //'width' => 200,
                'default' => 5,
                'validate' => 'isunsignedInt',                           
            ),
            'YBC_BLOG_SHOW_FEATURED_BLOCK' => array(
                'label' => $this->l('Display featured posts','ybc_blog_defines'),
                'type' => 'switch',
                'form_group_class' =>'sidebar_featured',
                'default' => 1,   
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ),
            'YBC_BLOG_FEATURED_POST_NUMBER' => array(
                'label' => $this->l('Maximum number of featured posts displayed','ybc_blog_defines'),
                'type' => 'text',
                'required2' => true,
                //'width' => 200,
                'default' => 5, 
                'validate' => 'isunsignedInt',                           
            ),
            'YBC_BLOG_SHOW_GALLERY_BLOCK' => array(
                'label' => $this->l('Display featured gallery images','ybc_blog_defines'),
                'type' => 'switch',
                'form_group_class' =>'sidebar_gallery',
                'default' => 1, 
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),               
            ), 
            'YBC_BLOG_GALLERY_BLOCK_SIDEBAR_SLIDER_ENABLED' => array(
                'label' => $this->l('Enable gallery carousel slider','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,  
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),              
            ), 
            'YBC_BLOG_GALLERY_POST_NUMBER' => array(
                'label' => $this->l('Maximum number of images displayed on featured gallery','ybc_blog_defines'),
                'type' => 'text',
                'required2' => true,
                //'width' => 200,
                'default' => 6,    
                'validate' => 'isunsignedInt',                         
            ),
            'YBC_BLOG_SHOW_ARCHIVES_BLOCK' => array(
                'label' => $this->l('Display archived posts block','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,  
                'form_group_class' =>'sidebar_archived',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),              
            ),
            'YBC_BLOG_EXPAND_ARCHIVES_BLOCK' => array(
                'label' => $this->l('Expand archived posts block by default','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,  
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),              
            ),
            'YBC_BLOG_SHOW_CATEGORIES_BLOCK' => array(
                'label' => $this->l('Display categories block','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,  
                'form_group_class' =>'sidebar_categories',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),              
            ),
            'YBC_BLOG_SHOW_SEARCH_BLOCK' => array(
                'label' => $this->l('Display post search block','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1, 
                'form_group_class' =>'sidebar_search',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),               
            ),            
            'YBC_BLOG_SHOW_TAGS_BLOCK' => array(
                'label' => $this->l('Display tags block','ybc_blog_defines'),
                'type' => 'switch',
                'form_group_class' =>'sidebar_tags',
                'default' => 1, 
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),               
            ),
            'YBC_BLOG_TAGS_NUMBER' => array(
                'label' => $this->l('Maximum number of tags displayed on Tags block'),
                'type' => 'text',
                'required2' => true,
                'default' => 20, 
                'validate' => 'isunsignedInt',                            
            ), 
            'YBC_BLOG_SHOW_COMMENT_BLOCK' => array(
                'label' => $this->l('Display latest comments'),
                'type' => 'switch',
                'form_group_class' =>'sidebar_comments',
                'default' => 1,   
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ),
            'YBC_BLOG_COMMENT_NUMBER' => array(
                'label' => $this->l('Maximum number of latest comments displayed on sidebar','ybc_blog_defines'),
                'type' => 'text',
                'default' => 5, 
                'required2' => true,
                'validate' => 'isunsignedInt',                            
            ),
            'YBC_BLOG_COMMENT_LENGTH' => array(
                'label' => $this->l('Maximum comment length of latest comments displayed','ybc_blog_defines'),
                'type' => 'text',
                //'width' => 200,
                'required2' => true,
                'default' => 60, 
                'validate' => 'isunsignedInt',                           
            ),
            'YBC_BLOG_SHOW_AUTHOR_BLOCK' => array(
                'label' => $this->l('Display top authors block on sidebar','ybc_blog_defines'),
                'type' => 'switch',
                'form_group_class' =>'sidebar_authors',
                'default' => 1,  
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),              
            ),
            
            'YBC_BLOG_AUTHOR_NUMBER' => array(
                'label' => $this->l('Maximum number of top authors','ybc_blog_defines'),
                'required2' => true,
                'type' => 'text',
                'default' => 5,           
            ),
            'YBC_BLOG_SHOW_HTML_BOX' => array(
                'label' => $this->l('Enable HTML box','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,
                'form_group_class' =>'sidebar_htmlbox',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_TITLE_HTML_BOX' => array(
                'type'=>'text',
                'lang' => true,
                'label'=> $this->l('Title','ybc_blog_defines'),
            ),
            'YBC_BLOG_CONTENT_HTML_BOX' => array(
                'type'=>'textarea',
                'lang' => true,
                'label'=> $this->l('Content HTML','ybc_blog_defines'),
                'autoload_rte' => true,
            ),
            'YBC_BLOG_ENABLE_RSS_SIDEBAR' => array(
                'label' => $this->l('Enable RSS feed','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'form_group_class' =>'sidebar_rss',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),  
            'YBC_BLOG_SIDEBAR_POSITION' => array(
                'label' => $this->l('Sidebar position','ybc_blog_defines'),
                'type' => 'select',                                       
				'options' => array(
        			 'query' => array( 
                            array(
                                'id_option' => 'left', 
                                'name' => $this->l('Left','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'right', 
                                'name' => $this->l('Right','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'none', 
                                'name' => $this->l('No sidebar','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'left',                
            ),  
            'YBC_BLOG_SIDEBAR_ON_MOBILE' => array(
                'label' => $this->l('Expand sidebar on mobile by default','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,
                'form_group_class'=>'mobile_slidebar',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_NAVIGATION_TITLE' => array(
                'label' => $this->l('Sidebar title when collapsed','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'Blog navigation',
                'lang'=>true,
                'form_group_class'=>'mobile_slidebar mobile_slidebar_off'
            ),  
            'YBC_BLOG_SIDEBAR_DISPLAY_DESC' => array(
                'label' => $this->l('Display post excerpt for related post for post blocks','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'form_group_class'=>'mobile_slidebar',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),  
            'YBC_BLOG_SIDEBAR_POST_TYPE' => array(
                'label' => $this->l('How to display post blocks in sidebar','ybc_blog_defines'),
                'type' => 'select',                                      
				'options' => array(
        			 'query' => array(                            
                            array(
                                'id_option' => 'carousel', 
                                'name' => $this->l('Carousel slider','ybc_blog_defines')
                            ),
                            array(
                                'id_option' => 'default', 
                                'name' => $this->l('Grid','ybc_blog_defines')
                            ),
                        ),                             
                     'id' => 'id_option',
        			 'name' => 'name'  
                ),    
                'default' => 'carousel',                
            ),
            'YBC_BLOG_DISPLAY_BLOG_ONLY' => array(
                'label' => $this->l('Display blog sidebar elements on blog pages only','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
                     
        );
        $this->configs_email=array(
            'YBC_BLOG_ENABLE_MAIL' => array(
                'label' => $this->l('When a comment is posted','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_ENABLE_MAIL_POLLS' => array(
                'label' => $this->l('When customer votes a post','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_ENABLE_MAIL_EDIT_COMMENT' => array(
                'label' => $this->l('When customer modified the comment','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ), 
            'YBC_BLOG_ENABLE_MAIL_REPORT' => array(
                'label' => $this->l('When a comment is reported as abused','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ENABLE_MAIL_REPLY' => array(
                'label' => $this->l('When user replies to post comments','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ADMIN_EMAIL_NEW_POST' => array(
                'label' => $this->l('When customer (community author) adds a new post','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'form_group_class' =>'setting_customer_author',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ALERT_EMAILS' => array(
                'label' => $this->l('Email address(es) to receive notifications','ybc_blog_defines'),
                'type' => 'text',                
                'desc' => $this->l('Emails that you want to receive notifications, separated by a comma (,)','ybc_blog_defines'),        
            ),
            'YBC_BLOG_ENABLE_MAIL_NEW_COMMENT' => array(
                'label' => $this->l('When submit new comment successfully','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ENABLE_MAIL_NEW_POLLS' => array(
                'label' => $this->l('When vote a post successfully','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ENABLE_MAIL_APPROVED' => array(
                'label' => $this->l('When his/her comment is approved','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ENABLE_MAIL_EDIT_COMMENT_CUSTOMER' => array(
                'label' => $this->l('When his/her comment updated','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ENABLE_MAIL_REPLY_CUSTOMER' => array(
                'label' => $this->l('When admin/other users replied to his/her comment','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ENABLE_MAIL_REPORTED_CUSTOMER' => array(
                'label' => $this->l('When his/her comment is reported as abused','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 0,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_CUSTOMER_EMAIL_NEW_POST' => array(
                'label' => $this->l('When their post is successfully added','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'form_group_class' =>'setting_customer_author',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					) 
				),
            ),
            'YBC_BLOG_CUSTOMER_EMAIL_APPROVED_POST' => array(
                'label' => $this->l('When their post is approved','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'form_group_class' =>'setting_customer_author',
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            
        );
        $this->configs_seo=array(
        'YBC_BLOG_FRIENDLY_URL' => array(
                'label' => $this->l('Enable blog friendly URL','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
            ),
            'YBC_BLOG_ALIAS' => array(
                'label' => $this->l('Blog alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'blog',
                'desc' => $this->l('Your blog main page:','ybc_blog_defines').' <a href="'.$this->getLink().'" class="ybc-link-desc">'.$this->getLink().'</a><br/>'.$this->l('Copy this link and paste it to your top menu or somewhere in order to link the blog area with your website','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_URL_SUBFIX' => array(
                'label' => $this->l('Use URL suffix','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
                'desc' => $this->l('Enable to add ".html" to the end of URLs','ybc_blog_defines'),
            ),
            'YBC_BLOG_URL_NO_ID' => array(
                'label' => $this->l('Remove blog post and categories ID on URL','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),
                'desc' => $this->l('Make URLs more friendly','ybc_blog_defines'),
            ),
            'YBC_BLOG_META_TITLE' => array(
                'label' => $this->l('Blog meta title','ybc_blog_defines'),
                'type' => 'text',
                'default' => $this->l('BLOG','ybc_blog_defines'),
                'lang' => true,
                'required' => true,
            ),
            'YBC_BLOG_META_KEYWORDS' => array(
                'label' => $this->l('Blog meta keywords','ybc_blog_defines'),
                'type' => 'tags',
                'default' => $this->l('lorem,ipsum,dolor','ybc_blog_defines'),
                'lang' => true,                
                'desc' => $this->l('Separated by a comma (,)','ybc_blog_defines'),               
            ),
            'YBC_BLOG_META_DESCRIPTION' => array(
                'label' => $this->l('Blog meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'default' => $this->l('The most powerful, flexible and feature-rich blog module for Prestashop. BLOG provides everything you need to create a professional blog area for your website.','ybc_blog_defines'),
                'lang' => true,                
            ), 
            'YBC_BLOG_ALIAS_POST' => array(
                'label' => $this->l('Single post page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'post',
                'desc' => $this->l('Default: "post"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ), 
            'YBC_BLOG_ALIAS_CATEGORY' => array(
                'label' => $this->l('Category page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'category',
                'desc' => $this->l('Default: "category"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),  
            'YBC_BLOG_ALIAS_CATEGORIES' => array(
                'label' => $this->l('Category listing page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'categories',
                'desc' => $this->l('Default: "categories"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_SEO_CATEGORIES' => array(
                'label' => $this->l('Category listing page meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'lang'=>true,
            ),            
            'YBC_BLOG_ALIAS_COMMENTS' => array(
                'label' => $this->l('Comment listing page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'comments',
                'desc' => $this->l('Default: "comments"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_GALLERY' => array(
                'label' => $this->l('Gallery page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'gallery',
                'desc' => $this->l('Default: "gallery"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_SEO_GALLERY' => array(
                'label' => $this->l('Gallery page meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'lang'=>true,
            ),            
            'YBC_BLOG_ALIAS_LATEST' => array(
                'label' => $this->l('Latest posts page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'latest',
                'desc' => $this->l('Default: "latest"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_SEO_LATEST' => array(
                'label' => $this->l('Latest posts page meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'lang'=>true,
            ),            
            'YBC_BLOG_ALIAS_POPULAR' => array(
                'label' => $this->l('Popular posts page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'popular',
                'desc' => $this->l('Default: "popular"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_SEO_POPULAR' => array(
                'label' => $this->l('Popular posts page meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'lang'=>true,
            ),            
            'YBC_BLOG_ALIAS_FEATURED' => array(
                'label' => $this->l('Featured posts page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'featured',
                'desc' => $this->l('Default: "featured"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_SEO_FEATURED' => array(
                'label' => $this->l('Featured posts page meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'lang'=>true,
            ),            
            'YBC_BLOG_ALIAS_SEARCH' => array(
                'label' => $this->l('Search page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'search',
                'desc' => $this->l('Default: "search"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_SEO_SEARCH' => array(
                'label' => $this->l('Search page meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'lang'=>true,
            ),            
            'YBC_BLOG_ALIAS_AUTHOR' => array(
                'label' => $this->l('Author page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'author',
                'desc' => $this->l('Default: "author"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_SEO_AUTHOR' => array(
                'label' => $this->l('Author page meta description','ybc_blog_defines'),
                'type' => 'textarea',
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_AUTHOR2' => array(
                'label' => $this->l('Community author page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'community-author',
                'desc' => $this->l('Default: "community-author"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_TAG' => array(
                'label' => $this->l('Tag page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'tag',
                'desc' => $this->l('Default: "tag"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
             'YBC_BLOG_ALIAS_YEARS' => array(
                'label' => $this->l('Archive year page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'year',
                'desc' => $this->l('Default: "year"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_MONTHS' => array(
                'label' => $this->l('Archive month page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'month',
                'desc' => $this->l('Default: "month"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_RSS' => array(
                'label' => $this->l('RSS page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'rss',
                'desc' => $this->l('Default: "rss"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_RSS_LATEST' => array(
                'label' => $this->l('RSS latest posts page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'latest-posts',
                'desc' => $this->l('Default: "latest-posts"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_RSS_POPULAR' => array(
                'label' => $this->l('RSS popular posts page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'popular-posts',
                'desc' => $this->l('Default: "popular-posts"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
            'YBC_BLOG_ALIAS_RSS_FEATURED' => array(
                'label' => $this->l('RSS featured posts page alias','ybc_blog_defines'),
                'type' => 'text',
                'default' => 'featured-posts',
                'desc' => $this->l('Default: "featured-posts"','ybc_blog_defines'),
                'required' => true,
                'lang'=>true,
            ),
        );     
        $this->socials=array(
            'YBC_BLOG_ENABLE_FACEBOOK_SHARE' => array(
                'label' => $this->l('Enable Facebook share','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1, 
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),               
            ),
            'YBC_BLOG_ENABLE_TWITTER_SHARE' => array(
                'label' => $this->l('Enable Twitter share','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,   
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ),  
            'YBC_BLOG_ENABLE_PINTEREST_SHARE' => array(
                'label' => $this->l('Enable Pinterest share','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,   
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ), 
            'YBC_BLOG_ENABLE_LIKED_SHARE' => array(
                'label' => $this->l('Enable LinkedIn share','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,   
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ), 
            'YBC_BLOG_ENABLE_TUMBLR_SHARE' => array(
                'label' => $this->l('Enable Tumblr share','ybc_blog_defines'),
                'type' => 'switch',
                'default' => 1,   
                'values' => array(
					array(
						'id' => 'active_on',
						'value' => 1,
						'label' => $this->l('Yes','ybc_blog_defines')
					),
					array(
						'id' => 'active_off',
						'value' => 0,
						'label' => $this->l('No','ybc_blog_defines')
					)
				),             
            ), 
        );
    }
    public function getLink($controller = 'blog', $params = array(),$id_lang=0)
     {
        $context = Context::getContext();      
        $id_lang =  $id_lang ? $id_lang : $context->language->id;
        $alias = $this->alias;
        $friendly = $this->friendly;
        $blogLink = new Ybc_blog_link_class();
        $page = isset($params['page']) && $params['page'] ? $params['page'] : '';
        if(trim($page)!='')
        {
            $page = $page.'/';
        }
        else
            $page='';        
        if($friendly && $alias)
        {    
            $url = $blogLink->getBaseLinkFriendly(null, null).$blogLink->getLangLinkFriendly($id_lang, null, null).$alias;
            return $url;         
        }
        $extra='';
        if($params)
            foreach($params as $key=> $param)
                $extra ='&'.$key.'='.$param;
        return Tools::getShopDomainSsl(true).__PS_BASE_URI__.'index.php?fc=module&module=ybc_blog&controller='.$controller.'&id_lang='.$this->context->language->id.$extra;
     }
     public function getGroups($list_id=false)
     {
        $sql ='SELECT g.id_group as value, gl.name as label FROM '._DB_PREFIX_.'group g
            LEFT JOIN '._DB_PREFIX_.'group_lang gl ON (g.id_group=gl.id_group AND gl.id_lang="'.(int)$this->context->language->id.'")
        WHERE g.id_group !="'.(int)Configuration::get('PS_UNIDENTIFIED_GROUP').'" AND g.id_group !="'.(int)Configuration::get('PS_GUEST_GROUP').'"
        ';
        $groups=Db::getInstance()->executeS($sql);
        if($list_id)
        {
            $ids='';
            foreach($groups as $key=> $group)
            {
                if($key+1 < count($groups))
                    $ids .=$group['value'].',';
                    
            }
            return $ids;
        }    
        return $groups;
    }
    public function getBaseLink()
    {
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')?'https://':'http://').$this->context->shop->domain.$this->context->shop->getBaseURI();
    }
}