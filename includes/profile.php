<?php

/**
 * @param object $user The user object currently being edited.
 */
function uri_teams_add_teams_section_to_profile( $user ) {
  global $pagenow;

  $tax = get_taxonomy( 'uri_teams' );

  // Make sure the user can assign terms of the departments taxonomy before proceeding.
  if ( ! _uri_teams_can_update_profile() ) {
    return;
  }

  /* Get the terms of the 'departments' taxonomy. */
  $terms = get_terms( 'uri_teams', array( 'hide_empty' => false ) );

  ?>

  <h3><?php _e( 'Teams' ); ?></h3>

  <table class="form-table">

    <tr>
      <th><label for="uri_teams"><?php _e( 'Teams' ); ?></label></th>

      <td><?php

      /* If there are any departments terms, loop through them and display checkboxes. */
      if ( !empty( $terms ) ) {

        foreach ( $terms as $term ) { 
	        $id = 'uri-teams-' . esc_attr( $term->slug );
        ?>
          <label for="<?php echo $id; ?>">
            <input type="checkbox" name="uri_teams[]" id="<?php echo $id; ?>" value="<?php echo $term->slug; ?>" <?php
            	if ( $pagenow !== 'user-new.php' ) {
            		checked( true, is_object_in_term( $user->ID, 'uri_teams', $term->slug ) );
            	}
            ?>>
            <?php echo $term->name; ?>
          </label><br/>
        <?php
        }
      }

      /* If there are no departments terms, display a message. */
      else {
        _e( 'There are no teams available.' );
      }

      ?></td>
    </tr>

  </table>
<?php }

add_action( 'show_user_profile', 'uri_teams_add_teams_section_to_profile' );
add_action( 'edit_user_profile', 'uri_teams_add_teams_section_to_profile' );
add_action( 'user_new_form', 'uri_teams_add_teams_section_to_profile' );



/**
 * @param int $user_id The ID of the user to save the terms for.
 */
function uri_teams_save_teams_in_profile( $user_id ) {

  $tax = get_taxonomy( 'uri_teams' );

  // Make sure the user can assign terms of the departments taxonomy before proceeding.
  if ( ! _uri_teams_can_update_profile() ) {
    return;
  }

  $term = $_POST['uri_teams'];

  /* Sets the terms (we're just using a single term) for the user. */
  $ok = wp_set_object_terms( $user_id, $term, 'uri_teams', false);
  
  clean_object_term_cache( $user_id, 'uri_teams' );
}
add_action( 'personal_options_update', 'uri_teams_save_teams_in_profile' );
add_action( 'edit_user_profile_update', 'uri_teams_save_teams_in_profile' );
add_action( 'user_register', 'uri_teams_save_teams_in_profile' );


/**
 * Adds a column to the users list.
 * @param arr $columns are the columns in the users table
 * @return arr
 */
function uri_teams_modify_user_table( $columns ) {
	if ( ! _uri_user_can_update() ) { 
		return $columns; 
	}
  $new_columns = array();
  
  foreach($columns as $key => $value) {
    if ( 'posts' === $key ) {
    	// add the new column just before "Posts"
      $new_columns['uri_teams'] = 'Teams';
    }
    $new_columns[$key] = $value;
  }
  return $new_columns;

}
add_filter( 'manage_users_columns', 'uri_teams_modify_user_table' );

/**
 * Populates new column in the users list
 * @param arr $value are the columns in the users table
 * @param arr $column_name are the columns in the users table
 * @param arr $user_id are the columns in the users table
 * @return str
 */
function uri_teams_modify_user_table_row( $value, $column_name, $user_id ) {
	if ( ! _uri_teams_can_update_profile() ) { 
		return $value; 
	}
		
	if ( 'uri_teams' === $column_name ) {
		$value = '';
		$teams = _uri_teams_get_teams_for_user( $user_id );
		if( is_array( $teams ) ) {
			$t = array();
			foreach( $teams as $team ) {
				$t[] = $team->name;
			}
			$value = implode( ',', $t );
		}
	}
	return $value;
}
add_filter( 'manage_users_custom_column', 'uri_teams_modify_user_table_row', 10, 3 );





/**
 * Prevents people from registering uri_teams as a username.
 * @param string $username The username of the user before registration is complete.
 */
function uri_teams_disable_uri_teams_username( $username ) {
  if ( 'uri_teams' === $username ) {
    $username = '';
  }
  return $username;
}
add_filter( 'sanitize_user', 'uri_teams_disable_uri_teams_username' );


/**
 * Checks if the current user can update the new teams settings.
 * @return bool
 */
function _uri_teams_can_update_profile() {
	// only show the field for users who can activate the plugin
	return current_user_can( 'activate_plugins' );
}