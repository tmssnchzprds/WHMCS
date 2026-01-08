{if $template == "shufytheme-child"}{$template = "shufytheme"}{/if}
{include file="$template/includes/theme-core/basic-seo.tpl"}
{include file="$template/includes/theme-core/typographie.tpl"}
<link rel="stylesheet" href="{$WEB_ROOT}/templates/{$template}/assets/css/theme.min.css?v=1.2.5">
{if ($language == 'arabic' || $language == 'hebrew' || $language == 'farsi')}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/css/bootstrap.rtl.min.css?v=1.2.5">
{/if}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/css/app.min.css?v=1.2.5">
{if ($language == 'arabic' || $language == 'hebrew' || $language == 'farsi')}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/css/app.rtl.min.css?v=1.2.5">
{/if}
{if $coodivcolorsettings.dafaultthemecolor=='theme-style-one'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/modules/addons/shufyTheme/css-values/default-style.css">
{else if $coodivcolorsettings.dafaultthemecolor=='theme-style-two'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/modules/addons/shufyTheme/css-values/green-style.css">
{else if $coodivcolorsettings.dafaultthemecolor=='theme-style-three'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/modules/addons/shufyTheme/css-values/purple-style.css">
{else if $coodivcolorsettings.dafaultthemecolor=='theme-style-four'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/modules/addons/shufyTheme/css-values/red-style.css">
{/if}
{if $coodivtypographiesettings.id == '1'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/modules/addons/shufyTheme/css-values/typographie.css">
{/if}
{assetExists file="custom.css"}
<link href="{$__assetPath__}" rel="stylesheet">
{/assetExists}
{if $coodivsettings.customcsscode != null}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/modules/addons/shufyTheme/css-values/custom.css">
{/if}
{if $coodivhomepagesettings.id == '1'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/modules/addons/shufyTheme/css-values/homepage-plans.css">
{/if}
<script>
	var csrfToken = '{$token}',
		markdownGuide = '{lang|addslashes key="markdown.title"}',
		locale = '{if !empty($mdeLocale)}{$mdeLocale}{else}en{/if}',
		saved = '{lang|addslashes key="markdown.saved"}',
		saving = '{lang|addslashes key="markdown.saving"}',
		whmcsThemeName = "{$template}",
		whmcsBaseUrl = "{\WHMCS\Utility\Environment\WebHelper::getBaseUrl()}";
		{if $captcha}{$captcha->getPageJs()}{/if}
</script>
<script src="{$WEB_ROOT}/templates/{$template}/assets/js/scripts.min.js?v=1.2.5"></script>
{if $templatefile == "viewticket" && !$loggedin}
  <meta name="robots" content="noindex" />
{/if}