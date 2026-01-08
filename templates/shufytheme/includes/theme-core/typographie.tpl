<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<!-- Theme google fonts  -->
{if ($language == 'arabic' || $language == 'hebrew' || $language == 'farsi')}
{if $coodivtypographiesettings.themesettingtyponamertl=='noto'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/noto.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponamertl=='rubik'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/rubik-rtl.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponamertl=='tajawal'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/tajawal.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponamertl=='kufam'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/kufam.css?v=1.2.5">
{else}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/cairo.css?v=1.2.5">
{/if}
{else}
{if $coodivtypographiesettings.themesettingtyponame=='lato'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/lato.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='abhaya'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/abhaya.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='merriweather'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/merriweather.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='alegreya'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/alegreya.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='montserrat'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/montserrat.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='aleo'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/aleo.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='muli'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/muli.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='arapey'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/arapey.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='nunito'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/nunito.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='asap'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/asap.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='assistant'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/assistant.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='open-sans'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/open-sans.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='barlow'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/barlow.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='oswald'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/oswald.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='bitter'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/bitter.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='poppins'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/poppins.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='brawler'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/brawler.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='roboto'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/roboto.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='caladea'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/caladea.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='rokkitt'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/rokkitt.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='carme'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/carme.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='rubik'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/rubik.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='encode'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/encode.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='enriqueta'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/enriqueta.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='source-sans-pro'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/source-sans-pro.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='frank-ruhl-libre'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/frank-ruhl-libre.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='spectral'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/spectral.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='work-sans'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/work-sans.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='gelasio'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/gelasio.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='headland-one'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/headland-one.css?v=1.2.5">
{else if $coodivtypographiesettings.themesettingtyponame=='ubuntu'}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/ubuntu.css?v=1.2.5">
{else}
<link rel="stylesheet" media="all" href="{$WEB_ROOT}/templates/{$template}/assets/google-fonts-caller/inter-tight.css?v=1.2.5">
{/if}
{/if}