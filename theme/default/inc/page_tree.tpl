<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<div class="treeBox">
 <h3>{$lang.about_tree}</h3>
 <ul>
  <li<!-- {if $top_cur} --> class="cur"<!-- {/if} -->><a href="{$top.url}">{$top.page_name}</a></li>
  <!-- {foreach from=$page_list item=list} -->
  <li<!-- {if $list.cur} --> class="cur"<!-- {/if} -->><a href="{$list.url}">{$list.page_name}</a></li>
  <!--{/foreach}-->
 </ul>
</div>