<h3 class="my-4">Choose Your Web Hosting Plan</h3>
<p>Shared hosting is the easiest, most economical way to get your website connected to the Internet so you can start building it.</p>
<div id="pricing_plan_home" class="row mt-5 pt-2 home">

 {foreach item=product from=$hosting_products key=arrayIndex}
       
        <div class="col-md-4 mb-3">
            <div class="product-column">
                <div class="card-body p-lg-4 p-xl-5 text-center">
                    <h3 class="card-title pricing-card-title">
                        {$product.name}
                    </h3>
                    <p>{$product.tagline}</p>
                    <p class="product-description">{$product.description}</p>
                    <h4 class="my-4">Price: {$default_currency_prefix} 	{$product.monthly}</h4>
                    <a href="/whmcs/index.php?rp=/store/{$product.group_slug}/{$product.slug}" class="btn btn-block primary__btn">
                        <i class="fas fa-shopping-cart"></i>
                        {$LANG.ordernowbutton}
                    </a>
                </div>
            </div>
        </div>
    {/foreach}


   


</div>


