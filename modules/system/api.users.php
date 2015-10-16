<?php
////////////////////////////////////////////////////////////////////////////////
//   Copyright (C) ReloadCMS Development Team                                 //
//   http://reloadcms.sf.net                                                  //
//                                                                            //
//   This program is distributed in the hope that it will be useful,          //
//   but WITHOUT ANY WARRANTY, without even the implied warranty of           //
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     //
//                                                                            //
//   This product released under GNU General Public License v2                //
////////////////////////////////////////////////////////////////////////////////

require_once(ENGINE_PATH.'api.usergroups.php');
define('NICKNAME_LENGTH',64);

class rcms_access
{
	var $rights_database = array();
	var $rights = array();
	var $root = false;
	var $level = 0;

	function initialiseAccess($rights, $level){
		$this->rights = array();
		$this->root = false;
		if($rights !== '*') {
			preg_match_all('/\|(.*?)\|/', $rights, $rights_r);

			// merging with usergroups rights

			$gids = @unpack_data($userdata['gids']);
			if($gids)
			{
				$gkeys = array_keys($gids);
				$count = sizeof($gids);
				$filter['logic'] = 'OR';
				$filter['gid'] = array();
				for($i=0; $i<$count; $i++)
				{
					array_push($filter['gid'], $gids[$gkeys[$i]]);
				}

				$grrights = array();
				$hlevel = $userdata['accesslevel'];
				$usergroup = new UserGroup();
				$usergroup->BeginUserGroupsListRead($filter,array('rights','level'),false);
				while($ugdata = $usergroup->Read())
				{
					if($ugdata['level'] > $hlevel)
					{
						$hlevel = $ugdata['level']; // set highest level
					}

					$ugdata['rights'] = unpack_data($ugdata['rights']);
					if($ugdata['rights'] !== '*')
					{
						$grrights = array_merge($grrights, $ugdata['rights']);
					}
					else
					{
						$root = true;
						$level = (int)@$userdata['accesslevel'];
						return true;
					}
				}
				$urights = array_merge($grrights, $rights_r[1]);
			}
			else
			{
				$urights = $rights_r[1];
			}

			// end

			foreach ($urights as $right){
				$this->rights[$right] = (empty($this->rights_database[$right])) ? ' ' : $this->rights_database[$right];
			}
		} else {
			$this->root = true;
		}
		$this->level = $level;
		return true;
	}

	/**
    * @param string $right
    * @return boolean
    * @desc Check if user have specified right
    */
	function checkForRight($right = '-any-', $username = ''){
		if(empty($username)) {
			$rights = &$this->rights;
			$root = &$this->root;
		} else {
			if(!$this->getRightsForUser($username, $rights, $root, $level)) {
				return false;
			}
		}
		return $root || ($right == '-any-' && !empty($rights)) || !empty($rights[$right]);
	}

	function getRightsForUser($username, &$rights, &$root, &$level){ // hcms: changed
		if (!($userdata = $this->getUserData($username))) return false;
		if(!empty($this->config['registered_accesslevel'])){
			$level = (int)$this->config['registered_accesslevel'];
			if(!isset($userdata['accesslevel']) || $level > $userdata['accesslevel']){
				$userdata['accesslevel'] = $level;
			}
		}
		$rights = array();
		$root = false;
		if($userdata['admin'] !== '*') {
			preg_match_all('/\|(.*?)\|/', $userdata['admin'], $rights_r);
			
			// merging with usergroups rights

			$grrights = array();
			$gids = unpack_data($userdata['gids']);
			$count = sizeof($gids);
			if($count > 0)
			{
				$gkeys = array_keys($gids);
				$filter['logic'] = 'OR';
				$filter['gid'] = array();
				for($i=0; $i<$count; $i++)
				{
					array_push($filter['gid'], $gids[$gkeys[$i]]);
				}

				$hlevel = $userdata['accesslevel'];
				$usergroup = new UserGroup();
				$usergroup->BeginUserGroupsListRead($filter,array('rights','level'),false);
				while($ugdata = $usergroup->Read())
				{
					if($ugdata['level'] > $hlevel)
					{
						$hlevel = $ugdata['level']; // set highest level
					}

					$ugdata['rights'] = unpack_data($ugdata['rights']);
					if($ugdata['rights'] !== '*')
					{
						$grrights = array_merge($grrights, $ugdata['rights']);
					}
					else
					{
						$root = true;
						$level = (int)@$userdata['accesslevel'];
						return true;
					}
				}
			}
			$urights = array_merge($grrights, $rights_r[1]);

			// end

			foreach ($urights as $right){
				$rights[$right] = (empty($this->rights_database[$right])) ? ' ' : $this->rights_database[$right];
			}
		} else {
			$root = true;
		}
		$level = (int)@$userdata['accesslevel'];
		return true;
	}

