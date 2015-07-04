<?php
/**
 * Plugin Name:       Student and Teacher Role
 * Plugin URI:        https://github.com/PhylomonTCG/Student-and-teacher-role
 * Description:       Allows you to have a student and teacher roles in this install
 * Version:           1.0.0
 * Author:            Enej Bajgoric
 *
 * @author    Enej Bajgoric
 * @license   GPL-2.0+
 */


class Student_and_Teacher_Role {

	static function init(){
		$user_info = wp_get_current_user();

		$data = get_user_by( 'email', $user_info->user_email );

		add_action( 'edit_user_profile_update', array( 'Student_and_Teacher_Role', 'update_user_ability' ), 10, 1 ) ;
		add_action( 'personal_options_update', array( 'Student_and_Teacher_Role', 'update_user_ability' ), 10, 1 ) ;
		add_action( 'personal_options',  array( 'Student_and_Teacher_Role' , 'edit_user_ability' ), 10, 1 );
		add_action( 'manage_users_columns', array( 'Student_and_Teacher_Role', 'add_column' ) );
		add_action( 'manage_users_custom_column', array( 'Student_and_Teacher_Role' , 'show_ability' ), 10, 3);
	}

	static function edit_user_ability( $profileuser ) {

		if( current_user_can( 'edit_users' ) ) {
			$checked = self::can_manage_diy_cards( $profileuser->ID );
			if( !class_exists( 'c2c_AllowMultipleAccounts' )  ) {

				echo "<tr><td colspan='2'><strong>Please make sure that you have <em>Allow Multiple Accounts</em>  Plugin installed </strong> | <a href='http://wordpress.org/plugins/allow-multiple-accounts/' target='_blank'> Download it here</a>.</td></tr>";
				if( !$checked )
				return;
			}

			?>
		<tr >
			<th scope="row">Manage DIY Cards</th>
			<td><fieldset><legend class="screen-reader-text"><span>User can edit/delete other users with the same email address cards</span></legend>
			<label for="edit_user_diy_cards">
			<input type="checkbox" <?php checked($checked); ?>  value="1" id="edit_user_diy_cards" name="edit_user_diy_cards" />
			User can edit/delete DIY Cards of others that have the same email address.
			</label><br>
			</fieldset>
			</td>
		</tr>
		<?php
		}
	}

	static function update_user_ability( $user_id ){
		$set_meta = ( isset( $_POST['edit_user_diy_cards']) && $_POST['edit_user_diy_cards'] == '1' ? 1 : false );

		add_user_meta( $user_id, 'edit_user_diy_cards', $set_meta, true ) || update_user_meta( $user_id, 'edit_user_diy_cards', $set_meta );

	}

	static function can_manage_diy_cards( $user_id ){

		return get_user_meta( $user_id, 'edit_user_diy_cards', true );
	}

	static function show_ability( $row, $column_name, $user_id ){

		if( "diy-cards" == $column_name){
			return ( self::can_manage_diy_cards( $user_id ) ? 'Yes': '-' );
		}
		return $row;
	}
	static function add_column( $columns ){
		$columns['diy-cards'] = 'Manage Cards';
		return $columns;
	}
}

add_action('init', array( 'Student_and_Teacher_Role', 'init' ) );


function STR_current_user_can_manage_diy_card( $card_author ){

	if( get_user_meta( get_current_user_id(), 'edit_user_diy_cards', true ) || current_user_can( 'remove_users' )   ) {
		$authors =  STR_get_all_authors();

		if( is_array( $authors ) && in_array( $card_author, $authors ) ) {
			return true;
		}
	}
	return false;
}

function STR_get_all_authors( $user_email = null ){
	if ( ! $user_email ) {
		$user_data = wp_get_current_user();
		$user_email = $user_data->user_email;
	}

	if ( class_exists( 'c2c_AllowMultipleAccounts' ) ){
		if ( method_exists( 'c2c_AllowMultipleAccounts', 'get_instance' ) ) {
			$instance = c2c_AllowMultipleAccounts::get_instance();
		} else {
			$instance = c2c_AllowMultipleAccounts::$instance;
		}

		$users = $instance->get_users_by_email( $user_email );
		$user_ids = array();
		foreach( $users as $user) {
			$user_ids[] = $user->ID;
		}
		return $user_ids;
	}
	return array( $user_data->id );
}

