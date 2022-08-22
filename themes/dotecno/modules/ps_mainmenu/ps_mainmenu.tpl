{assign var=_counter value=0}
{function name="menu" nodes=[] depth=0 parent=null}
    {if $nodes|count}
      <ul class="top-menu" {if $depth == 0}id="top-menu"{/if} data-depth="{$depth}">
        {foreach from=$nodes item=node}
            <li class="{$node.type}{if $node.current} current {/if} {if $node.type == 'cms-category'} category {/if}" id="{$node.page_identifier}">
            {assign var=_counter value=$_counter+1}
              <a
                class="{if $depth >= 0}dropdown-item{/if}{if $depth === 1} dropdown-submenu{/if}"
                href="{$node.url}" data-depth="{$depth}"
                {if $node.open_in_new_window} target="_blank" {/if}
              >
                {if $node.children|count}
                  {* Cannot use page identifier as we can have the same page several times *}
                  {assign var=_expand_id value=10|mt_rand:100000}
                  <span class="float-xs-right hidden-md-up">
                    <span data-target="#top_sub_menu_{$_expand_id}" data-toggle="collapse" class="navbar-toggler collapse-icons">
                      <i class="material-icons add">&#xE313;</i>
                      <i class="material-icons remove">&#xE316;</i>
                    </span>
                  </span>
                {/if}
                {if $node.type == 'category' && $depth == 1 && !($node.children|count)}
                  <div class="hidden-sm-down miniatura-menu">
                    {if isset($node.image_urls) && $node.image_urls}
                      {foreach from=$node.image_urls item='thumb'}
                        <img src="{$thumb}" alt="{$node.label}" />
                      {/foreach}
                    {/if}
                  </div>
                {/if}
                {if $node.type == 'category' && $depth == 2}
                  <div class="hidden-sm-down miniatura-menu">
                    {if isset($node.image_urls) && $node.image_urls}
                      {foreach from=$node.image_urls item='thumb'}
                        <img src="{$thumb}" alt="{$node.label}" />
                      {/foreach}
                    {/if}
                  </div>
                {/if}
                {if $node.type == 'cms-page' && $depth == 1}
                  <div class="hidden-sm-down miniatura-menu">
                    {assign var=file value="{_PS_ROOT_DIR_}/img/cms/miniaturas/{$node.page_identifier}.jpg"}
                    {if file_exists($file)}
                      <img src="{$urls.img_ps_url}cms/miniaturas/{$node.page_identifier}.jpg" class="imgm" alt="{$node.label}"/>
                    {/if}
                  </div>
                {/if}
                {$node.label}
              </a>
              {if $node.children|count}
              <div {if $depth === 0} class="popover sub-menu js-sub-menu collapse{if $node.page_identifier == 'category-5' || $node.page_identifier == 'category-68' || $node.page_identifier == 'cms-category-2'} especial{/if}"{else} class="collapse"{/if} id="top_sub_menu_{$_expand_id}">
                {menu nodes=$node.children depth=$node.depth parent=$node}
              </div>
              {/if}
              {if !($node.children|count) && $depth == 1} {*Para que no quede vacío el tercer nivel y se vean los hijos de otra categoría hermana*}
                <div class="collapse" id="top_sub_menu_{$_expand_id}"></div>
              {/if}
            </li>
        {/foreach}
      </ul>
    {/if}
{/function}

<div class="menu js-top-menu position-static hidden-sm-down" id="_desktop_top_menu">
    {menu nodes=$menu.children}
    <div class="clearfix"></div>
</div>
