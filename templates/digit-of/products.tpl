{include file="orderforms/digit-of/common.tpl"}

<div id="order-digit-of">
  <div class="row">
    <div class="cart-body">

      <div class="header-lined">
        <h1 class="font-size-36">
          {if $productGroup.headline}
            {$productGroup.headline}
          {else}
            {$productGroup.name}
          {/if}
        </h1>
        {if $productGroup.tagline}
          <p>{$productGroup.tagline}</p>
        {/if}
      </div>
      {if $errormessage}
        <div class="alert alert-danger">
          {$errormessage}
        </div>
      {elseif !$productGroup}
        <div class="alert alert-info">
          {lang key='orderForm.selectCategory'}
        </div>
      {/if}

      {include file="orderforms/digit-of/sidebar-categories-collapsed.tpl"}

      <div class="products" id="products">
        <div class="row row-eq-height">
          {foreach $products as $key => $product}
            {$idPrefix = ($product.bid) ? ("bundle"|cat:$product.bid) : ("product"|cat:$product.pid)}
            <div class="col-md-4">
              <div class="product clearfix" id="{$idPrefix}">
                <header>
                  {if $product.isFeatured}
                    <div class="txt-container">{$LANG.featuredProduct}</div>
                  {/if}
                  <i class="fad fa-hdd"></i>
                  <span id="{$idPrefix}-name">{$product.name}</span>
                  {if $product.stockControlEnabled}
                    <span class="qty">
                      {$product.qty} {$LANG.orderavailable}
                    </span>
                  {/if}
                </header>
                <div class="product-pricing" id="{$idPrefix}-price">
                  {if $product.bid}
                    {$LANG.bundledeal}<br />
                    {if $product.displayprice}
                      <span class="price">{$product.displayprice}</span>
                    {/if}
                  {else}
                    {if $product.pricing.hasconfigoptions}
                      {$LANG.startingfrom}
                      <br />
                    {/if}
                    <span class="price">{$product.pricing.minprice.price}</span>
                    {if $product.pricing.minprice.cycle eq "monthly"}
                      {$LANG.orderpaymenttermmonthly}
                    {elseif $product.pricing.minprice.cycle eq "quarterly"}
                      {$LANG.orderpaymenttermquarterly}
                    {elseif $product.pricing.minprice.cycle eq "semiannually"}
                      {$LANG.orderpaymenttermsemiannually}
                    {elseif $product.pricing.minprice.cycle eq "annually"}
                      {$LANG.orderpaymenttermannually}
                    {elseif $product.pricing.minprice.cycle eq "biennially"}
                      {$LANG.orderpaymenttermbiennially}
                    {elseif $product.pricing.minprice.cycle eq "triennially"}
                      {$LANG.orderpaymenttermtriennially}
                    {/if}
                    {if $product.pricing.minprice.setupFee}
                      <small>{$product.pricing.minprice.setupFee->toPrefixed()} {$LANG.ordersetupfee}</small>
                    {/if}
                  {/if}
                </div>
                <div class="product-desc">
                  {if $product.featuresdesc}
                    <p id="{$idPrefix}-description">
                      {$product.featuresdesc}
                    </p>
                  {/if}
                  <ul>
                    {foreach $product.features as $feature => $value}
                      <li id="{$idPrefix}-feature{$value@iteration}">
                        <span class="feature-value">{$value}</span>
                        {$feature}
                      </li>
                    {/foreach}
                  </ul>
                </div>
                <footer>
                  <a href="{$product.productUrl}" class="wdes-digit-of-order-btn btn btn-success btn-sm btn-order-now"
                    id="{$idPrefix}-order-button" {if $product.hasRecommendations} data-has-recommendations="1"{/if}>
                    <i class="fad fa-shopping-cart"></i>
                    {$LANG.ordernowbutton}
                  </a>
                </footer>
              </div>
            </div>
            {if $product@iteration % 3 == 0}
            </div>
            <div class="row row-eq-height">
            {/if}
          {/foreach}
        </div>
      </div>
    </div>
  </div>
</div>

{include file="orderforms/digit-of/recommendations-modal.tpl"}