	function setRightsForUser($username, $rights, $root = false, $level = 0){
		if(empty($rights)) $rights = array();
		if(!empty($this->config['registered_accesslevel'])){
			$reg_level = (int)$this->config['registered_accesslevel'];
			if($level === ''){
				$userdata['accesslevel'] = $reg_level;
			}
		}
		if($root) {
			$rights_string = '*';
		} else {
			$rights_string = '';
			if(is_array($rights)){
				foreach ($rights as $right => $cond){
					if($cond) $rights_string .= '|' . $right . '|';
				}
			}
		}
		user_change_field($username, 'admin', $rights_string);
		user_change_field($username, 'accesslevel', $level);
		return true;
	}
}

class rcms_user_cache{
	var $cache_filename = 'users.cache.dat';
	var $cache = array();

	function rcms_user_cache(){
		if(!is_file(DATA_PATH . $this->cache_filename)) {
			$this->cache = array();
		} else {
			if(!($this->cache = @unpack_data(@file_get_contents(DATA_PATH . 'users.cache.dat')))){
				$this->cache = array();
			}
		}
	}

	function save(){
		file_write_contents(DATA_PATH .  $this->cache_filename, pack_data($this->cache));
	}

	function registerUser($username, $usernick, $email){
		$this->cache['nicks'][$username] = $usernick;
		$this->cache['mails'][$username] = $email;
		$this->save();
		return true;
	}

	function getUser($field, $value){
		return array_search($value, $this->cache[$field]);
	}

	function removeUser($username){
		if(!empty($this->cache['nicks'][$username])) {
			$this->cache['nicks'][$username] = '';
			unset($this->cache['nicks'][$username]);
		}
		if(!empty($this->cache['mails'][$username])) {
			$this->cache['mails'][$username] = '';
			unset($this->cache['mails'][$username]);
		}
		$this->save();
		return true;
	}

	function checkField($field, $value){
		if(empty($this->cache[$field])) return true;
		return !in_array_i($value, $this->cache[$field]);
	}
}

define('USERS_ALLOW_CHANGE', 0);
define('USERS_ALLOW_SET', 1);
define('USERS_DISALLOW_CHANGE', 2);
define('USERS_DISALLOW_CHANGE_ALL', 3);

class rcms_user extends rcms_access {

	var $profile_fields = array();
	var $profile_defaults = array();

	var $usermng = false;

	/**
     * This property indicates if user is registered or just a guest
     *
     * @access public
     * @var boolean
     */
	var $logged_in = false;

	/**
     * This array contain data from user's profile
     *
     * @access public
     * @var array
     */
	var $user = array();

	/**
     * Name for user cookie
     *
     * @access private
     * @var string
     */
	var $cookie_user = 'reloadcms_user';

	var $users_cache = null;

	function rcms_user()
	{
		$this->usermng = new DataMng();
		$this->usermng->setWorkTable($this->usermng->prefix.'users');
	}

