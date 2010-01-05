<?php
/**
 * al_plurk_api.php - A Plurk Library based on official Plurk API
 *
 * This file is based on :
 * 
 * al_plurk_api.php - an unofficial PHP Plurk API provided by Ryan Lim.
 * 	@author    Ryan Lim <plurk-api@ryanlim.com>
 * 	@copyright 2008 Ryan Lim
 * 	@link      http://plurk.ryanlim.com/
 *
 * This file is subject to the terms and conditions of the GNU General Public
 * License. See the file COPYING in the main directory of this archive for
 * more details.
 */

/**
 * al_plurk_api version.
 */
define('ALPLURKAPI_VERSION', '0.1.0');

/**
 * Set our include_path to include our own pear location path.
 */
set_include_path('./pear' . PATH_SEPARATOR . get_include_path() . 
    PATH_SEPARATOR . '.');

require_once dirname(__FILE__).'/HTTP/Client.php';
require_once dirname(__FILE__).'/HTTP/Client/CookieManager.php';

define('HTTP_BASE', 'http://www.plurk.com/API');

class al_plurk_api
{
    /**
     * The http options that we provide to the http methods.
     * @var array $http_options
     */
    protected $http_options = array();

    /**
     * The http client object used to make http requests
     * @var array $http_client
     */
    protected $http_client = null;

    /**
     * The plurk URL paths that we can use.
     * @var array $plurk_paths
     */
    protected $plurk_paths = array(
	/* Users */
//	'register'		=> '/Users/register',
	'login'                 => '/Users/login',
//	'update_picture'	=> '/Users/update/Picture',
//	'update'		=> '/Users/update',

	/* Polling */
	'plurk_get_from'	=> '/Polling/getPlurks',

	/* Timeline */
//	'plurk_get'             => '/Timeline/getPlurk',
	'plurk_get'             => '/Timeline/getPlurks',
        'plurk_get_unread'      => '/Timeline/getUnreadPlurks',
	'plurk_mute'		=> '/Timeline/mutePlurks',
//	'plurk_unmute'		=> '/Timeline/unmutePlurks'
//	'plurk_mark_as_read'	=> '/Timeline/markAsRead',
        'plurk_add'             => '/Timeline/plurkAdd',
//	'upload_picture'	=> '/Timeline/uploadPicture'
	'plurk_delete'          => '/Timeline/plurkDelete',
//	'plurk_edit'		=> '/Timeline/plurkEdit',

	/* Responses */
        'plurk_response_get'	=> '/Responses/get',
	'plurk_response_add'	=> '/Responses/responseAdd',
//	'plurk_response_del'	=> '/Responses/responseDelete',

	/* Profile */
//	'profile_get_own'	=> '/Profile/getOwnProfile',
	'profile_get'		=> '/Profile/getPublicProfile',

	/* Friends and fans */
	'friends_get_10'	=> '/FriendsFans/getFriendsByOffset',
//	'fans_get_10'		=> '/FriendsFans/getFansByOffset',
//	'following_get_10'	=> '/FriendsFans/getFollowingByOffset',
//	'friend_add'		=> '/FriendsFans/becomeFriend',
//	'friend_remove'		=> '/FriendsFans/removeAsFriend',
//	'fan_add'		=> '/FriendsFans/becomeFan',
//	'following_set'		=> '/FriendsFans/setFollowing',
	'friends_get'		=> '/FriendsFans/getCompletion',

	/* Alerts */
	'notification_get'		=> '/Alerts/getActive',
//	'get_history'		=> '/Alerts/getHistory',
//	'add_as_fan'		=> '/Alerts/addAsFan',
//	'add_all_as_fan'	=> '/Alerts/addAllAsFan',
//	'add_all_as_friend'	=> '/Alerts/addAllAsFriends',
//	'add_as_friend'		=> '/Alerts/addAsFriend',
//	'deny_friendship'	=> '/Alerts/denyFriendship',
//	'notification_remove'	=> '/Alerts/removeNotification',

	/* Search */
//	'plurk_search'		=> '/PlurkSearch/search',
//	'user_search'		=> '/UserSearch/search',

	/* Emoticons */
//	'emotcion_get'		=> 'Emotions/get',

	/* Blocks */
//	'block_get'		=> 'Blocks/get',
//	'block_set'		=> 'Blocks/block',
//	'block_unset'		=> 'Blocks/unblock',

	/* Cliques */
//	'cliques_list'		=> 'Cliques/get_cliques',
//	'cliques_create'	=> 'Cliques/create_clique',
//	'cliques_get'		=> 'Cliques/get_clique',
//	'cliques_rename'	=> 'Cliques/rename_clique',
        );

