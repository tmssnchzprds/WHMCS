{include file="orderforms/digit-of/common.tpl"}

<script>
  var _localLang = {
    'addToCart': '{$LANG.orderForm.addToCart|escape}',
    'addedToCartRemove': '{$LANG.orderForm.addedToCartRemove|escape}'
  }
</script>

<div id="order-digit-of">

  <div class="row">
    <div class="cart-body">
      <div class="header-lined">
        <h1 class="font-size-36">{$LANG.cartdomainsconfig}</h1>
        <p>{$LANG.orderForm.reviewDomainAndAddons}</p>
      </div>
      {include file="orderforms/digit-of/sidebar-categories-collapsed.tpl"}

      <form method="post" action="{$smarty.server.PHP_SELF}?a=confdomains" id="frmConfigureDomains">
        <input type="hidden" name="update" value="true" />

        {if $errormessage}
          <div class="alert alert-danger" role="alert">
            <p>{$LANG.orderForm.correctErrors}:</p>
            <ul>
              {$errormessage}
            </ul>
          </div>
        {/if}

        {foreach $domains as $num => $domain}

          <div class="wdes-product-heading-options">
            <span>{$domain.domain}</span>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label>{$LANG.orderregperiod}</label>
                <br />
                {$domain.regperiod} {$LANG.orderyears}
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>{$LANG.hosting}</label>
                <br />
                {if $domain.hosting}<span style="color:#009900;">[{$LANG.cartdomainshashosting}]</span>
                {else}<a href="{$WEB_ROOT}/cart.php" style="color:#cc0000;">[{$LANG.cartdomainsnohosting}]</a>
                {/if}
              </div>
            </div>
            {if $domain.eppenabled}
              <div class="col-sm-12">
                <div class="form-group prepend-icon">
                  <input type="text" name="epp[{$num}]" id="inputEppcode{$num}" value="{$domain.eppvalue}" class="field"
                    placeholder="{$LANG.domaineppcode}" />
                  <label for="inputEppcode{$num}" class="field-icon">
                    <i class="fas fa-lock"></i>
                  </label>
                  <span class="field-help-text">
                    {$LANG.domaineppcodedesc}
                  </span>
                </div>
              </div>
            {/if}
          </div>

          {if $domain.dnsmanagement || $domain.emailforwarding || $domain.idprotection}
            <div class="row addon-products">

              {if $domain.dnsmanagement}
                <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}">
                  <div class="panel panel-default panel-addon{if $domain.dnsmanagementselected} panel-addon-selected{/if}">
                    <div class="panel-body">
                      <label>
                        <input type="checkbox" name="dnsmanagement[{$num}]" {if $domain.dnsmanagementselected} checked{/if} />
                        {$LANG.domaindnsmanagement}
                      </label>
                      <div class="panel-price">
                        {$domain.dnsmanagementprice} / {$domain.regperiod} {$LANG.orderyears}
                      </div>
                      <p>{$LANG.domainaddonsdnsmanagementinfo}</p>
                    </div>
                  </div>
                </div>
              {/if}

              {if $domain.idprotection}
                <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}">
                  <div class="panel panel-default panel-addon{if $domain.idprotectionselected} panel-addon-selected{/if}">
                    <div class="panel-body">
                      <label>
                        <input type="checkbox" name="idprotection[{$num}]" {if $domain.idprotectionselected} checked{/if} />
                        {$LANG.domainidprotection}
                      </label>
                      <div class="panel-price">
                        {$domain.idprotectionprice} / {$domain.regperiod} {$LANG.orderyears}
                      </div>
                      <p>{$LANG.domainaddonsidprotectioninfo}</p>
                    </div>
                  </div>
                </div>
              {/if}

              {if $domain.emailforwarding}
                <div class="col-sm-{math equation="12 / numAddons" numAddons=$domain.addonsCount}">
                  <div class="panel panel-default panel-addon{if $domain.emailforwardingselected} panel-addon-selected{/if}">
                    <div class="panel-body">
                      <label>
                        <input type="checkbox" name="emailforwarding[{$num}]" {if $domain.emailforwardingselected}
                          checked{/if} />
                        {$LANG.domainemailforwarding}
                      </label>
                      <div class="panel-price">
                        {$domain.emailforwardingprice} / {$domain.regperiod} {$LANG.orderyears}
                      </div>
                      <p>{$LANG.domainaddonsemailforwardinginfo}</p>
                    </div>
                  </div>
                </div>
              {/if}

            </div>
          {/if}
          {foreach from=$domain.fields key=domainfieldname item=domainfield}
            <div class="form-group row">
              <div class="col-sm-4 text-right">{$domainfieldname}:</div>
              <div class="col-sm-8">{$domainfield}</div>
            </div>
          {/foreach}

        {/foreach}

        {if $atleastonenohosting}

          <div class="wdes-product-heading-options">
            <span>{$LANG.domainnameservers}</span>
          </div>
          <p>{$LANG.cartnameserversdesc}</p>
          <div class="row wdes-server-field-container">
            <div class="col-sm-4">
              <div class="form-group">
                <label for="inputNs1">{$LANG.domainnameserver1}</label>
                <input type="text" class="form-control" id="inputNs1" name="domainns1" value="{$domainns1}" />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="inputNs2">{$LANG.domainnameserver2}</label>
                <input type="text" class="form-control" id="inputNs2" name="domainns2" value="{$domainns2}" />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="inputNs3">{$LANG.domainnameserver3}</label>
                <input type="text" class="form-control" id="inputNs3" name="domainns3" value="{$domainns3}" />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="inputNs1">{$LANG.domainnameserver4}</label>
                <input type="text" class="form-control" id="inputNs4" name="domainns4" value="{$domainns4}" />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="inputNs5">{$LANG.domainnameserver5}</label>
                <input type="text" class="form-control" id="inputNs5" name="domainns5" value="{$domainns5}" />
              </div>
            </div>
          </div>

        {/if}
        <br />
        <div class="text-center pt-4">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fad fa-shopping-bag"></i>
            {$LANG.continue}
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

{include file="orderforms/digit-of/recommendations-modal.tpl"}