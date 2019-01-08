<?php
/**
 *
 */
namespace oiyamaps;


/* Display a notice that can be dismissed */
function oiym_admin_notice()
{
	global $current_user ;
	$user_id = $current_user->ID;
	/* Check that the user hasn't already clicked to ignore the message */
	if ( ! get_user_meta($user_id, 'oiym_ignore_notice') && current_user_can( 'manage_options' ) )
	{
?>
	<div class="updated" style="position: relative;">
		<p>
			<?php printf(__('Check out the <a href="%1$s">option page</a> of Oi Yandex.Maps for WordPress plugin.','oi-yamaps'), 'options-general.php?page=oiym-setting-admin'); ?>
		</p>
		<a href="<?php print oi_yamaps_same_page( 'oiym_nag_ignore=0' ); ?>" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e( 'Hide Notice', 'oi-yamaps' ); ?></span>
		</a>
	</div>
<?php
	}
}
add_action('admin_notices', __NAMESPACE__.'\oiym_admin_notice');

function oiym_nag_ignore()
{
	global $current_user;
	$user_id = $current_user->ID;
	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset($_GET['oiym_nag_ignore']) && $_GET['oiym_nag_ignore'] == '0' )
	{
		add_user_meta($user_id, 'oiym_ignore_notice', 'true', true);
	}
}
add_action('admin_init', __NAMESPACE__.'\oiym_nag_ignore');

// eof