    /**
     * Are we logged in?
     * @var bool $bool_login
     */
    protected $bool_login = false;

    /**
     * Our Plurk uid.
     * @var int $uid
     */
    public $uid = -1;

    /**
     * Our Plurk nick name.
     * @var string $nick_name
     */
    public $nick_name = '';

    /**
     * The associative array of friend uids => nick_names
     * @var array $friends
     */
    public $friends = array();

    protected $api_key = '';
    /**
     * Constructor.
     */
    function __construct( $api_key)
    {
        $this->bool_login = false;

        $this->http_options = array(
            'User-Agent' => 'al_plurk_api ' . ALPLURKAPI_VERSION,
        );

        $this->http_client = new HTTP_Client();
        $this->http_client->setDefaultHeader($this->http_options);

	$this->api_key = $api_key;
    }


    function http_post( $url, $data, $preEncoded = false, $files = array(), $headers = array())
    {
	    return $this->http_client->post( $url, $data, $preEncoded, $files, $headers);
    }
    function http_get( $url, $data = null, $preEncoded = false, $headers = array())
    {
	    return $this->http_client->get( $url, $data, $preEncoded, $headers);
    }

    function &http_response()
    {
	    return $this->http_client->currentResponse();
    }

    function plurk_post( $url, $data, $preEncoded = false, $files = array(), $headers = array())
    {
	    if( is_array( $data))
		$data['api_key'] = $this->api_key;
	    else
		$data= array('api_key' => $this->api_key);

	    return $this->http_client->post( HTTP_BASE . $url, $data, $preEncoded, $files, $headers);
    }

    function &plurk_response()
    {
	    return $this->http_client->currentResponse();
    }

    function plurk_get( $url, $data = null, $preEncoded = false, $headers = array())
    {
	    if( is_array( $data))
		$data['api_key'] = $this->api_key;
	    else
		$data= array('api_key' => $this->api_key);

	    return $this->http_client->get( HTTP_BASE . $url, $data, $preEncoded, $headers);
    }

    /**
     * Login to Plurk.
     *
     * @param string $nick_name The nickname of the user to login as.
     * @param string $password  The password for this user.
     *
     * @return bool true if login was successful, false otherwise.
     */
    function login($username, $password)
    {
        $array_query = array(
            'username' => $username,
            'password' => $password,
            );

        $this->plurk_post($this->plurk_paths['login'],
            $array_query);

        $array_response = $this->plurk_response();

        $this->bool_login = false;

        foreach ($this->http_client->_cookieManager->_cookies as $cookie) {
            if (isset($cookie['name']) && $cookie['name'] == 'plurkcookiea') {
                $this->bool_login = true;

                break;
            }
        }

        if ($this->bool_login == true) {
            /*
             * Get my user information.
             */
            $this->http_client->get("http://www.plurk.com/user/{$nick_name}");

            $array_profile = $this->plurk_response();
            preg_match('/var GLOBAL = \{.*"uid": ([\d]+),.*\}/imU',
                $array_profile['body'], $matches);
            $this->uid       = $matches[1];
            $this->nick_name = $nick_name;

            /*
             * Get my friends' information.
	     */
	    /* FIXME: should use "get_friends" to get whole friends*/
            $this->plurk_get($this->plurk_paths['get_10_friends'],
                array('user_id' => $this->uid));

            $array_result = $this->plurk_response();

            $this->friends = array();

            $array_tmp = al_plurk_api::dejsonize($array_result['body']);
            if (is_array($array_tmp[0])) {
                foreach ($array_tmp as $key => $value) {
                    $this->friends[ $array_tmp[ $key]['id']] = $value;
                }
            }
        }
        return $this->bool_login;
    }


