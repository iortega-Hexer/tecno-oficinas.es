{*
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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if isset($import_ok) && $import_ok}
    <div class="ybc_blog_alert success">
        <div class="bootstrap">
            <div class="module_confirmation conf confirm alert alert-success">
                <button data-dismiss="alert" class="close" type="button">×</button>
                    {l s='Import successfully' mod='ybc_blog'} 
            </div>
        </div>
    </div>
{/if}
<form id="module_form" class="defaultForm form-horizontal" novalidate="" enctype="multipart/form-data" method="post" action="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=ybc_blog&tab_module=front_office_features&module_name=ybc_blog&control=export">
    <div id="fieldset_0" class="panel">
        <div class="panel-heading">
            <i class="material-icons"></i>
            {l s='Import/Export' mod='ybc_blog'}
        </div>
        <div class="ybc_blog_export_form_content">      
            <div class="ybc_blog_export_option">
                <div class="panel-heading">
                    {l s='EXPORT' mod='ybc_blog'}
                </div>
                <button type="submit" name="submitExportBlog" class="submitExportBlog"><i class="icon icon-download"></i>{l s='EXPORT' mod='ybc_blog'}</button>
                <p class="ybc_blog_export_option_note">{l s='Export entire your blog data including blog posts, blog categories, comments, polls, images, etc. and module configuration. The exported data is a complete backup of your blog module and can be restored using "IMPORT FROM BLOG" feature' mod='ybc_blog'}</p>
            </div>
            <div class="ybc_blog_import_blog_option">
                <div class="panel-heading">
                    {l s='Import from blog' mod='ybc_blog'}
                </div>
                <div class="ybc_blog_import_option_updata">
                    <label for="blogdata">{l s='Data package' mod='ybc_blog'}</label>
                    <input id="blogdata" type="file" name="blogdata" />
                </div>
                <div class="ybc_blog_import_option_data_imoport">
                    <p>{l s='Select types of data to import:' mod='ybc_blog'}</p>
                    <div class="data-group post-category">
                        <label for="data_import_posts_categories"><input type="checkbox" id="data_import_posts_categories" name="data_import[]" value="posts_categories" {if !isset($data_import) || (isset($data_import) && is_array($data_import) && in_array('posts_categories',$data_import))}checked="checked"{/if} /> {l s='Posts and categories' mod='ybc_blog'}</label>
                    </div>
                    <div class="data-group comments">
                        <label for="data_import_posts_comments"><input type="checkbox" id="data_import_posts_comments" name="data_import[]" value="posts_comments" {if !isset($data_import) || (isset($data_import) && is_array($data_import) && in_array('posts_comments',$data_import))}checked="checked"{/if}/> {l s='Posts comments' mod='ybc_blog'}</label>
                    </div>
                    <div class="data-group comments">
                        <label for="data_import_posts_polls"><input type="checkbox" id="data_import_posts_polls" name="data_import[]" value="posts_polls" {if !isset($data_import) || (isset($data_import) && is_array($data_import) && in_array('posts_polls',$data_import))}checked="checked"{/if}/> {l s='Polls' mod='ybc_blog'}</label>
                    </div>
                    <div class="data-group slider">
                        <label for="data_import_slider_images"><input type="checkbox" id="data_import_slider_images" name="data_import[]" value="slider_images" {if !isset($data_import) || (isset($data_import) && is_array($data_import) && in_array('slider_images',$data_import))}checked="checked"{/if}/> {l s='Slider images' mod='ybc_blog'}</label>
                    </div>
                    <div class="data-group gallery">
                        <label for="data_import_gallery_images"><input type="checkbox" id="data_import_gallery_images" name="data_import[]" value="gallery_images" {if !isset($data_import) || (isset($data_import) && is_array($data_import) && in_array('gallery_images',$data_import))}checked="checked"{/if}/> {l s='Gallery images' mod='ybc_blog'}</label>
                    </div>
                    <div class="data-group config">
                        <label for="data_import_module_configuration"><input type="checkbox" id="data_import_module_configuration" name="data_import[]" value="module_configuration" {if !isset($data_import) || (isset($data_import) && is_array($data_import) && in_array('module_configuration',$data_import))}checked="checked"{/if}/> {l s='Module configuration' mod='ybc_blog'}</label>
                    </div>
                </div>
                <div class="ybc_blog_import_options">
                    <p>{l s='Import options:' mod='ybc_blog'}</p>
                    <div class="option_group importoverride">
                        <label for="importoverride"><input type="checkbox" name="importoverride" id="importoverride" value="1" {if !isset($importoverride) ||(isset($importoverride) && $importoverride)}checked="checked"{/if} /> {l s='Override existing if exist the same ID' mod='ybc_blog'}</label>
                    </div>
                    <div class="option_group keepauthorid">
                        <label for="keepauthorid"><input type="checkbox" name="keepauthorid" id="keepauthorid" value="1" {if !isset($keepauthorid) ||(isset($keepauthorid) && $keepauthorid)}checked="checked"{/if}/> {l s='Keep post author ID' mod='ybc_blog'}</label>
                    </div>
                    <div class="option_group keepcommenter">
                        <label for="keepcommenter"><input type="checkbox" name="keepcommenter" id="keepcommenter" value="1" {if !isset($keepcommenter) ||(isset($keepcommenter) && $keepcommenter)}checked="checked"{/if} /> {l s='Keep commenter ID' mod='ybc_blog'}</label>
                    </div>
                </div>
                <div class="ybc_blog_import_option_button">
                    <div class="ybc_blog_import_submit">
                        <button type="submit" name="submitImportBlog" class="submitImportBlog"><i class="icon icon-compress"></i>{l s='IMPORT' mod='ybc_blog'}</button>
                    </div>
                </div>
            </div>
            <div class="ybc_blog_import_blog_option">
                <div class="panel-heading">
                    {l s='Import from wordpress' mod='ybc_blog'}
                </div>
                <div class="ybc_blog_import_option_updata">
                    <label for="blogdatawordpress">{l s='Data package' mod='ybc_blog'}</label>
                    <input id="blogdatawordpress" type="file" name="blogdatawordpress" />
                    <p>{l s='This data package can be exported using default built-in Wordpress export feature' mod='ybc_blog'}</p>
                </div>
                <div class="ybc_blog_import_option_clean">
                    <label for="importoverridewp"><input type="checkbox" name="importoverridewp" id="importoverridewp" value="1" /> {l s='Override existing if exist the same ID' mod='ybc_blog'}</label>
                </div>
                <div class="ybc_blog_import_option_button">
                    <div class="ybc_blog_import_submit">
                        <button type="submit" name="submitImportBlogWP" class="submitImportBlogWP"><i class="icon icon-compress"></i>{l s='IMPORT' mod='ybc_blog'}</button>
                    </div>
                </div>
            </div>
    </div>
    </div>