	/**
     * @return boolean
     * @param string $skipcheck Use this parameter to skip userdata checks
     * @desc This function is an internal private function for class rcms_system
             and must not be used externally. This function initialize user and
             load his profile to object.
     */
	function initializeUser($skipcheck = false){ // TODO: rewrite
		$this->users_cache = new rcms_user_cache();

		$this->data['apf'] = parse_ini_file(CONFIG_PATH . 'users.fields.ini');
		// Enter access levels for fields here
		$this->profile_fields = array(
		'hideemail' => USERS_ALLOW_CHANGE,
		'hidechat' => USERS_ALLOW_CHANGE,
		'admin' => USERS_DISALLOW_CHANGE_ALL,
		'timezone' => USERS_ALLOW_CHANGE,
		'accesslevel' => USERS_DISALLOW_CHANGE_ALL,
		'last_prr' => USERS_DISALLOW_CHANGE_ALL,
		'blocked' => USERS_DISALLOW_CHANGE,
		'mail_show_chat_msgs' => USERS_ALLOW_CHANGE
		);
		foreach ($this->data['apf'] as $field => $desc) {
			$this->profile_fields[$field] = USERS_ALLOW_CHANGE;
		}
		$this->profile_defaults = array('hideemail' => 0, 'hidechat' => 0, 'admin' => ' ', 'timezone' => $this->config['timezone'], 'accesslevel' => 0, 'blocked' => 0, 'last_prr' => 0, 'mail_show_chat_msgs' => 0);

		// Load default guest userdata
		$this->user = array('nickname' => __('Guest'), 'username' => 'guest', 'admin' => '', 'timezone' => $this->config['timezone'], 'accesslevel' => 0);
		$this->initialiseAccess($this->user['admin'], (int)@$userdata['accesslevel']);

		// Ability for guests to enter nick
		$_POST['gst_nick'] = mb_substr(trim(@$_POST['gst_nick']), 0, 32);
		if(!empty($_POST['gst_nick']) && !$this->logged_in){
			$this->user['nickname'] = $_POST['gst_nick'];
			setcookie('reloadcms_nick', $this->user['nickname']);
			$_COOKIE['reloadcms_nick'] = $this->user['nickname'];
		} elseif(!$this->logged_in && !empty($_COOKIE['reloadcms_nick'])){
			$this->user['nickname'] = mb_substr(trim($_COOKIE['reloadcms_nick']), 0, NICKNAME_LENGTH);
		}
		if(!$this->users_cache->checkField('nicks', $this->user['nickname'])) {
			$this->user['nickname'] = __('Guest');
			setcookie('reloadcms_nick', '', time() - 16000);
			unset($_COOKIE['reloadcms_nick']);
		}

		// Secure the nickname
		$this->user['nickname'] = hcms_htmlsecure($this->user['nickname']);

		// If user cookie is not present we exiting without -error
		if(empty($_COOKIE[$this->cookie_user])) {
			$this->logged_in = false;
			return true;
		}

		// So we have a cookie, let's extract data from it
		$cookie_data = explode(':', $_COOKIE[$this->cookie_user], 2);
		if(!$skipcheck){
			// If this cookie is invalid - we exiting destroying cookie and exiting with error
			if(sizeof($cookie_data) != 2){
				setcookie($this->cookie_user, null, time() - 3600);
				return false;
			}
			// Now we must validate user's data
			if(!$this->checkUserData($cookie_data[0], $cookie_data[1], 'user_init', true, $this->user)){
				setcookie($this->cookie_user, null, time() - 3600);
				$this->logged_in = false;
				return false;
			}
		}

		if(!empty($this->user) && ($skipcheck && $this->user['username'] != 'guest'))
		{
			$userdata = $this->user;
		}
		else
		{
			$userdata = $this->getUserData($cookie_data[0]);
		}
		if($userdata == false){
			setcookie($this->cookie_user, null, time() - 3600);
			$this->logged_in = false;
			return false;
		}
		$this->user = $userdata;
		$this->logged_in = true;

		if(!empty($this->config['registered_accesslevel'])){
			$level = (int)$this->config['registered_accesslevel'];
			if(!isset($userdata['accesslevel'])){
				$this->user['accesslevel'] = $level;
			}
		}
		
		$tzs = DateTimeZone::listIdentifiers();
//		var_dump($this->config['timezone']);
//		var_dump($this->user);
		if(empty($this->user['timezone']))
		{
			$this->user['timezone'] = $this->config['timezone'];
		}
		$key = array_search($this->user['timezone'],$tzs);
		date_default_timezone_set($tzs[$key]);

		// Initialise access levels
		$this->initialiseAccess($this->user['admin'], (int)@$this->user['accesslevel']);

		// Secure the nickname
		$this->user['nickname'] = hcms_htmlsecure($this->user['nickname']);

		return true;
	}

