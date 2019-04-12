<?php



function uri_teams_add_roles_on_plugin_activation() {

	if ( uri_teams_meets_dependencies() ) {
		foreach ( uri_teams_roles() as $r ) {
			add_role( $r['slug'], $r['name'], $r['caps'] );
		}	

		uri_teams_add_caps();
	}

}

/**
 * Check for plugin dependencies
 * @todo: this is a kludgy hack.  What's the proper WP way?
 */
function uri_teams_meets_dependencies() {

	require_once( ABSPATH . '/wp-admin/includes/plugin.php' ) ; // to get is_plugin_active() early
	
	if ( ! is_plugin_active ( 'wp-user-groups/wp-user-groups.php' ) ) {
		echo '<div class="notice notice-error">
			<p>Install and activate WP User Groups before activating Teams.</p>
		</div>';
		@trigger_error(__('Install and activate WP User Groups before activating Teams.', 'uri'), E_USER_ERROR);
		return FALSE;
	}
	
	return TRUE;
}