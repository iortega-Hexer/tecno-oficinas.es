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
    $languages = Language::getLanguages(false);
    $tempDir = dirname(__FILE__).'/../views/img/temp/';
    $imgDir = _PS_YBC_BLOG_IMG_DIR_;
    copy($tempDir.'default_customer.png',$imgDir.'avata/default_customer.png');
    //Install sample data
    //Category
    $category = new Ybc_blog_category_class();
    $category->enabled = 1;
    $category->sort_order = 1;
    $category->datetime_added = date('Y-m-d H:i:s');
    $category->datetime_modified = date('Y-m-d H:i:s');
    $category->added_by = (int)Context::getContext()->employee->id;
    $category->modified_by = (int)Context::getContext()->employee->id;
    $category->id_parent=0;
    foreach($languages as $language)
    {
        $category->url_alias[$language['id_lang']] = 'sample-category';
        $category->title[$language['id_lang']] = 'Sample Category';
        $category->description[$language['id_lang']] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';
        $category->meta_description[$language['id_lang']] = 'Sample category meta description';
        $category->meta_keywords[$language['id_lang']] = 'Lorem,ipsum';
    }
    $category->save();
    
    //Post
    for ($i = 1; $i <= 1; $i++){
        $post = new Ybc_blog_post_class();
        $post->id_post = $i;
        $post->enabled = $i;
        $post->sort_order = $i;
        $post->datetime_added = date('Y-m-d H:i:s');
        $post->datetime_modified = date('Y-m-d H:i:s');
        $post->added_by = (int)Context::getContext()->employee->id;
        $post->modified_by = (int)Context::getContext()->employee->id;
        $post->click_number = 0;
        $post->likes = 0;
        $post->products = '';
        $post->is_featured = 1;    
        $post->id_category_default =$category->id;    
        foreach($languages as $language)
        {
            $post->title[$language['id_lang']] = 'Sample blog post';
            $post->url_alias[$language['id_lang']] = 'sample-post'.$i;
            $post->short_description[$language['id_lang']] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';
            $post->description[$language['id_lang']] = 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.';
            $post->description[$language['id_lang']] .= '<br/>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.';
            $post->description[$language['id_lang']] .= '<br/>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.';
            $post->description[$language['id_lang']] .= '<br/>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.';
            $post->description[$language['id_lang']] .= '<br/>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.';
            $post->meta_description[$language['id_lang']] = 'Sample post meta description';
            $post->meta_keywords[$language['id_lang']] = 'Lorem,minim';
            $post->thumb[$language['id_lang']] = 'post-thumb-sample.jpg';
            $post->image[$language['id_lang']] = 'post.jpg';
        }
        $post->save();
        if(file_exists($tempDir.'post.jpg'))
            @copy($tempDir.'post.jpg',$imgDir.'post/post.jpg');
        if(file_exists($tempDir.'post-thumb-sample.jpg'))
            @copy($tempDir.'post-thumb-sample.jpg',$imgDir.'post/thumb/post-thumb-sample.jpg');
        
        $req ="INSERT INTO `"._DB_PREFIX_."ybc_blog_post_category`(id_post, id_category,position)  VALUES(".(int)$post->id.",".(int)$category->id.",".(int)$i.")";
        Db::getInstance()->execute($req);
        
        foreach($languages as $language)
        {
            $req ="INSERT INTO `"._DB_PREFIX_."ybc_blog_tag`(id_post, id_lang, tag, click_number)  VALUES(".(int)$post->id.",".(int)$language['id_lang'].",'Lorem',0)";
            Db::getInstance()->execute($req);
            $req ="INSERT INTO `"._DB_PREFIX_."ybc_blog_tag`(id_post, id_lang, tag, click_number)  VALUES(".(int)$post->id.",".(int)$language['id_lang'].",'Consectetur',0)";
            Db::getInstance()->execute($req);
        }    
    }
    $captions=array(
        1=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ',
        2=>'Lorem ipsum dolor sit amet,  sed do eiusmod tempor incididunt ut labore et dolore magna aliqua, consectetur adipiscing elit',
        3=>'Consectetur adipiscing elit, lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ',
        4=>'Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua, lorem ipsum dolor sit amet.',
        5=>'Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua, consectetur adipiscing elit.',
        6=>'Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua, ut enim ad minim veniam.',
        7=>'Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua, ut enim ad minim veniam.',
        
    );
    for($i=1;$i<=2;$i++)
    {
        $slide = new Ybc_blog_slide_class();
        $slide->enabled = 1;
        $slide->sort_order = $i;
        foreach($languages as $language)
        {
            $slide->url[$language['id_lang']] = '#';
            $slide->caption[$language['id_lang']] = $captions[$i];   
            $slide->image[$language['id_lang']] = 'slide'.$i.'.jpg';     
        }    
        $slide->save();
        if(file_exists($tempDir.'slide'.$i.'.jpg'))
            @copy($tempDir.'slide'.$i.'.jpg',$imgDir.'slide/slide'.$i.'.jpg');
    }  
    //Gallery
    $gallery = new Ybc_blog_gallery_class();
    $gallery->id_gallery = 1;
    $gallery->enabled = 1;
    $gallery->sort_order = 1;
    $gallery->url = '';
    $gallery->is_featured = 1;
    foreach($languages as $language)
    {
        $gallery->title[$language['id_lang']] = 'Sample gallery';  
        $gallery->description[$language['id_lang']] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et'; 
        $gallery->image[$language['id_lang']] = 'gallery.jpg';             
    }    
    $gallery->save();
    if(file_exists($tempDir.'gallery.jpg'))
        @copy($tempDir.'gallery.jpg',$imgDir.'gallery/gallery.jpg');
    if(file_exists($tempDir.'gallery-thumb.jpg'))
        @copy($tempDir.'gallery-thumb.jpg',$imgDir.'gallery/thumb/gallery-thumb.jpg');