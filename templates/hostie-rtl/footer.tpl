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
                                <h6 class="widget-title">About Us</h6>
                                <p>
                                    As a leading web hosting and domain provider, we empower businesses with reliable online solutions. Offering seamless website hosting services and a vast domain portfolio, we ensure optimal performance and visibility for your digital presence. Our cutting-edge infrastructure guarantees speed, security, and 24/7 support, fostering a dependable online environment. Trust us to elevate your web experience, providing a solid foundation for your online success.    
                                </p>
                                {if $company_address}
                                <p>Address: {$company_address}</p>
                                {/if}
                                <div class="footer-social">
                                    {include file="$template/includes/social-accounts.tpl"}
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
                                <h6 class="widget-title">Payment Method</h6>
                                <p>
                                    Rest assured, our payment option prioritizes your security. Employing state-of-the-art encryption technology, we safeguard your sensitive information throughout the transaction process. Our robust security measures ensure that your financial data remains confidential, providing you with a worry-free and secure payment experience. Your trust is paramount to us, and we are committed to maintaining the highest standards of security to protect your valuable information.
                                </p>
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