	/**
     * @return boolean
     * @param string $username
     * @param string $password
     * @param string $report_to
     * @param boolean $hash
     * @param link $userdata
     * @desc This function is an internal private function for class rcms_system
             and must not be used externally. This function check user's data and
             validate his data file.
     */
	function checkUserData($username, $password, $report_to, $hash, &$userdata){ // hcms: changed
		if(preg_replace("/[\d\w]+/i", "", $username) != ""){
			$this->results[$report_to] = __('Invalid username');
			return false;
		}

		// So all is ok. Let's load userdata
		$result = $this->getUserData($username);

		// If login is not exists - we exiting with error
		if(!$result){
			$this->results[$report_to] = __('There is no user with this uid/username');
			return false;
		}

		// If password is invalid - exit with error
		if((!$hash && hcms_hash($password) !== $result['password']) || ($hash && $password !== $result['password'])) {
			$this->results[$report_to] = __('Invalid password');
			return false;
		}
		// If user is blocked - exit with error
		if(@$result['blocked']) {
			$this->results[$report_to] = __('This account has been blocked by administrator');
			return false;
		}
		$userdata = $result;
		return true;
	}

	/**
     * @return boolean
     * @param string $username
     * @param string $password
     * @param boolean $remember
     * @desc This function check user's data and log in him.
     */
	function logInUser($username, $password, $remember){
		//$username = basename($username); // no FS - no problem ;)
		if($username == 'guest') return false;
		if(!$this->logged_in && $this->checkUserData($username, $password, 'user_login', false, $userdata)){
			rcms_log_put('Notification', $this->user['username'], 'Logged in as ' . $username);
			// OK... Let's allow user to log in :)
			setcookie($this->cookie_user, $username . ':' . $userdata['password'], ($remember) ? time()+3600*24*365 : null);
			$_COOKIE[$this->cookie_user] = $username . ':' . $userdata['password'];
			$this->initializeUser(true);
			return true;
		} else {
			if(!$this->logged_in) {
                rcms_log_put('Notification', $this->user['username'], 'Attempted to log in as ' . $username);
			}
			return false;
		}
	}

	/**
     * @return boolean
     * @desc This function log out user from system and destroys his cookie.
     */
	function logOutUser(){
		if($this->logged_in){
			rcms_log_put('Notification', $this->user['username'], 'Logged out');
			setcookie($this->cookie_user, '', time()-3600);
			$_COOKIE[$this->cookie_user] = '';            
			//$this->initializeUser(false);
			return true;
		}
        return false;
	}


	function registerUser($username, $nickname, $password, $confirm, $email, $userdata){ // hcms: changed
		//$username = basename($username); // no FS - no problem ;)
		global $system;
		$nickname = empty($nickname) ? $username : mb_substr(trim($nickname), 0, NICKNAME_LENGTH);

		if(empty($username) || preg_replace("/[\d\w]+/i", '', $username) != '' || mb_strlen($username) > NICKNAME_LENGTH || $username == 'guest') {
			$this->results['registration'] = __('Invalid username');
			return false;
		}

		$result = $this->getUserData($username);

		if($result) {
			$this->results['registration'] = __('User with this username already exists');
			return false;
		}

		if(!user_check_nick_in_cache($username, $nickname, $cache)) {
			$this->results['registration'] = __('User with this nickname already exists');
			return false;
		}

		if(empty($email) || !rcms_is_valid_email($email)) {
			$this->results['registration'] = __('Invalid e-mail address');
			return false;
		}

		if(!user_check_email_in_cache($username, $email, $cache)){
			$this->results['registration'] = __('This e-mail address already registered');
			return false;
		}

		if(!empty($this->config['regconf'])) $password = $confirm = rcms_random_string(8);
		if(empty($password) || empty($confirm) || $password != $confirm) {
			$this->results['registration'] = __('Password doesnot match it\'s confirmation');
			return false;
		}

		// If our user is first - we must set him an admin rights
		$uid = $this->usermng->GetTableAINextValue();
		$_userdata['admin'] = ($uid==1 ? '*' : ' ');

		// Also we must set a hash of user's password to userdata
		$_userdata['password'] = hcms_hash($password);
		$_userdata['nickname'] = $nickname;
		$_userdata['username'] = $username;
		$_userdata['email'] = $email;

		// tz
		$tzs = DateTimeZone::listIdentifiers();
		$userdata['timezone'] = $tzs[(int)$userdata['timezone']];
		
		// Parse some system fields
		$userdata['hideemail'] = empty($userdata['hideemail']) ? '0' : '1';
		$userdata['hiding'] = empty($userdata['hiding']) ? '0' : intval($userdata['hiding']);
		$userdata['hidechat'] = empty($userdata['hidechat']) ? '0' : '1';
		$userdata['mail_show_chat_msgs'] = empty($userdata['mail_show_chat_msgs']) ? '0' : '1';

		foreach ($this->profile_fields as $field => $acc){
			if($acc <= USERS_ALLOW_SET || $acc == USERS_ALLOW_CHANGE){
				if(!isset($userdata[$field])) {
					$userdata[$field] = $this->profile_defaults[$field];
				} else {
					$_userdata['ext'][$field] = strip_tags(trim($userdata[$field]));
				}
			}
		}
		foreach ($this->data['apf'] as $field => $desc) {
			$_userdata['ext'][$field] = strip_tags(trim($userdata[$field]));
		}

		// admin -> rights!!! Ambigiuos and silly variable name in Reload!!!
		if(!$this->usermng->addData(array(null, $_userdata['username'], $_userdata['nickname'], $_userdata['password'], $_userdata['email'], pack_data($_userdata['ext']), $userdata['hiding'], $system->config['registered_accesslevel'], $_userdata['admin'], null, null))){
			$this->results['registration'] = __('Cannot save profile');
			return false;
		}

		user_register_in_cache($username, $nickname, $email, $cache);

		// CONFIRMATION EMAIL MUST BE FIXED SOON!!!!!!
		if(!empty($this->config['regconf'])) {
			$site_url = parse_url($this->url);
			rcms_send_mail($email, 'no_reply@' . $site_url['host'],
			__('Password'),
			$this->config['encoding'],
			__('Your password at') . ' ' . $site_url['host'],
			__('Your username at') . ' ' . $site_url['host'] . ': ' . $username . "\r\n" . __('Your password at') . ' ' . $site_url['host'] . ': ' . $password);
		}

		$this->results['registration'] = __('Registration complete. You can now login with your username and password.');
		rcms_log_put('Notification', $this->user['username'], 'Registered account ' . $username);
		return true;
	}

