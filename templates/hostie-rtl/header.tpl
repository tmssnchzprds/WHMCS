<!doctype html>
<html lang="en" dir="rtl">
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
                        <a href="{$WEB_ROOT}/supporttickets.php" class="live__chat"><img src="{$WEB_ROOT}/templates/{$template}/images/icon/forum.svg" alt="" class="icon">Support</a>
                        <div class="rts-ht__links">
                            <a href="{$WEB_ROOT}/clientarea.php" class="login__link"><img src="{$WEB_ROOT}/templates/{$template}/images/icon/person.svg" alt="" class="icon">{$LANG.login}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}
        <div class="bottombar">
            <div class="container">
                <div class="bottombar-inner">
                    <a class="navbar-brand" href="{$WEB_ROOT}/index.php">
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
                        <li class="nav-item d-xl-none">
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
