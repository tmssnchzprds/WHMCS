{include file="orderforms/standard_cart/common.tpl"}

<div id="order-standard_cart">
    <div class="row">
        <div class="cart-sidebar sidebar">
            {include file="orderforms/standard_cart/sidebar-categories.tpl"}
        </div>
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

            {include file="orderforms/standard_cart/sidebar-categories-collapsed.tpl"}

            <div class="products" id="products">
                <div class="row row-eq-height">
                    {foreach $products as $key => $product}
                        {$idPrefix = ($product.bid) ? ("bundle"|cat:$product.bid) : ("product"|cat:$product.pid)}
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="product clearfix" id="{$idPrefix}">
                            <header>
                                <div class="header-top">
                                    {if $product.stockControlEnabled}
                                        <span class="qty">
                                            {$product.qty} {$LANG.orderavailable}
                                        </span>
                                    {/if}
                                </div>
                                <div class="header-bottom">
                                    <span class="product-name" id="{$idPrefix}-name">{$product.name}</span>
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
                                            <p class="billing-cycle-name m-0">
                                                {if $product.pricing.minprice.cycle eq "monthly"}
                                                    {$LANG.orderpaymenttermmonthly}
                                                    <br>
                                                {elseif $product.pricing.minprice.cycle eq "quarterly"}
                                                    {$LANG.orderpaymenttermquarterly}
                                                    <br>
                                                {elseif $product.pricing.minprice.cycle eq "semiannually"}
                                                    {$LANG.orderpaymenttermsemiannually}
                                                    <br>
                                                {elseif $product.pricing.minprice.cycle eq "annually"}
                                                    {$LANG.orderpaymenttermannually}
                                                    <br>
                                                {elseif $product.pricing.minprice.cycle eq "biennially"}
                                                    {$LANG.orderpaymenttermbiennially}
                                                    <br>
                                                {elseif $product.pricing.minprice.cycle eq "triennially"}
                                                    {$LANG.orderpaymenttermtriennially}
                                                    <br>
                                                {/if}
                                            </p>
                                            {if $product.pricing.minprice.setupFee}
                                                <small>{$product.pricing.minprice.setupFee->toPrefixed()} {$LANG.ordersetupfee}</small>
                                            {/if}
                                            
                                        {/if}
                                    </div>
                                </div>
                            </header>
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
                                <a href="{$product.productUrl}" class="primary__btn btn-order-now" id="{$idPrefix}-order-button"{if $product.hasRecommendations} data-has-recommendations="1"{/if}>
                                    <i class="fas fa-shopping-cart"></i>
                                    {$LANG.ordernowbutton}
                                </a>
                            </footer>
                        </div>
                    </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>

{include file="orderforms/standard_cart/recommendations-modal.tpl"}
