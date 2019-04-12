<?php



function uri_teams_delete_roles_on_plugin_deactivation() {

	foreach( uri_teams_roles() as $role ) {
		remove_role( $role['slug'] );
	}
	
	uri_teams_remove_caps();
		
}


// @todo: destroy taxonomies on uninstall / delete?