	function updateUser($username, $nickname, $password, $confirm, $email, $userdata, $admin = false){ // hcms: changed
		//$username = basename($username); // no FS - no problem ;)
		$nickname = empty($nickname) ? $username : mb_substr(strip_tags($nickname), 0, NICKNAME_LENGTH);

		if(empty($username) || preg_replace("/[\d\w]+/i", '', $username) != '') {
			$this->results['profileupdate'] = __('Invalid username');
			return false;
		}
		if($username == 'guest') return false;

		$_userdata = $this->getUserData($username);

		if(!$_userdata) {
			$this->results['profileupdate'] = __('There is no user with this uid/username');
			return false;
		}

		user_remove_from_cache($username, $cache);

		if(!user_check_nick_in_cache($username, $nickname, $cache)) {
			$this->results['profileupdate'] = __('User with this nickname already exists');
			return false;
		}

		if(empty($email) || !rcms_is_valid_email($email)) {
			$this->results['profileupdate'] = __('Invalid e-mail address');
			return false;
		}

		if(!user_check_email_in_cache($username, $email, $cache)){
			$this->results['profileupdate'] = __('This e-mail address already registered');
			return false;
		}

		if(!empty($password) && !empty($confirm) && $password != $confirm) {
			$this->results['profileupdate'] = __('Password doesnot match it\'s confirmation');
			return false;
		}

		// Also we must set a hash of user's password to userdata
		$_userdata['password']  = (empty($password)) ? $_userdata['password'] : hcms_hash($password);
		$_userdata['nickname'] = $nickname;
		$_userdata['email'] = $email;

		// tz
		$tzs = DateTimeZone::listIdentifiers();
		$userdata['timezone'] = $tzs[(int)$userdata['timezone']];
		// TODO: display updated fields in-time
		$_userdata['timezone'] = $userdata['timezone']; // not full-checked
		
		
		// Parse some system fields
		$userdata['hideemail'] = empty($userdata['hideemail']) ? '0' : '1';
		$userdata['hidechat'] = empty($userdata['hidechat']) ? '0' : '1';
		$userdata['hiding'] = empty($userdata['hiding']) ? '0' : intval($userdata['hiding']);
		$userdata['accesslevel'] = (int) @$userdata['accesslevel'];
		$userdata['mail_show_chat_msgs'] = empty($userdata['mail_show_chat_msgs']) ? '0' : '1';

		$_userdata['ext'] = array();
		foreach ($this->profile_fields as $field => $acc){
			if(($admin && $acc < USERS_DISALLOW_CHANGE_ALL) || $acc <= USERS_ALLOW_SET || $acc == USERS_ALLOW_CHANGE){
				if(!isset($userdata[$field])) {
					$userdata[$field] = $this->profile_defaults[$field];
				} else {
					$_userdata['ext'][$field] = strip_tags(trim($userdata[$field]));
				}
			}
		}
		foreach ($this->data['apf'] as $field => $desc) {
			$_userdata['ext'][$field] = strip_tags(trim($userdata[$field]));
		}

		// FORMING NEW USERDATA ARRAY

		$new_userdata = array('password' => $_userdata['password'], 'email' => $_userdata['email'], 'ext' => pack_data($_userdata['ext']), 'nickname' => $_userdata['nickname']);

		// hiding
		if($_userdata['admin'] == '*' || $admin) // allow admins being fully invisible
		{
			$new_userdata['hiding'] = $_userdata['hiding'];
		}
		else if($userdata['hiding'] > 1)
		{
			$new_userdata['hiding'] = 1;
		}
		else
		{
			$new_userdata['hiding'] = 0;
		}
		// END
		if(isset($userdata['gids']))
		{
			$new_userdata['gids'] = pack_data(array_filter($userdata['gids'], "my_is_int"));
		}
		else
		{
			$new_userdata['gids'] = '';
		}

		$idkey = is_numeric($username) ? 'uid' : 'username';
		$this->usermng->setId($username, $idkey);
		if(!$this->usermng->editData($new_userdata)){
			$this->results['profileupdate'] = __('Cannot save profile');
			return false;
		}

		user_register_in_cache($username, $nickname, $email, $cache);
		$this->results['profileupdate'] = __('Profile updated');
		if($this->user['username'] == $username) {
			// not full-checked 
			$this->user = $_userdata;
		}
		rcms_log_put('Notification', $this->user['username'], 'Updated userinfo for ' . $username);
		return true;
	}

