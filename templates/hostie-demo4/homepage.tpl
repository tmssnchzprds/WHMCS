<div class="howCanWeHelpSec my-8">
    <div class="row action-icon-btns">
        <div class="col-6 col-md-4 col-lg mb-4">
            <a href="{routePath('announcement-index')}" class="card-accent-teal">
                <figure class="ico-container">
                    <i class="fal fa-bullhorn"></i>
                </figure>
                {lang key='announcementstitle'}
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg mb-4">
            <a href="serverstatus.php" class="card-accent-pomegranate">
                <figure class="ico-container">
                    <i class="fal fa-server"></i>
                </figure>
                {lang key='networkstatustitle'}
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg mb-4">
            <a href="{routePath('knowledgebase-index')}" class="card-accent-sun-flower">
                <figure class="ico-container">
                    <i class="fal fa-book"></i>
                </figure>
                {lang key='knowledgebasetitle'}
            </a>
        </div>
        <div class="col-6 col-md-4 offset-md-2 offset-lg-0 col-lg mb-4">
            <a href="{routePath('download-index')}" class="card-accent-asbestos">
                <figure class="ico-container">
                    <i class="fal fa-download"></i>
                </figure>
                {lang key='downloadstitle'}
            </a>
        </div>
        <div class="col-6 offset-3 offset-md-0 col-md-4 col-lg mb-4">
            <a href="submitticket.php" class="card-accent-green">
                <figure class="ico-container">
                    <i class="fal fa-life-ring"></i>
                </figure>
                {lang key='homepage.submitTicket'}
            </a>
        </div>
    </div>
</div>


{if $templatefile == 'homepage'}
    {if $registerdomainenabled || $transferdomainenabled}
        {include file="$template/includes/domain-search.tpl"}
    {/if}
{/if}

{if !empty($productGroups)}
    <div class="clientHomePanelsSec my-8">
        <h3 class="my-4">{lang key='clientHomePanels.productsAndServices'}</h3>
        <p>No matter your hosting requirements, our platform will fit your needs.<p>
        <div class="row card-columns mt-5 pt-2 home">
            {foreach $productGroups as $productGroup}

                <div class="col-md-4 mb-4">
                    <div class="product-column">
                        <div class="card-body p-lg-4 p-xl-5 text-center">
                            <h3 class="card-title pricing-card-title">
                                {$productGroup->name}
                            </h3>
                            <p>{$productGroup->tagline}</p>
                            <p>{$productGroup->shortDescription}</p>
                            <a href="{$productGroup->getRoutePath()}" class="btn btn-block primary__btn">
                                {lang key='browseProducts'}
                            </a>
                        </div>
                    </div>
                </div>

            {/foreach}
        </div>
    </div>
{/if}





<h3 class="my-4">{lang key='homepage.yourAccount'}</h3>

<div class="row gx-5 my-5 action-icon-btns">
    <div class="col-6 col-md-4 col-lg mb-4">
        <a href="clientarea.php" class="card-accent-midnight-blue">
            <figure class="ico-container">
                <i class="fal fa-home"></i>
            </figure>
            {lang key='homepage.yourAccount'}
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg mb-4">
        <a href="clientarea.php?action=services" class="card-accent-midnight-blue">
            <figure class="ico-container">
                <i class="far fa-cubes"></i>
            </figure>
            {lang key='homepage.manageServices'}
        </a>
    </div>
    {if $registerdomainenabled || $transferdomainenabled || $numberOfDomains}
        <div class="col-6 col-md-4 col-lg mb-4">
            <a href="clientarea.php?action=domains" class="card-accent-midnight-blue">
                <figure class="ico-container">
                    <i class="fal fa-globe"></i>
                </figure>
                {lang key='homepage.manageDomains'}
            </a>
        </div>
    {/if}
    <div class="col-6 col-md-4 offset-md-2 offset-lg-0 col-lg mb-4">
        <a href="supporttickets.php" class="card-accent-midnight-blue">
            <figure class="ico-container">
                <i class="fal fa-comments"></i>
            </figure>
            {lang key='homepage.supportRequests'}
        </a>
    </div>
    <div class="col-6 offset-3 offset-md-0 col-md-4 col-lg mb-4">
        <a href="clientarea.php?action=masspay&all=true" class="card-accent-midnight-blue">
            <figure class="ico-container">
                <i class="fal fa-credit-card"></i>
            </figure>
            {lang key='homepage.makeAPayment'}
        </a>
    </div>
</div>

{if $registerdomainenabled || $transferdomainenabled}
    <div class="clientHomePanelsSec my-8">
        <h3 class="my-4">{lang key='clientHomePanels.productsAndServices'}</h3>
        <div class="row">
            {if $registerdomainenabled}
                <div class="col-md-6 mb-4">
                    <div class="service-column product-column">
                        <div class="card-body p-lg-4 p-xl-5 text-center">
                            <h3 class="card-title pricing-card-title">
                                {lang key='orderregisterdomain'}
                            </h3>
                            <p>{lang key='secureYourDomain'}</p>
                            <a href="{$WEB_ROOT}/cart.php?a=add&domain=register" class="btn primary__btn">
                                {lang key='navdomainsearch'}
                            </a>
                        </div>
                    </div>
                </div>
            {/if}
            {if $transferdomainenabled}
                <div class="col-md-6 mb-4">
                    <div class="service-column product-column">
                        <div class="card-body p-lg-4 p-xl-5 text-center">
                            <h3 class="card-title pricing-card-title">
                                {lang key='transferYourDomain'}
                            </h3>
                            <p>{lang key='transferExtend'}</p>
                            <a href="{$WEB_ROOT}/cart.php?a=add&domain=transfer" class="btn primary__btn">
                                {lang key='transferYourDomain'}
                            </a>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/if}

{include file="$template/price-table-custom.tpl"} 
