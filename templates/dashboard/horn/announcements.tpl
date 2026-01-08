{if $announcementsFbRecommend}
    <script>
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/{$LANG.locale}/all.js#xfbml=1";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
{/if}

<div class="row">
{foreach from=$announcements item=announcement}
        <div class="col-md-4">
            <div class="announcement-single">

                <a href="{routePath('announcement-view', $announcement.id, $announcement.urlfriendlytitle)}" class="title">
                    {$announcement.title} <span class="badge feat bg-puretheme mr-20 mt-10" data-toggle="tooltip" data-placement="left" title="" data-original-title="{$carbon->createFromTimestamp($announcement.timestamp)->format('jS M Y')}">
                    <i class="ico-calendar f-18"></i> </span>
                </a>

                {if $announcement.text|strip_tags|strlen < 350}
                    <p>{$announcement.text}</p>
                {else}
                    <p>{$announcement.summary}</p>
                {/if}

                {if $announcementsFbRecommend}
                    <div class="fb-like hidden-sm hidden-xs" data-layout="standard" data-href="{fqdnRoutePath('announcement-view', $announcement.id, $announcement.urlfriendlytitle)}" data-send="true" data-width="450" data-show-faces="true" data-action="recommend"></div>
                    <div class="fb-like hidden-lg hidden-md" data-layout="button_count" data-href="{fqdnRoutePath('announcement-view', $announcement.id, $announcement.urlfriendlytitle)}" data-send="true" data-width="450" data-show-faces="true" data-action="recommend"></div>
                {/if}

            </div>
        </div>
{foreachelse}
</div>

    {include file="$template/includes/alert.tpl" type="info" msg="{$LANG.noannouncements}" textcenter=true}

{/foreach}

{if $prevpage || $nextpage}

    <div class="col-xs-12 margin-bottom">
        <form class="form-inline" role="form">
            <div class="form-group">
                <div class="input-group">
                    <span class="btn-group">
                        {foreach $pagination as $item}
                            <a href="{$item.link}" class="btn btn-default{if $item.active} active{/if}"{if $item.disabled} disabled="disabled"{/if}>{$item.text}</a>
                        {/foreach}
                    </span>
                </div>
            </div>
        </form>
    </div>
    <div class="clearfix"></div>
{/if}