</form>
{if isset($errors) && $errors}
    <div class="ybc_blog_alert error" style="width: 100%;">
        <div class="bootstrap">
            <div class="module_error alert alert-danger">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <ul>
                    {foreach from=$errors item='error'}
                        <li>{$error|escape:'html':'UTF-8'}</li>
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
{/if}
<script type="text/javascript">
$(document).ready(function(){
    $('#data_import_posts_comments,#data_import_posts_categories').change(function(){
        if($('#data_import_posts_categories').is(':checked'))
        {
            $('.data-group.comments').show();
            $('.option_group.keepauthorid').show();
            if($('#data_import_posts_comments').is(':checked'))
            {
                $('.option_group.keepcommenter').show();
            }
            else
                $('.option_group.keepcommenter').hide();
        } 
        else
        {
            $('.data-group.comments').hide();
            $('.option_group.keepauthorid').hide();
            $('.option_group.keepcommenter').hide();
            $('#data_import_posts_comments').prop('checked',false);
        }
    });
    if($('#data_import_posts_categories').is(':checked'))
    {
        $('.data-group.comments').show();
        $('.option_group.keepauthorid').show();
        if($('#data_import_posts_comments').is(':checked'))
        {
            $('.option_group.keepcommenter').show();
        }
        else
            $('.option_group.keepcommenter').hide();
    } 
    else
    {
        $('.data-group.comments').hide();
        $('.option_group.keepauthorid').hide();
        $('.option_group.keepcommenter').hide();
        $('#data_import_posts_comments').prop('checked',false);
    }
});

</script>