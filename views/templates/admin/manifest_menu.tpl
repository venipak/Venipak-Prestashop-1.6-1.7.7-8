<div class="panel clearfix">
    {foreach from=$moduleMenu item=menuItem}
        <a class="btn btn-{if $menuItem.active}primary{else}default{/if}{if isset($menuItem.class)} {$menuItem.class}{/if}" href="{$menuItem.url|escape:'htmlall':'UTF-8'}">
                {if isset($menuItem.icon) && $menuItem.icon}
                        <i class="{$menuItem.icon}"></i>&nbsp;
                {/if}
                {$menuItem.label|escape:'htmlall':'UTF-8'}
        </a>
    {/foreach}
</div>