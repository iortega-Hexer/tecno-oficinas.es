{**
 * @author    Ana E
 *}
 <div class="container">
   {if $category.description}
     <div id="category-description" class="text-muted">{$category.description nofilter}</div>
   {/if}
   
   {assign var="textoSeoPie" value="{hook h="categoryField" name="Texto SEO (al pie)"}"}
   {if $textoSeoPie!=""}
     <div id="category-description-seo">{hook h="categoryField" name="Texto SEO (al pie)"}</div>
   {/if}
 </div>
