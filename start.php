<?php
/**
 * Elgg presence plugin
 *
 * @package presence
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @copyright Kévin Dunglas - 2010
 */

function get_twitter_friends($user) {
    $ftwitter_friends = array();
    foreach ($user->getFriends() as $friend) {
        $twitter_screen_name = $friend->twitter_screen_name;

        if ($twitter_screen_name) {
            $twitter_friends[$friend->getGUID()] = $twitter_screen_name;
        }
    }

    return $twitter_friends;
}

function get_online_twitter_friends($user) {
    if (!isset ($user->twitter_oauth_token) || !isset ($user->twitter_oauth_token_secret)) return false;

    require_once(dirname(dirname(__FILE__)) . '/twitterlogin/models/OAuth.php');
    require_once(dirname(dirname(__FILE__)) . '/twitterlogin/models/twitteroauth.php');
    require_once(dirname(dirname(__FILE__)) . '/twitterlogin/models/secret.php');

    $connection = new TwitterOAuth($consumer_key, $consumer_secret, $user->twitter_oauth_token, $user->twitter_oauth_token_secret);
    $result = $connection->get('statuses/user_timeline', array('screen_name' => $user->twitter_screen_name, 'count' => 1));

    $elgg_ids = array();
    foreach (get_twitter_friends($user) as $key => $value) {
        $result = $connection->get('statuses/user_timeline', array('screen_name' => $value, 'count' => 1));
        if (strtotime($result[0]->created_at) >= (time() - 600))
            $elgg_ids[] = $key;
    }

    return $elgg_ids;
}

function get_facebook_friends($user) {
    $facebook_friends = array();
    foreach ($user->getFriends() as $friend) {
        $facebook_id = get_plugin_usersetting('facebook_id', $friend->getGUID(), 'fblink');

        if ($facebook_id) {
            $facebook_friends[$friend->getGUID()] = $facebook_id;
        }
    }

    return $facebook_friends;
}

function get_online_facebook_friends($user) {
    $facebook = fblink_facebook();

    $facebook_id = $facebook->getUser();
    $registered_facebook_id = get_plugin_usersetting('facebook_id', $user->getGUID(), 'fblink');

    if (!$registered_facebook_id || $registered_facebook_id != $facebook_id) return false;

    $facebook_friends = get_facebook_friends($user);
    $facebook_ids = array_values($facebook_friends);

    //$facebook_ids = array('1416073175', '1416073175', '1053710690', '835501869');

    $in = '(' . implode(', ', $facebook_ids) . ')';
    $query = 'SELECT uid, name, online_presence FROM user WHERE online_presence IN (\'active\', \'idle\') AND uid IN ' . $in;
    //$query = 'SELECT uid, name, online_presence FROM user WHERE uid IN ' . $in;
    //echo $query;

    $result = $facebook->api(
            array('method' => 'fql.query',
            'query' => $query
    ));

    //var_dump($result);

    $elgg_ids = array();
    foreach ($result as $facebook_user) {
        $elgg_ids[] = array_search($facebook_user['uid'], $facebook_friends);
    }

    return $elgg_ids;
}

function get_online_elgg_friends($user) {
    $elgg_ids = array();

    foreach ($user->getFriends() as $friend) {
        if ($friend->last_action >= (time() - 600)) {
            $elgg_ids[] = $friend->getGUID();
        }
    }

    return $elgg_ids;
}

function presence_init() {
    extend_view('page_elements/footer', 'page_elements/online');
}

register_elgg_event_handler('init','system','presence_init');
?>