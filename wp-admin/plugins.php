<?php
/**
 * Plugins administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('activate_plugins') )
	wp_die( __( 'You do not have sufficient permissions to manage plugins for this site.' ) );

$wp_list_table = _get_list_table('WP_Plugins_List_Table');
$pagenum = $wp_list_table->get_pagenum();

$action = $wp_list_table->current_action();

$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
$s = isset($_REQUEST['s']) ? urlencode($_REQUEST['s']) : '';

// Clean up request URI from temporary args for screen options/paging uri's to work as expected.
$_SERVER['REQUEST_URI'] = remove_query_arg(array('error', 'deleted', 'activate', 'activate-multi', 'deactivate', 'deactivate-multi', '_error_nonce'), $_SERVER['REQUEST_URI']);

if ( $action ) {

	switch ( $action ) {
		case 'activate':
			if ( ! current_user_can('activate_plugins') )
				wp_die(__('You do not have sufficient permissions to activate plugins for this site.'));

			if ( is_multisite() && ! is_network_admin() && is_network_only_plugin( $plugin ) ) {
				wp_redirect( self_admin_url("plugins.php?plugin_status=$status&paged=$page&s=$s") );
				exit;
			}

			check_admin_referer('activate-plugin_' . $plugin);

			$result = activate_plugin($plugin, self_admin_url('plugins.php?error=true&plugin=' . $plugin), is_network_admin() );
			if ( is_wp_error( $result ) ) {
				if ( 'unexpected_output' == $result->get_error_code() ) {
					$redirect = self_admin_url('plugins.php?error=true&charsout=' . strlen($result->get_error_data()) . '&plugin=' . $plugin . "&plugin_status=$status&paged=$page&s=$s");
					wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugin), $redirect));
					exit;
				} else {
					wp_die($result);
				}
			}

			if ( ! is_network_admin() ) {
				$recent = (array) get_option( 'recently_activated' );
				unset( $recent[ $plugin ] );
				update_option( 'recently_activated', $recent );
			}

			if ( isset($_GET['from']) && 'import' == $_GET['from'] ) {
				wp_redirect( self_admin_url("import.php?import=" . str_replace('-importer', '', dirname($plugin))) ); // overrides the ?error=true one above and redirects to the Imports page, stripping the -importer suffix
			} else {
				wp_redirect( self_admin_url("plugins.php?activate=true&plugin_status=$status&paged=$page&s=$s") ); // overrides the ?error=true one above
			}
			exit;
			break;
		case 'activate-selected':
			if ( ! current_user_can('activate_plugins') )
				wp_die(__('You do not have sufficient permissions to activate plugins for this site.'));

			check_admin_referer('bulk-plugins');

			$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();

			if ( is_network_admin() ) {
				foreach ( $plugins as $i => $plugin ) {
					// Only activate plugins which are not already network activated.
					if ( is_plugin_active_for_network( $plugin ) ) {
						unset( $plugins[ $i ] );
					}
				}
			} else {
				foreach ( $plugins as $i => $plugin ) {
					// Only activate plugins which are not already active and are not network-only when on Multisite.
					if ( is_plugin_active( $plugin ) || ( is_multisite() && is_network_only_plugin( $plugin ) ) ) {
						unset( $plugins[ $i ] );
					}
				}
			}

			if ( empty($plugins) ) {
				wp_redirect( self_admin_url("plugins.php?plugin_status=$status&paged=$page&s=$s") );
				exit;
			}

			activate_plugins($plugins, self_admin_url('plugins.php?error=true'), is_network_admin() );

			if ( ! is_network_admin() ) {
				$recent = (array) get_option('recently_activated' );
				foreach ( $plugins as $plugin )
					unset( $recent[ $plugin ] );
				update_option( 'recently_activated', $recent );
			}

			wp_redirect( self_admin_url("plugins.php?activate-multi=true&plugin_status=$status&paged=$page&s=$s") );
			exit;
			break;
		case 'error_scrape':
			if ( ! current_user_can('activate_plugins') )
				wp_die(__('You do not have sufficient permissions to activate plugins for this site.'));

			check_admin_referer('plugin-activation-error_' . $plugin);

			$valid = validate_plugin($plugin);
			if ( is_wp_error($valid) )
				wp_die($valid);

			if ( ! WP_DEBUG ) {
				error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
			}

			@ini_set('display_errors', true); //Ensure that Fatal errors are displayed.
			// Go back to "sandbox" scope so we get the same errors as before
			function plugin_sandbox_scrape( $plugin ) {
				wp_register_plugin_realpath( WP_PLUGIN_DIR . '/' . $plugin );
				include( WP_PLUGIN_DIR . '/' . $plugin );
			}
			plugin_sandbox_scrape( $plugin );
			/** This action is documented in wp-admin/includes/plugins.php */
			do_action( "activate_{$plugin}" );
			exit;
			break;
		case 'deactivate':
			if ( ! current_user_can('activate_plugins') )
				wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));

			check_admin_referer('deactivate-plugin_' . $plugin);

			if ( ! is_network_admin() && is_plugin_active_for_network( $plugin ) ) {
				wp_redirect( self_admin_url("plugins.php?plugin_status=$status&paged=$page&s=$s") );
				exit;
			}

			deactivate_plugins( $plugin, false, is_network_admin() );
			if ( ! is_network_admin() )
				update_option( 'recently_activated', array( $plugin => time() ) + (array) get_option( 'recently_activated' ) );
			if ( headers_sent() )
				echo "<meta http-equiv='refresh' content='" . esc_attr( "0;url=plugins.php?deactivate=true&plugin_status=$status&paged=$page&s=$s" ) . "' />";
			else
				wp_redirect( self_admin_url("plugins.php?deactivate=true&plugin_status=$status&paged=$page&s=$s") );
			exit;
			break;
		case 'deactivate-selected':
			if ( ! current_user_can('activate_plugins') )
				wp_die(__('You do not have sufficient permissions to deactivate plugins for this site.'));

			check_admin_referer('bulk-plugins');

			$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
			// Do not deactivate plugins which are already deactivated.
			if ( is_network_admin() ) {
				$plugins = array_filter( $plugins, 'is_plugin_active_for_network' );
			} else {
				$plugins = array_filter( $plugins, 'is_plugin_active' );
				$plugins = array_diff( $plugins, array_filter( $plugins, 'is_plugin_active_for_network' ) );
			}
			if ( empty($plugins) ) {
				wp_redirect( self_admin_url("plugins.php?plugin_status=$status&paged=$page&s=$s") );
				exit;
			}

			deactivate_plugins( $plugins, false, is_network_admin() );

			if ( ! is_network_admin() ) {
				$deactivated = array();
				foreach ( $plugins as $plugin )
					$deactivated[ $plugin ] = time();
				update_option( 'recently_activated', $deactivated + (array) get_option( 'recently_activated' ) );
			}

			wp_redirect( self_admin_url("plugins.php?deactivate-multi=true&plugin_status=$status&paged=$page&s=$s") );
			exit;
			break;
		case 'clear-recent-list':
			if ( ! is_network_admin() )
				update_option( 'recently_activated', array() );
			break;
	}
}

