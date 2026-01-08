<link rel="stylesheet" type="text/css"
  href="{$WEB_ROOT}/templates/orderforms/digit-of/css/all.min.css?v={$versionHash}" />
{if $templatefile eq 'configureproductdomain'}
  <link rel="stylesheet" type="text/css" href="{assetPath file='select2.min.css'}?v={$versionHash}" />
{/if}
<link rel="stylesheet" type="text/css" href="{assetPath file='core.css'}?v={$versionHash}" />
<link rel="stylesheet" type="text/css" href="{assetPath file='form-theme-dark.css'}?v={$versionHash}" />
{if $language == 'arabic' || $language == 'hebrew' || $language == 'farsi'}
  <link rel="stylesheet" type="text/css" href="{assetPath file='custom-rtl.css'}?v={$versionHash}" />
{/if}
<script type="text/javascript" src="{$WEB_ROOT}/templates/orderforms/digit-of/js/scripts.min.js?v={$versionHash}">
</script>
{if $templatefile eq 'configureproductdomain'}
  <script type="text/javascript" src="{assetPath file='select2.min.js'}?v={$versionHash}"></script>
  <script>
    $(document).ready(function() {
      $('.option select').select2();
    });
  </script>
{/if}