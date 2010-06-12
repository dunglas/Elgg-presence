<?php
/**
 * Elgg Online Friends plugin
 * My Friends online interface for Elgg sites
 *
 * @package Online Friends
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Shannon Anderson
 * @copyright Shannon Anderson
 * @link http://www.squidworks.net
 */

global $CONFIG;

if (isloggedin()) {
    get_online_facebook_friends($_SESSION['user']);

    $onlineFacebook = get_online_facebook_friends($_SESSION['user']);
    $onlineTwitter = get_online_elgg_friends($_SESSION['user']);
    $onlineElgg = get_online_elgg_friends($_SESSION['user']);

    $first = true;

    echo '<h1>' . elgg_echo('presence:online') . '</h1>';
    
    get_online_twitter_friends($_SESSION['user']);

    foreach ($_SESSION['user']->getFriends() as $friend) {
        $online = array();

        if (in_array($friend->getGUID(), $onlineElgg)) {
            $online[] = 'elgg';
        }
        if (in_array($friend->getGUID(), $onlineTwitter)) {
            $online[] = 'twitter';
        }
        if (in_array($friend->getGUID(), $onlineFacebook)) {
            $online[] = 'facebook';
        }

        if (count($online)) {
            if ($first) {
                echo '<ul>';
                $first = false;
            }

            $icon = $friend->getIcon('topbar');

            echo '<img src="' . $icon . '" alt="' . $friend->name . '" />';
            echo ' <a href="' . $friend->getUrl() . '">' . $friend->name . '</a>';
            if (in_array('elgg', $online)) echo ' [online]';

            if ($friend->twitter_screen_name) {
                echo ' <a href="http://twitter.com/' . $friend->twitter_screen_name . '">Twitter</a>';
                if (in_array('twitter', $online)) echo ' [online]';
            }
            if ($friend->facebook_uid) {
                echo ' <a href="http://www.facebook.com/profile.php?id=' . $friend->facebook_uid . '">Facebook</a>';
                if (in_array('facebook', $online)) echo ' [online]';
            }
        }
    }
    if ($first) {
        echo '<p>' . elgg_echo('presence:noone') . '</p>';
    } else {
        echo '</ul>';
    }
}