$wp_list_table->prepare_items();

add_thickbox();

add_screen_option( 'per_page', array('label' => _x( 'Plugins', 'plugins per page (screen options)' ), 'default' => 999 ) );

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . __('Plugins extend and expand the functionality of WordPress. Once a plugin is installed, you may activate it or deactivate it here.') . '</p>' .
	'<p>' . sprintf(__('You can find additional plugins for your site by using the <a href="%1$s">Plugin Browser/Installer</a> functionality or by browsing the <a href="%2$s" target="_blank">WordPress Plugin Directory</a> directly and installing new plugins manually. To manually install a plugin you generally just need to upload the plugin file into your <code>/wp-content/plugins</code> directory. Once a plugin has been installed, you can activate it here.'), 'plugin-install.php', 'https://wordpress.org/plugins/') . '</p>'
) );
get_current_screen()->add_help_tab( array(
'id'		=> 'compatibility-problems',
'title'		=> __('Troubleshooting'),
'content'	=>
	'<p>' . __('Most of the time, plugins play nicely with the core of WordPress and with other plugins. Sometimes, though, a plugin&#8217;s code will get in the way of another plugin, causing compatibility issues. If your site starts doing strange things, this may be the problem. Try deactivating all your plugins and re-activating them in various combinations until you isolate which one(s) caused the issue.') . '</p>' .
	'<p>' . sprintf( __('If something goes wrong with a plugin and you can&#8217;t use WordPress, delete or rename that file in the <code>%s</code> directory and it will be automatically deactivated.'), WP_PLUGIN_DIR) . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.wordpress.org/Managing_Plugins#Plugin_Management" target="_blank">Documentation on Managing Plugins</a>') . '</p>' .
	'<p>' . __('<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

$title = __('Plugins');
$parent_file = 'plugins.php';

require_once(ABSPATH . 'wp-admin/admin-header.php');

$invalid = validate_active_plugins();
if ( !empty($invalid) )
	foreach ( $invalid as $plugin_file => $error )
		echo '<div id="message" class="error"><p>' . sprintf(__('The plugin <code>%s</code> has been <strong>deactivated</strong> due to an error: %s'), esc_html($plugin_file), $error->get_error_message()) . '</p></div>';
?>

<?php if ( isset($_GET['error']) ) :

	if ( isset( $_GET['main'] ) )
		$errmsg = __( 'You cannot delete a plugin while it is active on the main site.' );
	elseif ( isset($_GET['charsout']) )
		$errmsg = sprintf(__('The plugin generated %d characters of <strong>unexpected output</strong> during activation. If you notice &#8220;headers already sent&#8221; messages, problems with syndication feeds or other issues, try deactivating or removing this plugin.'), $_GET['charsout']);
	else
		$errmsg = __('Plugin could not be activated because it triggered a <strong>fatal error</strong>.');
	?>
	<div id="message" class="updated"><p><?php echo $errmsg; ?></p>
	<?php
		if ( !isset( $_GET['main'] ) && !isset($_GET['charsout']) && wp_verify_nonce($_GET['_error_nonce'], 'plugin-activation-error_' . $plugin) ) { ?>
	<iframe style="border:0" width="100%" height="70px" src="<?php echo 'plugins.php?action=error_scrape&amp;plugin=' . esc_attr($plugin) . '&amp;_wpnonce=' . esc_attr($_GET['_error_nonce']); ?>"></iframe>
	<?php
		}
	?>
	</div>
<?php elseif ( isset($_GET['activate']) ) : ?>
	<div id="message" class="updated"><p><?php _e('Plugin <strong>activated</strong>.') ?></p></div>
<?php elseif (isset($_GET['activate-multi'])) : ?>
	<div id="message" class="updated"><p><?php _e('Selected plugins <strong>activated</strong>.'); ?></p></div>
<?php elseif ( isset($_GET['deactivate']) ) : ?>
	<div id="message" class="updated"><p><?php _e('Plugin <strong>deactivated</strong>.') ?></p></div>
<?php elseif (isset($_GET['deactivate-multi'])) : ?>
	<div id="message" class="updated"><p><?php _e('Selected plugins <strong>deactivated</strong>.'); ?></p></div>
<?php endif; ?>

<div class="wrap">
<h2><?php echo esc_html( $title );?></h2>

<?php
/**
 * Fires before the plugins list table is rendered.
 *
 * This hook also fires before the plugins list table is rendered in the Network Admin.
 *
 * Please note: The 'active' portion of the hook name does not refer to whether the current
 * view is for active plugins, but rather all plugins actively-installed.
 *
 * @since 3.0.0
 *
 * @param array $plugins_all An array containing all installed plugins.
 */
do_action( 'pre_current_active_plugins', $plugins['all'] );
?>

<?php $wp_list_table->views(); ?>

<form method="get" action="">
<?php $wp_list_table->search_box( __( 'Search Installed Plugins' ), 'plugin' ); ?>
</form>

<form method="post" action="">

<input type="hidden" name="plugin_status" value="<?php echo esc_attr($status) ?>" />
<input type="hidden" name="paged" value="<?php echo esc_attr($page) ?>" />

<?php $wp_list_table->display(); ?>
</form>

</div>

<?php
include(ABSPATH . 'wp-admin/admin-footer.php');
