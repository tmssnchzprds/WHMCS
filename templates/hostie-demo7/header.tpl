<!doctype html>
<html lang="en">
<head>
    <meta charset="{$charset}" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{if $kbarticle.title}{$kbarticle.title} - {/if}{$pagetitle} - {$companyname}</title>
    {include file="$template/includes/head.tpl"}
    {$headoutput}
</head>
<body class="{if $templatefile != 'homepage'}primary-bg-color{/if}" data-phone-cc-input="{$phoneNumberInputStyle}" data-pageTemplate="{$templatefile}">

    {$headeroutput}

    <header id="header" class="header {if $templatefile == 'homepage'} transparent-header {/if}">
        {if $loggedin}
            <div class="topbar">
                <div class="rts-ht__wrapper">
                    <div class="container">
                        <div class="d-flex">
                            <div class="mr-auto">
                                <button type="button" class="btn" data-toggle="popover" id="accountNotifications" data-placement="bottom">
                                    <i class="far fa-flag"></i>
                                    {if count($clientAlerts) > 0}
                                        {count($clientAlerts)}
                                        <span class="d-none d-sm-inline">{lang key='notifications'}</span>
                                    {else}
                                        <span class="d-sm-none">0</span>
                                        <span class="d-none d-sm-inline">{lang key='nonotifications'}</span>
                                    {/if}
                                </button>
                                <div id="accountNotificationsContent" class="w-hidden">
                                    <ul class="client-alerts">
                                    {foreach $clientAlerts as $alert}
                                        <li>
                                            <a href="{$alert->getLink()}">
                                                <i class="fas fa-fw fa-{if $alert->getSeverity() == 'danger'}exclamation-circle{elseif $alert->getSeverity() == 'warning'}exclamation-triangle{elseif $alert->getSeverity() == 'info'}info-circle{else}check-circle{/if}"></i>
                                                <div class="message">{$alert->getMessage()}</div>
                                            </a>
                                        </li>
                                    {foreachelse}
                                        <li class="none">
                                            {lang key='notificationsnone'}
                                        </li>
                                    {/foreach}
                                    </ul>
                                </div>
                            </div>

                            <div class="ml-auto">
                                <div class="input-group active-client" role="group">
                                    <div class="input-group-prepend d-none d-md-inline">
                                        <span class="input-group-text">{lang key='loggedInAs'}:</span>
                                    </div>
                                    <div class="btn-group">
                                        <a href="{$WEB_ROOT}/clientarea.php?action=details" class="btn btn-active-client">
                                            <span>
                                                {if $client.companyname}
                                                    {$client.companyname}
                                                {else}
                                                    {$client.fullName}
                                                {/if}
                                            </span>
                                        </a>
                                        <a href="{routePath('user-accounts')}" class="btn" data-toggle="tooltip" data-placement="bottom" title="Switch Account">
                                            <i class="fad fa-random"></i>
                                        </a>
                                        {if $adminMasqueradingAsClient || $adminLoggedIn}
                                            <a href="{$WEB_ROOT}/logout.php?returntoadmin=1" class="btn btn-return-to-admin" data-toggle="tooltip" data-placement="bottom" title="{if $adminMasqueradingAsClient}{lang key='adminmasqueradingasclient'} {lang key='logoutandreturntoadminarea'}{else}{lang key='adminloggedin'} {lang key='returntoadminarea'}{/if}">
                                                <i class="fas fa-redo-alt"></i>
                                                <span class="d-none d-md-inline-block">{lang key="admin.returnToAdmin"}</span>
                                            </a>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        {if !$loggedin}
        <div class="topbar navbar navbar-light">
            <div class="topbar-inner">
                <div class="container">
                    <div class="rts-ht__wrapper">
                        <a href="{$WEB_ROOT}/supporttickets.php" class="live__chat">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.25 11.25H2.5V3.125C2.5 2.75 2.75 2.5 3.125 2.5H11.25V1.25H3.125C2.0625 1.25 1.25 2.0625 1.25 3.125V11.25ZM3.75 5C3.75 4.25 4.25 3.75 5 3.75H17.5C18.25 3.75 18.75 4.25 18.75 5V13.75C18.75 14.5 18.25 15 17.5 15H13.625L11 18.75L10 18L13 13.75H17.5V5H5V13.75H10V15H5C4.25 15 3.75 14.5 3.75 13.75V5Z" fill="white"/>
                            </svg>
                            Support
                        </a>
                        <div class="rts-ht__links">
                            <a href="{$WEB_ROOT}/clientarea.php" class="login__link">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_146_46069)">
                                <path d="M6 7.99997C6.79113 7.99997 7.56449 7.76538 8.22228 7.32585C8.88008 6.88632 9.39277 6.26161 9.69552 5.53071C9.99827 4.7998 10.0775 3.99553 9.92314 3.21961C9.7688 2.44369 9.38784 1.73095 8.82843 1.17154C8.26902 0.612134 7.55629 0.231171 6.78036 0.0768302C6.00444 -0.0775107 5.20017 0.00170263 4.46927 0.304453C3.73836 0.607204 3.11365 1.11989 2.67412 1.77769C2.2346 2.43549 2 3.20885 2 3.99997C2.00106 5.06051 2.42283 6.07731 3.17274 6.82723C3.92266 7.57715 4.93946 7.99891 6 7.99997ZM6 1.3333C6.52742 1.3333 7.04299 1.4897 7.48152 1.78272C7.92005 2.07574 8.26185 2.49221 8.46368 2.97948C8.66551 3.46675 8.71832 4.00293 8.61543 4.52021C8.51253 5.0375 8.25856 5.51265 7.88562 5.88559C7.51268 6.25853 7.03752 6.51251 6.52024 6.6154C6.00296 6.71829 5.46678 6.66548 4.97951 6.46365C4.49224 6.26182 4.07576 5.92002 3.78275 5.48149C3.48973 5.04296 3.33333 4.52739 3.33333 3.99997C3.33333 3.29273 3.61428 2.61445 4.11438 2.11435C4.61448 1.61426 5.29276 1.3333 6 1.3333Z" fill="white"/>
                                <path d="M6 9.33374C4.40924 9.3355 2.88414 9.96821 1.75931 11.093C0.634472 12.2179 0.00176457 13.743 0 15.3337C0 15.5106 0.0702379 15.6801 0.195262 15.8051C0.320286 15.9302 0.489856 16.0004 0.666667 16.0004C0.843478 16.0004 1.01305 15.9302 1.13807 15.8051C1.2631 15.6801 1.33333 15.5106 1.33333 15.3337C1.33333 14.0961 1.825 12.9091 2.70017 12.0339C3.57534 11.1587 4.76232 10.6671 6 10.6671C7.23768 10.6671 8.42466 11.1587 9.29983 12.0339C10.175 12.9091 10.6667 14.0961 10.6667 15.3337C10.6667 15.5106 10.7369 15.6801 10.8619 15.8051C10.987 15.9302 11.1565 16.0004 11.3333 16.0004C11.5101 16.0004 11.6797 15.9302 11.8047 15.8051C11.9298 15.6801 12 15.5106 12 15.3337C11.9982 13.743 11.3655 12.2179 10.2407 11.093C9.11586 9.96821 7.59076 9.3355 6 9.33374Z" fill="white"/>
                                </g>
                                <defs>
                                <clipPath id="clip0_146_46069">
                                <rect width="16" height="16" fill="white"/>
                                </clipPath>
                                </defs>
                            </svg>
                            {$LANG.login}
                        </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}
        <div class="bottombar">
            <div class="container">
                <div class="bottombar-inner">
                    <a class="navbar-brand mr-3" href="{$WEB_ROOT}/index.php">
                        {if $assetLogoPath}
                            <img src="{$assetLogoPath}" alt="{$companyname}" class="logo-img">
                        {else}
                            {$companyname}
                        {/if}
                    </a>
                    <div class="navbar navbar-expand-xl main-navbar-wrapper">
                        <div class="collapse navbar-collapse" id="mainNavbar">
                            <div class="bottom-navs-wrapper">
                                <ul id="nav" class="navbar-nav primaryNavbar">
                                    {include file="$template/includes/navbar.tpl" navbar=$primaryNavbar}
                                </ul>
                                <ul class="navbar-nav navbar-nav-right">
                                    {include file="$template/includes/navbar.tpl" navbar=$secondaryNavbar rightDrop=true}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <ul class="navbar-nav toolbar">
                        <li class="nav-item ml-3 px-0">
                            <a class="btn nav-link cart-btn" href="{$WEB_ROOT}/cart.php?a=view">
                                <i class="far fa-shopping-cart fa-fw"></i>
                                <span id="cartItemCount" class="badge badge-info">{$cartitemcount}</span>
                                <span class="sr-only">{lang key="carttitle"}</span>
                            </a>
                        </li>
                        <li class="nav-item ml-3 d-xl-none">
                            <button class="btn nav-link" type="button" data-toggle="collapse" data-target="#mainNavbar">
                                <span class="fas fa-bars fa-fw"></span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    {include file="$template/includes/network-issues-notifications.tpl"}

    {include file="$template/includes/validateuser.tpl"}
    {include file="$template/includes/verifyemail.tpl"}

    {if $templatefile == 'homepage'}
        {include file="$template/includes/site-hero.tpl"}
    {/if}

    <section id="main-body">
        <div class="{if !$skipMainBodyContainer}container{/if}">
            <div class="row">

            {if !$inShoppingCart && ($primarySidebar->hasChildren() || $secondarySidebar->hasChildren())}
                <div class="col-lg-4 col-xl-3">
                    <div class="sidebar">
                        {include file="$template/includes/sidebar.tpl" sidebar=$primarySidebar}
                    </div>
                    {if !$inShoppingCart && $secondarySidebar->hasChildren()}
                        <div class="d-none d-lg-block sidebar">
                            {include file="$template/includes/sidebar.tpl" sidebar=$secondarySidebar}
                        </div>
                    {/if}
                </div>
            {/if}
            <div class="{if !$inShoppingCart && ($primarySidebar->hasChildren() || $secondarySidebar->hasChildren())}col-lg-8 col-xl-9{else}col-12{/if} primary-content">