    /**
     * Are we logged in?
     *
     * @return bool true if we are logged in, false otherwise.
     */
    function is_loggedin()
    {
        return $this->bool_login;
    }

    public static function dejsonize($data)
    {
	return json_decode( $data, true);
    }

    function profile_get( $uid)
    {
        $this->plurk_get($this->plurk_paths['profile_get'], 
            array('user_id' => $uid));
	$array_response= $this->plurk_response();

        return al_plurk_api::dejsonize( $array_response['body']);
    }

    /**
     * Add a new plurk.
     *
     * @param string $string_content   The content of the plurk to be posted.
     * @param string $string_lang      The plurk language.
     * @param string $string_qualifier The plurk qualifier.
     * @param bool   $allow_comments   true if this plurk allows comments, false 
     *                                 otherwise.
     * @param array  $array_limited_to The array of uids this plurk is visible 
     *                                 to. If this array is of size 0, it is 
     *                                 visible to everyone.
     *
     * @return bool true if it was posted, false otherwise.
     */
    function plurk_add(
        $string_content = '',
        $string_lang = 'en',
        $string_qualifier = 'says',
        $no_comments = false,
        $array_limited_to = array()
    )
    {
        if ($this->bool_login == false) {
            return false;
        }

        if (!is_string($string_lang) ||
            !is_string($string_qualifier) ||
            !is_string($string_content) ||
            $string_content == '' ||
            ! is_array($array_limited_to) ||
            ! is_bool($no_comments)
        ) {
            return false;
        }

        $posted_ = gmdate('c');
        $posted_ = explode('+', $posted_);
        $posted  = urlencode($posted_[0]);

        $qualifier = urlencode(':');
        if ($string_qualifier != '') {
            $qualifier = urlencode($string_qualifier);
        }

        if (strlen($string_content) > 140) {
            return false;
        }
        $content = urlencode($string_content);

        $lang = urlencode($string_lang);

        $array_query = array(
            'qualifier'   => $qualifier,
            'content'     => $content,
            'lang'        => $lang,
            'no_comments' => $no_comments?1:0
            );

        if (count($array_limited_to) > 0) {
            $limited_to = '[' . implode(',', $array_limited_to) . ']';
            $limited_to = urlencode($limited_to);

            $array_query['limited_to'] = $limited_to;
        }

        $this->plurk_get($this->plurk_paths['plurk_add'], 
            $array_query, true);

        $array_response = $this->plurk_response();

        if (preg_match('/anti-flood/', $array_response['body']) != 0) {
            return false;
        }

        if (preg_match('/"error":\s(\S+)}/', $array_response['body'], 
            $error_match) != 0) {
            if ($error_match[1] != 'null') {
                echo "Error sending message: ";
                echo print_r($error_match[1], true) . "\n";
                return false;
            }
        }

        $array_plurks = al_plurk_api::dejsonize($array_response['body']);

        return $array_plurks['plurk_id'];
    }

    /**
     * Get alert notification for friend requests.
     *
     * @return mixed false on error(s), otherwise an array
     * of friend uids.
     */
    function notification_get()
    {
        if ($this->bool_login == false) {
            return false;
        }

        $this->plurk_get($this->plurk_paths['notification_get']);
        $array_notification_page = $this->plurk_response();

        preg_match_all('/DI\s*\(\s*Notifications\.render\(\s*(\d+),\s*0\)\s*\);/iU',
            $array_notification_page['body'],
            $requests);

        if (isset($requests[1])) {
            return $requests[1];
        } else {
            return array();
        }
    }

    /**
     * Mute or unmute plurks
     *
     * @param array $int_plurk_id The plurk id to be muted/unmuted.
     * @param bool  $bool_setmute If true, this plurk is to be muted, else, 
     *                            unmute it.
     *
     * @return bool Returns true if successful or false otherwise.
     */
    function plurk_mute($int_plurk_id, $bool_setmute= true)
    {
        if ($this->bool_login == false) {
            return false;
        }

        if (!is_int($int_plurk_id) || ! is_bool($bool_setmute)) {
            return false;
        }

        $int_setmute = 0;
        if ($bool_setmute == true) {
            $int_setmute = 1;
        }

        $this->plurk_post($this->plurk_paths['plurk_mute'],
            array('ids' => '['.$int_plurk_id.']'));
        $response = $this->plurk_response();

        return $response['body'];
    }


