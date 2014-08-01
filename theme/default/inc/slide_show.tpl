<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<div id="slideShow">
 <div class="slides">
  <!-- {foreach from=$show_list name=show item=show} -->
  <div class="slide<!-- {if $smarty.foreach.show.first} --> current<!-- {/if} -->" id="slide-{$smarty.foreach.show.iteration}"> <a href="{$show.show_link}" target="_blank" ><img src="{$show.show_img}"/></a> </div>
  <!-- {/foreach} -->
 </div>
 <ul class="controlBase">
 </ul>
 <ul class="controls">
  <!-- {foreach from=$show_list name=show item=show} -->
  <li<!-- {if $smarty.foreach.show.first} --> class="active"<!-- {/if} -->><a href="#" rel="slide-{$smarty.foreach.show.iteration}"></a></li>
  <!-- {/foreach} -->
 </ul>
</div>