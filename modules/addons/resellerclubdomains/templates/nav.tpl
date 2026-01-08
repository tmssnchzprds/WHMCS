{block name="nav"}
    {if !empty($moduleHeader)}
        <div class="sidebar-header">
            {$moduleHeader}
        </div>
    {/if}
    <div class="sidebar">
        <ul class="menu">
            <li>
                <a href="{$moduleLink}">Home</a>
            </li>
            <li>
                <a href="{$moduleLink}&action=raa">Pending Verification Domains</a>
            </li>
        </ul>
    </div>
{/block}