	function recoverPassword($username, $email){ // hcms: changed
		//$username = basename($username);
		if(!($data = $this->getUserData($username))) {
			$this->results['passrec'] = __('Cannot open profile');
			return false;
		}
		if($email != $data['email']) {
			$this->results['passrec'] = __('Your e-mail doesn\'t match e-mail in profile');
			return false;
		}
		$new_password = rcms_random_string(8);
		$site_url = parse_url($this->url);
		$time = time();
		if(!empty($data['last_prr']) && !empty($this->config['pr_flood']) && (int)$time <= ((int)$data['last_prr'] + (int)$this->config['pr_flood'])){
			$this->results['passrec'] = __('Too many requests in limited period of time. Try later.');

			// rcms fucks =/// usage of changeProfileField added
			if(!$this->changeProfileField($username, 'last_prr', time())) {
				$this->results['passrec'] .= '<br />' . __('Cannot save profile');
			}

			rcms_log_put('Notification', $this->user['username'], 'Attempted to recover password for ' . $username);
			return false;
		}

		if(rcms_send_mail($email, 'no_reply@' . $site_url['host'], __('Password'), $this->config['encoding'], __('Your new password at') . ' ' . $site_url['host'],
		__('Your username at') . ' ' . $site_url['host'] . ': ' . $username . "\r\n" . __('Your new password at') . ' ' . $site_url['host'] . ': ' . $new_password)) {
			if(!$this->changeMultiProfileFields($username, array('password', 'last_prr'), array(hcms_hash($new_password), $time)))
			{
				$this->results['passrec'] = __('Cannot save profile');
				return false;
			}
			$this->results['passrec'] = __('New password has been sent to your e-mail');
			rcms_log_put('Notification', $this->user['username'], 'Recovered password for ' . $username);
			return true;
		} else {
			rcms_log_put('Notification', $this->user['username'], 'Recovered password for ' . $username . '" (BUT E-MAIL WAS NOT SENT)');
			$this->results['passrec'] = __('Cannot send e-mail');
			return false;
		}
	}

