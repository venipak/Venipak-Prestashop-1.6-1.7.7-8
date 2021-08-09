<div class="tab-content mjvp-admin-order">
  <div class="panel">
    <div class="panel-heading">
      <i class="icon-truck"></i> {$block_title}
    </div>
    <div class="row">
      <div class="col-lg-12">
        {if $label_error && $label_status eq 'error'}
          <div class="alert alert-danger">{$label_error}</div>
        {elseif $label_status eq 'new'}
          <div class="alert alert-warning">{l s='Order not registered.' mod='mijoravenipak'}</div>
        {elseif $label_status eq 'registered'}
          <div class="alert alert-success">{l s='Order registered.' mod='mijoravenipak'}</div>
        {else}
          <div class="alert alert-danger">{l s='An unspecified error occurred.' mod='mijoravenipak'}</div>
        {/if}
      </div>
      {if !empty($label_tracking_numbers)}
        <div class="col-lg-12">
            <span>{l s='Tracking numbers' mod='mijoravenipak'}:</span>
            <ul>
                {foreach from=$label_tracking_numbers item=tracking_number}
                    <li>{$tracking_number}</li>
                {/foreach}
            </ul>
        </div>
      {/if}
    </div>
  </div>
</div>