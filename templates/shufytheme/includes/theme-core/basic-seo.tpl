<link rel="shortcut icon" type="image/x-icon" href="{if $coodivsettings.seositefavicon == null}{$WEB_ROOT}/templates/{$template}/assets/img/favicon.ico{else}{$coodivsettings.seositefavicon}{/if}">
<meta name="description" content="{$coodivsettings.seositedescription}">
<meta name="owner" content="{$coodivsettings.seoorganizationname}">
<meta name="copyright" content="{if $coodivsettings.seositename == null}{$companyname}{else}{$coodivsettings.seositename}{/if}">
<meta name="category" content="{$coodivsettings.seocontacttype}">
<meta name="classification" content="{$coodivsettings.seowebsitetype}">
<meta name="locale" content="{$LANG.locale}" />
<meta property="og:locale" content="{$LANG.locale}" />
<meta property="og:site_name" content="{if $coodivsettings.seositename == null}{$companyname}{else}{$coodivsettings.seositename}{/if}" />
<meta property="og:type" content="{$coodivsettings.seowebsitetype}" />
<meta property="og:title" content="{if $coodivsettings.seositename == null}{$companyname}{else}{$coodivsettings.seositename}{/if}" />
<meta property="og:description" content="{$coodivsettings.seositedescription}" />
<meta property="og:url" content="{$systemsslurl}" />
<meta property="og:image" content="{if $coodivsettings.seoopengraph == null}https://coodiv.net/blog/wp-content/uploads/2022/05/placeholder.jpg{else}{$coodivsettings.seoopengraph}{/if}" />
<meta property="og:image:secure_url" content="{if $coodivsettings.seoopengraph == null}https://coodiv.net/blog/wp-content/uploads/2022/05/placeholder.jpg{else}{$coodivsettings.seoopengraph}{/if}" />
<meta name="twitter:image" content="{if $coodivsettings.seoopengraph == null}https://coodiv.net/blog/wp-content/uploads/2022/05/placeholder.jpg{else}{$coodivsettings.seoopengraph}{/if}" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="{$coodivsettings.seotwitterusername}" />
<meta name="twitter:title" content="{if $coodivsettings.seositename == null}{$companyname}{else}{$coodivsettings.seositename}{/if}" />
<meta name="twitter:description" content="{$coodivsettings.seositedescription}" />
<meta name="og:phone_number" content="{$coodivsettings.seoorganizationphonenumber}"/>
<meta name="phone_number" content="{$coodivsettings.seoorganizationphonenumber}"/>