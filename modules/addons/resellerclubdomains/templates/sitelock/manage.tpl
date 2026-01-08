<script>
    $(document).ready(function () {

        const siteLockContent = '<div class="row mb-3">' +
            '<div class="col-3 col-md-2 text-center">' +
            '<i class="fas fa-lock fa-3x">&nbsp;</i>' +
            '</div>' +
            '<div class="col-9 col-md-10">' +
            '<strong>Domain Monitoring</strong>' +
            '<br>' +
            'Malware scanning & Blacklist monitoring.' +
            '<br>' +
            '<a class="btn btn-success" href="{$ssoUrl}" target="_blank">Manage</a>' +
            '</div>' +
            '</div>';


        {if $theme == 'six'}
        $("#tabAddons").append(siteLockContent);
        {else}
        $("#tabAddons > div.card > div.card-body > div.row:nth-of-type(1)").after('<hr>' + siteLockContent);
        {/if}
    });
</script>

