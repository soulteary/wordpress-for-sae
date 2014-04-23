<?php
/**
 * Upgrade WordPress Page.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * We are upgrading WordPress.
 *
 * @since 1.5.1
 * @var bool
 */
define( 'WP_INSTALLING', true );

//升级需要清空MC
$sy['mc'] = memcache_init();
if(!$sy['mc']){
    die('<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MC出错了</title>
</head>
<body>
<span style="font-size: 16px;color: #D30022;margin: 40px auto;display: block;text-align: center;">建立MC连接时出错，请访问SAE在线管理平台开启Mc服务：<a href="http://wp4cloud.sinaapp.com/" style="color: #3B84C7;">支持文档</a>。</span>
<script type="text/javascript">
(function(){
var loc = window.location;
var url = loc.hostname;
var ret = false;
ret = confirm("如果你不知道该如何解决问题，可以点击“是”跳转WordPress 3.9 FAQ获取问题解决方法。");
if(ret===true){
    window.location.href = "http://www.soulteary.com/2014/04/21/wordpress-for-sae-3-9-doc.html#MC-ERROR";
}
})();
</script>
</body>
</html>');
}else{
    $sy['mc']->flush();
}


/** Load WordPress Bootstrap */
require( dirname( dirname( __FILE__ ) ) . '/wp-load.php' );

nocache_headers();

timer_start();
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

delete_site_transient('update_core');

if ( isset( $_GET['step'] ) )
	$step = $_GET['step'];
else
	$step = 0;

// Do it. No output.
if ( 'upgrade_db' === $step ) {
	wp_upgrade();
	die( '0' );
}

if(!validate_current_theme()){
    $themes = wp_get_themes();
    $theme_count = count($themes);
    if($theme_count){
        $theme_item = array_keys($themes);
        switch_theme($theme_item[rand(0, $theme_count-1)]);
    }
}

$step = (int) $step;

$php_version    = phpversion();
$mysql_version  = $wpdb->db_version();
$php_compat     = version_compare( $php_version, $required_php_version, '>=' );
if ( file_exists( WP_CONTENT_DIR . '/db.php' ) && empty( $wpdb->is_mysql ) )
	$mysql_compat = true;
else
	$mysql_compat = version_compare( $mysql_version, $required_mysql_version, '>=' );

@header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>" />
	<meta name="mc-has-flush" content="@soulteary <?php echo microtime()?>">
	<title><?php _e( 'WordPress &rsaquo; Update' ); ?></title>
	<?php
	wp_admin_css( 'install', true );
	wp_admin_css( 'ie', true );
	?>
</head>
<body class="wp-core-ui">
<h1 id="logo"><a href="<?php echo esc_url( __( 'https://wordpress.org/' ) ); ?>"><?php _e( 'WordPress' ); ?></a></h1>

<?php if ( get_option( 'db_version' ) == $wp_db_version || !is_blog_installed() ) : ?>

<h2><?php _e( 'No Update Required' ); ?></h2>
<p><?php _e( 'Your WordPress database is already up-to-date!' ); ?></p>
<p class="step"><a class="button button-large" href="<?php echo get_option( 'home' ); ?>/"><?php _e( 'Continue' ); ?></a></p>

<?php elseif ( !$php_compat || !$mysql_compat ) :
	if ( !$mysql_compat && !$php_compat )
		printf( __('You cannot update because <a href="http://codex.wordpress.org/Version_%1$s">WordPress %1$s</a> requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.'), $wp_version, $required_php_version, $required_mysql_version, $php_version, $mysql_version );
	elseif ( !$php_compat )
		printf( __('You cannot update because <a href="http://codex.wordpress.org/Version_%1$s">WordPress %1$s</a> requires PHP version %2$s or higher. You are running version %3$s.'), $wp_version, $required_php_version, $php_version );
	elseif ( !$mysql_compat )
		printf( __('You cannot update because <a href="http://codex.wordpress.org/Version_%1$s">WordPress %1$s</a> requires MySQL version %2$s or higher. You are running version %3$s.'), $wp_version, $required_mysql_version, $mysql_version );
?>
<?php else :
switch ( $step ) :
	case 0:
		$goback = wp_get_referer();
		$goback = esc_url_raw( $goback );
		$goback = urlencode( $goback );
?>
<h2><?php _e( 'Database Update Required' ); ?></h2>
<p><?php _e( 'WordPress has been updated! Before we send you on your way, we have to update your database to the newest version.' ); ?></p>
<p><?php _e( 'The update process may take a little while, so please be patient.' ); ?></p>
<p class="step"><a class="button button-large" href="upgrade.php?step=1&amp;backto=<?php echo $goback; ?>"><?php _e( 'Update WordPress Database' ); ?></a></p>
<?php
		break;
	case 1:
		wp_upgrade();

			$backto = !empty($_GET['backto']) ? wp_unslash( urldecode( $_GET['backto'] ) ) : __get_option( 'home' ) . '/';
			$backto = esc_url( $backto );
			$backto = wp_validate_redirect($backto, __get_option( 'home' ) . '/');
?>
<h2><?php _e( 'Update Complete' ); ?></h2>
	<p><?php _e( 'Your WordPress database has been successfully updated!' ); ?></p>
	<p class="step"><a class="button button-large" href="<?php echo $backto; ?>"><?php _e( 'Continue' ); ?></a></p>

<!--
<pre>
<?php printf( __( '%s queries' ), $wpdb->num_queries ); ?>

<?php printf( __( '%s seconds' ), timer_stop( 0 ) ); ?>
</pre>
-->

<?php
		break;
endswitch;
endif;
?>
</body>
</html>
