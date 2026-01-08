                    </div>

                    </div>
                    {if !$inShoppingCart && $secondarySidebar->hasChildren()}
                        <div class="d-lg-none sidebar sidebar-secondary">
                            {include file="$template/includes/sidebar.tpl" sidebar=$secondarySidebar}
                        </div>
                    {/if}
                <div class="clearfix"></div>
            </div>
        </div>
    </section>

    <footer id="footer" class="footer">
       

            <div class="footer-top">
                <div class="container">
                    <div class="row">
                        <div class="footer-widget col-lg-3 col-md-6 col-12">
                            <div class="footer-widget-inner">
                               <div class="footer-logo"><img src="{$assetLogoPath}" alt=""></div>
                                <p>
                                   We’re on a mission make life easier for web developers & small businesses.
                                </p>
                                {if $company_address}
                                <p class:"address">Address: {$company_address}</p>
                                {/if}
                                <div class="separator"></div>
                                <h6 class="widget-title payment">Payment Method</h6>
                                <div class="payment__method">
                                    <ul>
                                        <li><img src="{$WEB_ROOT}/templates/{$template}/images/payment/visa.svg" alt=""></li>
                                        <li><img src="{$WEB_ROOT}/templates/{$template}/images/payment/master-card.svg" alt=""></li>
                                        <li><img src="{$WEB_ROOT}/templates/{$template}/images/payment/paypal.svg" alt=""></li>
                                        <li><img src="{$WEB_ROOT}/templates/{$template}/images/payment/american-express.svg" alt=""></li>
                                        <li><img src="{$WEB_ROOT}/templates/{$template}/images/payment/wise.svg" alt=""></li>
                                    </ul>
                                </div>
                               
                            </div>
                        </div>
                        <div class="footer-widget col-md-1 col-12"></div>
                        <div class="footer-widget col-lg-3 col-md-5 col-12">
                            <div class="footer-widget-inner">
                                <h6 class="widget-title">Products & Services</h6>
                                <ul class="footer-nav">
                                    {if !empty($products_groups)}
                                        {foreach $products_groups as $productGroup}
                                            <li class="nav-item">
                                                <a class="nav-link" href="{$WEB_ROOT}/index.php?rp=/store/{$productGroup->slug}" target="_blank">{$productGroup->name}</a>
                                            </li>
                                        {/foreach}
                                    {/if}
                                    {if $registerdomainenabled}
                                        <li class="nav-item">
                                            <a class="nav-link" href="{$WEB_ROOT}/cart.php?a=add&domain=register">
                                                {lang key='navdomainsearch'}
                                            </a>
                                        </li>
                                    {/if}
                                    {if $transferdomainenabled}
                                        <li class="nav-item">
                                            <a class="nav-link" href="{$WEB_ROOT}/cart.php?a=add&domain=transfer">
                                                {lang key='transferYourDomain'}
                                            </a>
                                        </li>
                                    {/if}
                                </ul>
                            </div>
                        </div>
                        <div class="footer-widget col-lg-2 col-md-7 col-12">
                            <div class="footer-widget-inner">
                                <h6 class="widget-title">Our Services</h6>
                                <ul class="footer-nav">
                                {include file="$template/includes/navbar.tpl" navbar=$primaryNavbar rightDrop=false}
                                    {if $acceptTOS}
                                        <li class="nav-item">
                                            <a class="nav-link" href="{$tosURL}" target="_blank">{lang key='ordertos'}</a>
                                        </li>
                                    {/if}
                                </ul>
                            </div>
                        </div>
                        <div class="footer-widget col-lg-3 col-md-5 col-12">

                            <div class="footer-widget-inner">
                                <h6 class="widget-title">Join Our Newsletter</h6>
                                <p>We'll send you news and offers.</p>
                                <form action="#" class="newsletter">
                                    <input type="email" class="home-one" name="email" placeholder="Enter mail" required="">
                                    <span class="icon"><i class="far fa-envelope-open"></i></span>
                                    <button type="submit"><i class="far fa-arrow-right"></i></button>
                                </form>
                                <h6 class="widget-title social">Social Media</h6>
                                 <div class="footer-social">
                                    {include file="$template/includes/social-accounts.tpl"}
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="container">
                    <div class="footer-bottom-content">
                        <p class="copyright mb-0">
                            {lang key="copyrightFooterNotice" year=$date_year company=$companyname}
                        </p>
                        <ul class="list-inline mb-0 text-center float-lg-right">
                            {if $languagechangeenabled && count($locales) > 1 || $currencies}
                                <li class="list-inline-item">
                                    <button type="button" class="btn primary__btn" data-toggle="modal" data-target="#modalChooseLanguage">
                                        <div class="d-inline-block align-middle">
                                            <div class="iti-flag {if $activeLocale.countryCode === 'GB'}us{else}{$activeLocale.countryCode|lower}{/if}"></div>
                                        </div>
                                        {$activeLocale.localisedName}
                                        /
                                        {$activeCurrency.prefix}
                                        {$activeCurrency.code}
                                    </button>
                                </li>
                            {/if}
                        </ul>
                    </div>
                </div>
            </div>
        
    </footer>

    <div id="fullpage-overlay" class="w-hidden">
        <div class="outer-wrapper">
            <div class="inner-wrapper">
                <img src="{$WEB_ROOT}/assets/img/overlay-spinner.svg" alt="">
                <br>
                <span class="msg"></span>
            </div>
        </div>
    </div>

    <div class="modal system-modal fade" id="modalAjax" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">{lang key='close'}</span>
                    </button>
                </div>
                <div class="modal-body">
                    {lang key='loading'}
                </div>
                <div class="modal-footer">
                    <div class="float-left loader">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        {lang key='loading'}
                    </div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {lang key='close'}
                    </button>
                    <button type="button" class="btn btn-primary modal-submit">
                        {lang key='submit'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form method="get" action="{$currentpagelinkback}">
        <div class="modal modal-localisation" id="modalChooseLanguage" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close text-light" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                        {if $languagechangeenabled && count($locales) > 1}
                            <h5 class="h5 pt-5 pb-3">{lang key='chooselanguage'}</h5>
                            <div class="row item-selector">
                                <input type="hidden" name="language" data-current="{$language}" value="{$language}" />
                                {foreach $locales as $locale}
                                    <div class="col-4">
                                        <a href="#" class="item{if $language == $locale.language} active{/if}" data-value="{$locale.language}">
                                            {$locale.localisedName}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                        {if !$loggedin && $currencies}
                            <p class="h5 pt-5 pb-3">{lang key='choosecurrency'}</p>
                            <div class="row item-selector">
                                <input type="hidden" name="currency" data-current="{$activeCurrency.id}" value="">
                                {foreach $currencies as $selectCurrency}
                                    <div class="col-4">
                                        <a href="#" class="item{if $activeCurrency.id == $selectCurrency.id} active{/if}" data-value="{$selectCurrency.id}">
                                            {$selectCurrency.prefix} {$selectCurrency.code}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-default">{lang key='apply'}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {if !$loggedin && $adminLoggedIn}
        <a href="{$WEB_ROOT}/logout.php?returntoadmin=1" class="btn btn-return-to-admin" data-toggle="tooltip" data-placement="bottom" title="{if $adminMasqueradingAsClient}{lang key='adminmasqueradingasclient'} {lang key='logoutandreturntoadminarea'}{else}{lang key='adminloggedin'} {lang key='returntoadminarea'}{/if}">
            <i class="fas fa-redo-alt"></i>
            <span class="d-none d-md-inline-block">{lang key="admin.returnToAdmin"}</span>
        </a>
    {/if}

    {include file="$template/includes/generate-password.tpl"}

    {$footeroutput}

</body>
</html>
