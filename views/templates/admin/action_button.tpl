<span class="btn-group-action">
    <span class="btn-group">
        <a      class="btn btn-default{if isset($data_button.class) && $data_button.class} {$data_button.class}{/if}"
                {if isset($data_button.blank) && $data_button.blank}target="_blank"{/if}
                {if isset($data_button.data)}
                    {foreach $data_button.data as $data}
                        data-{$data.identifier}="{$data.value}"
                    {/foreach}
                {/if}
                {if isset($data_button.href) && $data_button.href}
                    href="{$data_button.href}"
                {elseif isset($data_button.orders) || isset($data_button.order)}
                    href="{$currentIndex}&token={$token}&{$data_button.action}{if isset($data_button.orders) && !empty($data_button.orders)}&orderBox[]={$data_button.orders}{/if}"
                {elseif isset($data_button.manifest) || isset($data_button)}
                    href="{$currentIndex}&token={$token}&print{$table}{if isset($data_button.manifest)}&id={$data_button.manifest}{/if}"
                {/if}
        >
            <i class="{$data_button.icon}"></i> {$data_button.title}
        </a>
    </span>
</span>