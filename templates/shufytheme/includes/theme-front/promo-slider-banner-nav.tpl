{assign var="items" value=$CoodivMarketConnectServices}
{if $items|count > 1}
	<div class="homepage__promo__slider__nav__wrapper d-lg-block d-none items-{$items|count}">	
		{foreach from=$items item=item key=k name=foo}
			<div class="homepage__promo__slider__nav__box text-center coodiv-hover-y">
				<div class="homepage__promo__slider__nav__box__icon__wrapper">
				{include file="$template/includes/theme-core/promo-slide-icons.tpl" illustration="{$item.name}"}  
				</div>
				<h6 class="coodiv-text-9 font-weight-700 mt-2">{$item.name}</h6>				
				<p class="coodiv-text-11 font-weight-300 mt-1 slider__text__wrapper">
				{if $item.name == 'threesixtymonitoring'}
					{lang key='store.threesixtymonitoring.serverHeading.tagline'}
				{else if $item.name == 'xovinow'}
					{assign var="title" value=$LANG.store.xoviNow.tagline}
					{$title|replace:':lineBreak':''}
				{else if $item.name == 'symantec'}
					{lang key='store.ssl.landingPage.tagline1'}
				{else if $item.name == 'nordvpn'}		
					{assign var="title" value=$LANG.store.nordvpn.hero.title}
					{$title|replace:':tagBreak':''|replace:':tagOpen':'<strong>'|replace:':tagClose':'</strong>'}
				{else if $item.name == 'spamexperts'}
					{lang key='store.emailServices.tagline'}
				{else if $item.name == 'ox'}
					{lang key='store.ox.promoHeading'}
				{else if $item.name == 'codeguard'}
					{lang key='store.codeGuard.headline'}
				{else if $item.name == 'marketgoo'}
					{lang key='store.marketgoo.headline'}
				{else if $item.name == 'sitebuilder'}
					{lang key='store.siteBuilder.introHeadline'}
				{else if $item.name == 'weebly'}
					{lang key='store.websiteBuilder.tagline'}
				{else if $item.name == 'sitelock'}
					{lang key='store.sitelock.tagline'}
				{/if}
				</p>
			</div>
		{/foreach}
	</div>
{/if}