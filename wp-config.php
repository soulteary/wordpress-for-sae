<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', SAE_MYSQL_DB);

/** MySQL database username */
define('DB_USER', SAE_MYSQL_USER);

/** MySQL database password */
define('DB_PASSWORD', SAE_MYSQL_PASS);

/** MySQL hostname */
define('DB_HOST', SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/** Use Muitiple Database */
define('WP_USE_MULTIPLE_DB', true);

/** Support Muitiple Database */
$db_list = array(
  	'write'=> array(
			array(
				'db_host' => SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,
				'db_user'=> SAE_MYSQL_USER,
				'db_password'=> SAE_MYSQL_PASS,
				'db_name'=> SAE_MYSQL_DB,
				'db_charset'=> 'utf8'
				)
			),
		'read'=> array(
			array(
				'db_host' => SAE_MYSQL_HOST_S.':'.SAE_MYSQL_PORT,
				'db_user'=> SAE_MYSQL_USER,
				'db_password'=> SAE_MYSQL_PASS,
				'db_name'=> SAE_MYSQL_DB,
				'db_charset'=> 'utf8'
				)
			),
		);
$global_db_list = $db_list['write'];

/** SAE Storage Domain */
define('SAE_STORAGE', wordpress);

/** File Upload Dir */ 
define('SAE_DIR','saestor://'.SAE_STORAGE.'/uploads');

/** File URL PATH */ 
define('SAE_URL', 'http://' . $_SERVER['HTTP_APPNAME'] . '-'.SAE_STORAGE.'.stor.sinaapp.com/uploads');


/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         hash_hmac('sha1', SAE_ACCESSKEY . 'AUTH_KEY', SAE_SECRETKEY ));
define('SECURE_AUTH_KEY',  hash_hmac('sha1', SAE_ACCESSKEY . 'SECURE_AUTH_KEY', SAE_SECRETKEY ));
define('LOGGED_IN_KEY',    hash_hmac('sha1', SAE_ACCESSKEY . 'LOGGED_IN_KEY', SAE_SECRETKEY ));
define('NONCE_KEY',        hash_hmac('sha1', SAE_ACCESSKEY . 'NONCE_KEY', SAE_SECRETKEY ));
define('AUTH_SALT',        hash_hmac('sha1', SAE_ACCESSKEY . 'AUTH_SALT', SAE_SECRETKEY ));
define('SECURE_AUTH_SALT', hash_hmac('sha1', SAE_ACCESSKEY . 'SECURE_AUTH_SALT', SAE_SECRETKEY ));
define('LOGGED_IN_SALT',   hash_hmac('sha1', SAE_ACCESSKEY . 'LOGGED_IN_SALT', SAE_SECRETKEY ));
define('NONCE_SALT',       hash_hmac('sha1', SAE_ACCESSKEY . 'NONCE_SALT', SAE_SECRETKEY ));

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'w352_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
// define('WPLANG', '');
define('WPLANG', 'zh_CN');

//USE SINAAPP DOMAIN OR USE OWN DOMAIN
//define('BIND_DOMAIN', 'yourdoamain.com');
define('BIND_DOMAIN', false);

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
