{*
*
*  MODIFIED BY MYPRESTA.EU FOR PRESTASHOP 1.7 PURPOSES !
*
*}
<div class="comments_note">
    {if ($averageTotal>0 && Configuration::get('PRODUCT_COMMENTS_LIST') == 1) || $PRODUCT_COMMENTS_LIST_ALL == true}
        <div class="star_content clearfix">
            {section name="i" start=0 loop=5 step=1}
                {if $averageTotal le $smarty.section.i.index}
                    <div class="star"></div>
                {else}
                    <div class="star star_on"></div>
                {/if}
            {/section}
        </div>
        <span>{l s='%s Review(s)' sprintf=[$nbComments]  mod='myprestacomments'}&nbsp</span>
    {/if}
</div>