    /**
     * Deletes a plurks.
     *
     * @param array $int_plurk_id The plurk id to be deleted.
     *
     * @return bool Returns true if successful or false otherwise.
     */
    function plurk_delete($int_plurk_id)
    {
        if ($this->bool_login == false) {
            return false;
        }

        if (!is_int($int_plurk_id)) {
            return false;
        }

        $this->plurk_post($this->plurk_paths['plurk_delete'],
            array('plurk_id' => $int_plurk_id));
        $response = $this->plurk_response();

        if ($response['body'] == 'ok') {
            return true;
        }
        return false;
    }

    /**
     * Gets the plurks for the user. Only 25 plurks are fetch at a time as this 
     * is limited on the server.
     * The array returned is ordered most recent post first followed by 
     * previous posts.
     *
     * @param string $date_from        The date/time to start fetching plurks. This 
     *                                 must be in the <yyyy-mm-dd>T<hh:mm:ss> format 
     *                                 assumed to be UTC time.
     * @param bool   $fetch_responses  If true, populate the responses_fetch value 
     *                                 with the array of responses.
     * @param bool   $self_plurks_only If true, return only self plurks.
     *
     * @return array The array (numerical) of plurks (an associative subarray).
     */
    function plurk_from( $date_from = null, $fetch_responses = false, 
	    $self_plurks_only = false)
    {
        $data = '[]';

        $array_query = array();
        if (isset($date_from) && $date_from != null) {
            $array_query['offset'] = "$date_from";
	    $this->plurk_post($this->plurk_paths['plurk_get_from'],
		    $array_query);
	}
	else {
            $this->plurk_post($this->plurk_paths['plurk_get'], 
                $array_query);
	}
        $data = $this->plurk_response();
        $data = $data['body'];

        $array_plurks = al_plurk_api::dejsonize($data);

	if( !is_array( $array_plurks))
		return array();
        foreach ($array_plurks as &$plurk) {
            $plurk['nick_name'] = $this->uid_to_nickname((int) $plurk['owner_id']);

            $plurk['responses_fetched'] = null;
            if ($fetch_responses == true) {
                $plurk['responses_fetched'] = 
                    $this->plurk_response_get($plurk['plurk_id']);
            }
            $plurk['permalink'] = al_plurk_api::plurk_get_permlink($plurk['plurk_id']);
        }

        return $array_plurks;
    }

    /**
     * Get the unread plurks.
     *
     * @param bool $fetch_responses If true, populate the responses_fetch value 
     *                              with the array of responses.
     *
     * @return array The array (numerical) of unread plurks (an associative 
     *               subarray).
     */
    function plurk_get_unread($fetch_responses = false)
    {
        if ($this->bool_login == false) {
            return array();
        }

        $this->plurk_get($this->plurk_paths['plurk_get_unread']); 
        $data = $this->plurk_response();
        $data = $data['body'];

        $array_plurks = al_plurk_api::dejsonize($data);

        foreach ($array_plurks as &$plurk) {
            $plurk['nick_name'] = $this->uid_to_nickname((int) $plurk['owner_id']);

            $plurk['responses_fetched'] = null;

            if ($fetch_responses == true) {
                $plurk['responses_fetched'] = 
                    $this->plurk_response_get($plurk['plurk_id']);
            }
            $plurk['permalink'] = al_plurk_api::plurk_get_permlink($plurk['plurk_id']);
        }

        return $array_plurks;
    }

    /**
     * Translates a uid to the corresponding nickname.
     *
     * @param int $uid The uid to be translated.
     *
     * @return string The nick_name corresponding to the given uid.
     */
    function uid_to_nickname($uid)
    {
        if (!is_int($uid)) {
            return false;
        }

        if ($uid == $this->uid) {
            return (string) $this->nick_name;
        }

        foreach ($this->friends as $friend) {
            if ($friend['uid'] == $uid) {
                return (string) $friend['nick_name'];
            }
        }

        /*
         * We don't know who this is, just return the string "User $uid"
         */
        return 'User ' . $uid;

    }

