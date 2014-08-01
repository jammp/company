<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<div class="treeBox">
 <h3>{$lang.article_tree}</h3>
 <ul>
  <!-- {foreach from=$article_category item=cate} -->
  <li<!-- {if $cate.cur} --> class="cur"<!-- {/if} -->><a href="{$cate.url}">{$cate.mark} {$cate.cat_name}</a></li>
  <!--{/foreach}-->
 </ul>
</div>