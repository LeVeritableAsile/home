<?php

require 'config.php';
require 'include/essentials.php';
require FORUM_ROOT.'include/common.php';

// Load the necessary search functions            
require FORUM_ROOT.'include/search_functions.php';

$forum_loader->add_css(home_link('Home.css'), array('type' => 'url', 'group' => FORUM_CSS_GROUP_SYSTEM));

define('FORUM_ALLOW_INDEX', 1);
define('FORUM_PAGE', 'index');
require FORUM_ROOT.'header.php';

// START SUBST - <!-- forum_main -->
ob_start();

$forum_page['item_body']['action'] = home_link('shouts.php?action=post');
$forum_page['item_body']['csrf_token'] = generate_form_token($forum_page['item_body']['action']);

?>
    <div id="home">
        <div id="home-shouts" class="main-content">
<?php
if (!$forum_user['is_guest'])
{
?>
            <form id="home-shout-form" action="<?php echo $forum_page['item_body']['action'] ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $forum_page['item_body']['csrf_token'] ?>" />
                <div>
                    <div class="submit primary"><input type="submit" value="Shout"></div>
                    <div class="fdl-input"><input id="home-shout-form-input" type="text" name="text"></div>
                </div>
            </form>
<?php
}
?>
            <div id="home-shouts-messages"></div>
        </div>
    </div>
<?php


$tpl_temp = forum_trim(ob_get_contents());
$tpl_main = str_replace('<!-- forum_main -->', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <!-- forum_main -->

if ($forum_user['is_guest'])
    $shout_js = 'function bind_shout_prefix_event() { };';
else
    $shout_js = '
        function bind_shout_prefix_event() {
            $(".home-shouts-message").click(function(event) {
                var prefix = "[i]@";
                prefix += $(".home-shouts-message-user", this).text();
                prefix += "[/i] ";

                $("#home-shout-form-input").val(prefix);
            });
        };
    ';

$shout_js .= '
    function load_shouts() {
        $("#home-shouts-messages").load("shouts.php?action=view",
                                        bind_shout_prefix_event);

        setTimeout(load_shouts, 30 * 1000);
    }

    $("#home-shout-form").submit(function(event) {
        $.ajax({
            url: $(this).attr("action"),
            type: "post",
            dataType: "html",
            data: $(this).serialize(),
            success: function(data) {
                $("#home-shouts-messages").html(data);
                bind_shout_prefix_event();
                $("#home-shout-form")[0].reset();
            }
        });

        return false;
    });

    $(document).ready(function(){
        load_shouts();
    });
';

$forum_loader->add_js($shout_js, array('type' => 'inline', 'weight' => 100, 'group' => FORUM_JS_GROUP_DEFAULT));

require FORUM_ROOT.'footer.php';