	function getUserData($username){ // hcms: changed
		//$result = @unpack_data(@file_get_contents(USERS_PATH . basename($username)));
		if(!$this->usermng)
		{
			$this->rcms_user();
		}
		$this->usermng->setId($username, (is_numeric($username) ? 'uid' : 'username'));
		$this->usermng->BeginDataRead();
		$result = $this->usermng->Read();
		if(empty($result))
		{
			return false;
		}

		$ext = unpack_data($result['ext']);
		if(is_array($ext))
		{
			$result = array_merge($ext, $result);
		}
		//var_dump($result);
		$result['admin'] = $result['rights']; // rcms compability
		$result['accesslevel'] = $result['level']; // rcms compability

		return (empty($result) ? false : $result);
	}

	function getUserList($expr = '*', $id_field = '', $fields = '*', $search = ''){ // hcms: changed
		$return = array();

		if(!$this->usermng)
		{
			$this->rcms_user();
		}
		$this->usermng->setId('','');
		if(empty($search))
		{
			$this->usermng->BeginDataRead($fields); // TODO: pages, expressions parsing
			while($data = $this->usermng->Read())
			{
				$data = array_merge(unpack_data($data['ext']), $data);
				$data['admin'] = $data['rights']; // rcms compability
				$data['accesslevel'] = $data['level']; // rcms compability
				if(!empty($id_field) && !empty($data[$id_field]))
				{
					$return[$data[$id_field]] = $data;
				}
				else
				{
					$return[] = $data;
				}
			}
		}
		else // TODO: fix id_field =////
		{
			$where = $this->usermng->ParseFilter($search['filter'],$search['idkey'],$search['extwhere']);
			$this->usermng->BeginRawDataRead('SELECT '.($fields == '*' || !is_array($fields) ? '*' : implode(',',$fields)).' FROM '.$this->usermng->table.' '.$where.';');
			while($data = $this->usermng->Read())
			{
				$return[] = $data;
			}
		}

		return $return;
	}

	function changeProfileField($username, $field, $value){ // hcms: changed
		//$username = basename($username);
		$this->usermng->setId($username, (is_numeric($username) ? 'uid' : 'username'));
		if (!($userdata = $this->getUserData($username))) return false;

		if($field == 'admin')
		{
			$field = 'rights';
		}
		if($field == 'accesslevel')
		{
			$field = 'level';
		}

		if(!in_array($field,array('uid','username','nickname','password','email','ext','hiding','level','rights','gids','friends')))
		{
			// ext fields mode
			$ext = unpack_data($userdata['ext']); // unpack
			$ext[$field]=$value;
			$change= array('ext' => pack_data($ext));
		}
		else
		{
			// db fields mode
			$change= array($field => $value);
		}

		if(!$this->usermng->editData($change))
		{
			return false;
		}
		return true;
	}

	function changeMultiProfileFields($username, $fields, $values)
	{
		$this->usermng->setId($username, (is_numeric($username) ? 'uid' : 'username'));
		if (!($userdata = $this->getUserData($username))) return false;

		$count = sizeof($fields);
		for($i=0; $i<$count; $i++)
		{
			if(!in_array($fields[$i],array('uid','username','nickname','password','email','ext','hiding','level','rights','gids','friends')))
			{
				// ext fields mode
				$ext = unpack_data($userdata['ext']); // unpack
				$ext[$fields[$i]]=$value[$i];
				$change['ext'] = pack_data($ext);
			}
			else
			{
				// db fields mode
				$change[$fields[$i]] = $value[$i];
			}
		}


		if(!$this->usermng->editData($change))
		{
			return false;
		}
		return true;
	}

	function deleteUser($username){ // hcms: changed
		//$username = basename($username);
		$this->usermng->setId($username, (is_numeric($username) ? 'uid' : 'username')); //TODO: to fix bug with username such as '000', '111', etc
		//$this->usermng->setId($username, 'username');
		if(!$this->usermng->dropData())
		{
			return false;
		}

		user_remove_from_cache($username, $cache);
		return true;
	}

	function createLink($user, $nick, $target = ''){
		if(!empty($target))  $target = ' target="' . $target . '"';
		if($user != 'guest') {
			return '<a href="' . RCMS_ROOT_PATH . '?module=user.list&amp;user=' . $user . '"' . $target . '>' . strip_tags($nick) . '</a>';
		} elseif(!empty($nick)) {
			return $nick;
		} else {
			return __('Guest');
		}
	}
}

function my_is_int($value)
{
	return is_integer(intval($value));
}
?>