    /**
     * Respond to a plurk.
     *
     * @param int    $int_plurk_id     The plurk ID number to respond to.
     * @param string $string_lang      The plurk language.
     * @param string $string_qualifier The qualifier to use for this response.
     * @param string $string_content   The content to be posted as a reply.
     *
     * @return mixed false on failure, otherwise the http response from plurk.
     */
    function plurk_response_add(
        $int_plurk_id,
        $string_qualifier,
        $string_content
    )
    {
        if ($this->bool_login == false) {
            return false;
        }

        if (!is_int($int_plurk_id) ||
            ! is_string($string_qualifier) ||
            ! is_string($string_content)
        ) {
            return false;
        }

        $qualifier = urlencode(':');
        if ($string_qualifier != '') {
            $qualifier = urlencode($string_qualifier);
        }

        if (strlen($string_content) > 140) {
            return false;
        }
        $content = urlencode($string_content);

        $array_query = array(
            'qualifier'   => $qualifier,
            'content'     => $content,
            'plurk_id'    => $int_plurk_id,
        );

        $this->plurk_post($this->plurk_paths['plurk_response_add'],
            $array_query, true);
        $array_response = $this->plurk_response();

        return $array_response['body'];
    }

    /**
     * Get the responses of a plurk. This method will load "temporary" friends 
     * who have responded to the plurk.
     *
     * @param int $int_plurk_id The plurk ID 
     *
     * @return array The array of responses.
     */
    function plurk_response_get($int_plurk_id)
    {
        $this->plurk_post($this->plurk_paths['plurk_response_get'],
            array('plurk_id' => $int_plurk_id)); 
        $data            = $this->plurk_response();
        $string_response = $data['body'];
        
        $data = explode('"responses": ', $string_response);

        preg_match('/\{"friends": \{"\d+": (.*)\}, "responses/', $data[0], 
            $local_friend);

        if (isset($local_friend[1])) {
            $temp_friends = array();

            $each_friend_almost_json = preg_split('/\}, "\d+": \{/', 
                $local_friend[1]);

            foreach ($each_friend_almost_json as $friend_data) {
                if (substr($friend_data, 0, 1) != '{') {
                    $friend_data = '{' . $friend_data;
                }

                if (substr($friend_data, strlen($friend_data)-1, 
                    strlen($friend_data)) != '}') {
                    $friend_data = $friend_data . '}';
                }

                $temp_friends = array_merge($temp_friends, 
                    al_plurk_api::dejsonize($friend_data));
            }
            $this->friends = array_merge($this->friends, $temp_friends);
        }

        $responses = array();
        if (isset($data[1])) {
            $response_data = substr($data[1], 0, strlen($data[1])-1);

            $responses = al_plurk_api::dejsonize($response_data);

            foreach ($responses as &$each_response) {
                $each_response['nick_name'] = 
                    $this->uid_to_nickname($each_response['user_id']);
            }
        }

        return $responses;
    }

    /**
     * Retrieve a user's uid from given his/her plurk nick name.
     * 
     * @param string $string_nick_name The nickname of the user to retrieve the 
     *                                 uid from.
     *
     * @return int The uid of the given nickname.
     */
    function nickname_to_uid($string_nick_name)
    {
        if (!is_string($string_nick_name) || $string_nick_name == '') {
            return -1;
        }

	$array_profile = $this->profile_get( $string_nick_name);

        return $array_profile['user_info']['uid'];
    }

    /**
     * Convert a plurk ID to a permalink URL.
     *
     * @param int $plurk_id The plurk ID number.
     *
     * @return string The permalink URL address.
     */
    public static function plurk_get_permlink($plurk_id)
    {
        if (!is_int($plurk_id)) {
            return '';
        }

        return "http://www.plurk.com/p/" . base_convert($plurk_id, 10, 36);
    }

    /**
     * Convert a plurk permalink URL address to a plurk ID.
     *
     * @param string $string_permalink The plurk permalink URL address.
     *
     * @return int The plurk ID number.
     */
    public static function plurk_get_plurkid($string_permalink)
    {
        $base36number = str_replace('http://www.plurk.com/p/', '', 
            $string_permalink);

        return (int) base_convert($base36number, 36, 10);
    }
}
?>
