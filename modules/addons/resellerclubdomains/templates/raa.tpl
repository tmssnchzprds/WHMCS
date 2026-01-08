{extends file="main.tpl"}
{block name="content"}
    <div style="width:75%">
        <h2><strong>{$pageText['title']}</strong></h2>
        <p>
            {$pageText['description']}
            <br/>
            {if !empty($linkMoreDetails)}
                <a href="{$linkMoreDetails}" target="_blank">{$pageText['moreDetails']}</a>
            {/if}
        </p>
        <hr/>

        {if !is_null($success)}
            <div style="float:left;width:100%;">
                <div class="{if $success == true} successbox {else} errorbox {/if}">
                    <strong><span class="title">{$messageTitle}</span></strong>
                    <br>
                    {$messageBody}
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-md-6 col-sm-12">
                {$totalItems} {$pageText['labelDomainsFound']}
            </div>
            <div class="col-md-6 col-sm-12  text-right">
                <label for="cars">{$pageText['jumpToPage']}: </label>
                <select name="page" id="pageNumberSelect">
                    {for $i=1 to $numberOfPages}
                        <option value="{$i}" {if $i==$pageNo } selected {/if}>{$i}</option>
                    {/for}
                </select>
            </div>
        </div>

        <form method="post" action="{$moduleLinkRAA}&page={$pageNo}">
            <div class="tablebg">
                <table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
                    <thead>
                    <tr>
                        <th width="20"><input type="checkbox" id="checkallRAA"/></th>
                        <th>{$pageText['tableHeaderDomain']}</th>
                        <th>{$pageText['tableHeaderRegistrantEmail']}</th>
                        <th>{$pageText['tableHeaderStatus']}</th>
                        <th>{$pageText['tableHeaderRegisteredOn']}</th>
                        <th>{$pageText['tableHeaderVerificationDeadline']}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $domains as $domain}
                        <tr>
                            <td><input type="checkbox" name="selectedorders[]" value="{$domain['orders.orderid']}"
                                       class="checkall">
                            </td>
                            <td>{$domain['entity.description']}</td>
                            <td>{$domain['details']['registrantcontact']['emailaddr']}</td>
                            <td class="text-center">{$domain['entity.currentstatus']}</td>
                            <td class="text-center">{date('M j<\s\u\p>S</\s\u\p>, \'y',$domain['orders.creationtime'])}</td>
                            <td>{if !empty($domain['details']['raa_suspension_date'])} {date('M j<\s\u\p>S</\s\u\p>, \'y',$domain['details']['raa_suspension_date'])} {else} - {/if}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                {$pageText['withSelected']}:
                <input type="submit" name="massaccept" value="{$pageText['resendVerificationEmail']}"
                       class="btn btn-success"/>
            </div>
        </form>
        <div>
            <ul class="pagination">
                <li class="{if $pageNo == 1} disabled {/if}">
                    <a href="{$moduleLinkRAA}&page={$pageNo-1}">
                        « {$pageText['previousPage']}
                    </a>
                </li>
                <li>
                    <span>
                        <strong>{$pageNo}/{$numberOfPages}</strong>
                    </span>
                </li>
                <li class="{if $pageNo == $numberOfPages} disabled {/if}">
                    <a href="{$moduleLinkRAA}&page={$pageNo+1}">
                        <span>{$pageText['nextPage']} »</span>
                    </a>
                </li>
            </ul>
        </div>
        <script>
            $("#pageNumberSelect").on("change", function (e) {
                console.log(event, $(this).val());
                var url = new URL(window.location.href);
                url.searchParams.set('page', $(this).val());
                window.location.href = url.href;
            });
            $('#checkallRAA').click(function (event) {
                $(event.target).parents('.datatable').find('input').prop('checked', this.checked);
            });
        </script>
    </div>
{/block}
