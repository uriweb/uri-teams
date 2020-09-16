<?php



function uri_teams_add_roles_on_plugin_activation() {

	foreach ( uri_teams_roles() as $r ) {
		add_role( $r['slug'], $r['name'], $r['caps'] );
	}	

	uri_teams_add_caps();


}