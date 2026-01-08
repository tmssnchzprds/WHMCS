<p>
    <div class="row">
        <div class="col-sm-5 text-right"> <strong> Your Jetpack License Key </strong> </div>

        <div class="col-sm-7 text-left">{$license_key}
            <input type="text" id="licenseKey" hidden value="{$license_key}" >
            <button type="button" class="btn btn-default btn-xs copy-to-clipboard" data-clipboard-target="#licenseKey">
                <img src="{$WEB_ROOT}/assets/img/clippy.svg" alt="Copy to clipboard" width="15">
                {lang key='copy'}
            </button>
        </div>
    </div>
</p>

<p>
    {if $domain}
        Need to activate your license? Copy your license above and click <a href="http://{$domain}/wp-admin/admin.php?page=jetpack#/license/activation" target="_blank"> here </a> to do so.
    {/if}
</p>

<p>
    Need help getting started with Jetpack? Follow the instructions we've put together <a href="https://jetpack.com/support/install-jetpack-and-connect-your-new-plan/" target="_blank"> here. </a>.
</p>
