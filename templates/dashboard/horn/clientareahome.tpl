{include file="$template/includes/flashmessage.tpl"}
<div class="row">
    <div class="container-clientarea">
        <span class="container-clientarea-bg"></span>
        <div class="clientarea-new-header">
            <div class="row">
                <div class="col-md-8 col-sm-8">
                    <div class="col profile-photo">
                        <img class="gravatar br-50 img-profile-client-area" src="{$WEB_ROOT}/templates/{$template}/assets/img/gravatar.jpg" alt="Avatar" title="Avatar for {$loggedinuser.firstname}"/>
                        <a target="_blank" href="https://gravatar.com/">{$LANG.orderForm.edit}</a>
                    </div>
                    <h5 class="header-accout-details">
                    <span class="username">{$clientsdetails.firstname} {$clientsdetails.lastname} !</span>
                    <span class="adress">{$clientsdetails.address1}, {$clientsdetails.city} <b>{$clientsdetails.country}</b></span></h5>
                </div>
                
                <div class="col-md-4 col-sm-4">
                    <div class="header-features-icons">
                        <a href="clientarea.php?action=details" class="badge feat bg-puretheme" data-toggle="tooltip" data-placement="top" title="" data-original-title="{$LANG.orderForm.update}"><i class="ico-edit-3 f-16"></i> </a>
                        
                        <a href="clientarea.php?action=addfunds" class="badge feat bg-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="{$clientsstats.creditbalance}"><i class="ico-dollar-sign f-16"></i> </a>

                        <a href="clientarea.php?action=addcontact" class="badge feat bg-prussian-light" data-toggle="tooltip" data-placement="top" title="" data-original-title="{$LANG.clientareanavaddcontact}"><i class="ico-user-plus f-16"></i></a>

                        <a href="clientarea.php?action=quotes" class="badge feat bg-prata" data-toggle="tooltip" data-placement="top" title="" data-original-title="{$LANG.quotes} {$clientsstats.numquotes}"><i class="ico-twitch f-16"></i></a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<div class="area-discreted-info-user">
    <div class="row">
        <div class="col-md-3">
            <a href="clientarea.php?action=services">
                <div class="content-serv">
                    <i class="ico-settings bg-prussian-extralight p-5 br-50"></i>
                    <span class="title">{$clientsstats.productsnumactive} {$LANG.navservices}</span>
                    <span> {$LANG.clientareaproducts} </span>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="content-serv">
                {if $clientsstats.numdomains || $registerdomainenabled || $transferdomainenabled}
                <a href="clientarea.php?action=domains"><i class="ico-globe bg-prussian-extralight p-5 br-50"></i>
                    <span class="title">{$clientsstats.numactivedomains} {$LANG.navdomains} </span>
                    <span>{$LANG.domainRenewal.renewingDomains}</span></a>
                    {elseif $condlinks.affiliates && $clientsstats.isAffiliate}
                    <a href="affiliates.php">
                        <i class="icon-domains"></i>
                        <span class="title">{$clientsstats.numaffiliatesignups} {$LANG.affiliatessignups}</span>
                        <span>{$clientsstats.numaffiliatesignups}</span></a>
                        {/if}
                    </div>
                </div>
                <div class="col-md-3">
                    <a href="supporttickets.php">
                        <div class="content-serv">
                            <i class="ico-mail bg-prussian-extralight p-5 br-50"></i>
                            <span class="title">{$clientsstats.numactivetickets} {$LANG.navtickets} </span>
                            <span> {$LANG.ticketsyourhistory} </span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="clientarea.php?action=invoices">
                        <div class="content-serv">
                            <i class="ico-file-text bg-prussian-extralight p-5 br-50"></i>
                            <span class="title">{$clientsstats.numunpaidinvoices} {$LANG.navinvoices} </span>
                            <span> {$LANG.invoicesintro} </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            {foreach key=num item=invoice from=$invoices}
            <div class="col-md-6">
                <div class="invoices-elements-tringo">
                    <div class="invoice-elements-header">
                        <span data-toggle="tooltip" data-placement="left" title="{$LANG.invoicestitle}" class="number">{$invoice.invoicenum}</span>
                        <span data-toggle="tooltip" data-placement="top" title="{$LANG.invoicesdatecreated}" class="time">{$invoice.datecreated}</span>
                        <span data-toggle="tooltip" data-placement="top" title="{$LANG.invoicesdatedue}" class="time-end">{$invoice.datedue}</span>
                    </div>
                    <div class="invoice-elements-footer">
                        <span class="total" data-order="{$invoice.totalnum}"><span>{$LANG.ordertotalduetoday}</span> <b>{$invoice.total}</b></span>

                        <span class="invoice-elements-btns">
                            <a href="viewinvoice.php?id={$invoice.id}" class="invoice-pay-button status-{$invoice.statusClass}">{$invoice.status}</a>
                            <a href="viewinvoice.php?id={$invoice.id}" class="invoice-pay-button-now">pay now</a>
                        </span>
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
        {foreach from=$addons_html item=addon_html}
        <div>
            {$addon_html}
        </div>
        {/foreach}
        <div class="client-home-panels">
            <div class="row">
                <div class="col-sm-6">
                    {function name=outputHomePanels}
                    <div menuItemName="{$item->getName()}" class="panel panel-default panel-accent-{if $item->getClass()} {$item->getClass()}{/if}"{if $item->getAttribute('id')} id="{$item->getAttribute('id')}"{/if}>
                        
                        <div class="panel-heading">
                            <h3 class="panel-title">
                            {if $item->getExtra('btn-link') && $item->getExtra('btn-text')}
                            <a href="{$item->getExtra('btn-link')}" class="badge feat bg-puretheme mr-30 mt-5" data-toggle="tooltip" data-placement="left" title="" data-original-title="{$item->getExtra('btn-text')}"><i class="ico-link-2 f-16"></i> </a>
                            {/if}
                            {$item->getLabel()}
                            {if $item->hasBadge()}&nbsp;<span class="badge">{$item->getBadge()}</span>{/if}
                            </h3>
                        </div>
                        {if $item->hasBodyHtml()}
                        <div class="panel-body">
                            {$item->getBodyHtml()}
                        </div>
                        {/if}
                        {if $item->hasChildren()}
                        <div class="list-group{if $item->getChildrenAttribute('class')} {$item->getChildrenAttribute('class')}{/if}">
                            
                            {foreach $item->getChildren() as $childItem}
                            {if $childItem->getUri()}
                            
                            <a menuItemName="{$childItem->getName()}" href="{$childItem->getUri()}" class="list-group-item{if $childItem->getClass()} {$childItem->getClass()}{/if} {if $childItem->isCurrent()} active{/if}"{if $childItem->getAttribute('dataToggleTab')} data-toggle="tab"{/if}{if $childItem->getAttribute('target')} target="{$childItem->getAttribute('target')}"{/if} id="{$childItem->getId()}">
                                {if $childItem->hasIcon()}<i class="{$childItem->getIcon()}"></i>&nbsp;{/if}
                                {$childItem->getLabel()}
                                {if $childItem->hasBadge()}&nbsp;<span class="badge">{$childItem->getBadge()}</span>{/if}
                            </a>
                            {else}
                            <div menuItemName="{$childItem->getName()}" class="list-group-item{if $childItem->getClass()} {$childItem->getClass()}{/if}" id="{$childItem->getId()}">
                                {if $childItem->hasIcon()}<i class="{$childItem->getIcon()}"></i>&nbsp;{/if}
                                {$childItem->getLabel()}
                                {if $childItem->hasBadge()}&nbsp;<span class="badge">{$childItem->getBadge()}</span>{/if}
                            </div>
                            {/if}
                            {/foreach}
                        </div>
                        {/if}
                    </div>
                    {/function}

                    {foreach $panels as $item}
                        {if $item->getExtra('colspan')}
                            {outputHomePanels}
                            {assign "panels" $panels->removeChild($item->getName())}
                        {/if}
                    {/foreach}
                    {foreach $panels as $item}
                    {if $item@iteration is odd}
                    {outputHomePanels}
                    {/if}
                    {/foreach}
                </div>
                <div class="col-sm-6">
                    {foreach $panels as $item}
                    {if $item@iteration is even}
                    {outputHomePanels}
                    {/if}
                    {/foreach}
                </div>
            </div>
        </div>