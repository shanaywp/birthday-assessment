<?php 
/**
 * Settings Helper Class
 */	

class Realm_Wishes_Settings
{
	public static function settings_init(){

		add_action( 'admin_menu', array( 'Realm_Wishes_Settings','realm_api_add_admin_menu' ) );

		add_action( 'admin_init', array( 'Realm_Wishes_Settings','realm_api_settings_init' ) );

		add_action( 'admin_enqueue_scripts', array( 'Realm_Wishes_Settings','realm_api_settings_post_chek' ) );

		add_action("wp_ajax_send_user_birthday", array( 'Realm_Wishes_Settings','get_employee_data' ));
		
		add_action("wp_ajax_nopriv_send_user_birthday", array( 'Realm_Wishes_Settings', 'get_employee_data'));
	
	}

	public static function realm_activation_hook(){
		if(empty(get_option( 'realm_api_settings' ))){
			$options = array(
				'realm_api_text_field_0' => '' ,
				'realm_api_text_field_1' => '' ,
				'realm_api_text_field_2' => '' ,
				'realm_api_text_field_3' => '' ,
				'realm_data_birthday_email_sent' => '',
			);
			update_option( 'realm_api_settings' , $options , 'yes');
		}
	}

	public static function realm_deactivation_hook(){
		$timestamp = wp_next_scheduled( 'minute_remainder' );
    	wp_unschedule_event( $timestamp, 'minute_remainder' );
	}

	public static function realm_api_add_admin_menu(  ) {
		// add_options_page( 'Realm Whishes Settings', 'Realm Whishes Settings', 'manage_options', 'settings-api-page', array( 'Realm_Wishes_Settings','realm_api_options_page' ) );
		add_menu_page( 'Realm Whishes Settings', 'Realm Whishes Settings', 'manage_options', 'settings-api-page', array( 'Realm_Wishes_Settings','realm_api_options_page'), 'dashicons-chart-pie');
	}

	public static function realm_api_settings_init(  ) {
	    register_setting( 'realmPlugin', 'realm_api_settings' );
	    add_settings_section(
	        'realm_api_realmPlugin_section',
	        __( 'Settings for Realm Whishes', 'realm' ),array( 'Realm_Wishes_Settings','realm_api_settings_section_callback'),
	        'realmPlugin'
	    );

		/**
		 * @see We can extend this function by Registring new Field.
		 */
		add_settings_field(
	        'realm_api_text_field_1',
	        __( 'Send Email To', 'realm' ),array( 'Realm_Wishes_Settings','realm_to_email_field_callback'),
	        'realmPlugin',
	        'realm_api_realmPlugin_section'
		);
		add_settings_field(
	        'realm_api_text_field_2',
	        __( 'Email Subject', 'realm' ),array( 'Realm_Wishes_Settings','realm_to_email_subject_callback'),
	        'realmPlugin',
	        'realm_api_realmPlugin_section'
	    );
		add_settings_field(
	        'realm_api_text_field_0',
	        __( 'Birthday Email Template', 'realm' ),array( 'Realm_Wishes_Settings','realm_birthday_email_template_callback'),
	        'realmPlugin',
	        'realm_api_realmPlugin_section'
		);

		add_settings_field(
	        'realm_api_text_field_3',
	        __( 'Test For this Date', 'realm' ),array( 'Realm_Wishes_Settings','realm_test_date_callback'),
	        'realmPlugin',
	        'realm_api_realmPlugin_section'
		);
	}

	/**
	 * This function Used For the Birthday Mail Template 
	 */
	public static function realm_birthday_email_template_callback(  ) {
	    $options = get_option( 'realm_api_settings' );
	    $settings_value = empty($options['realm_api_text_field_0']) ? '' : $options['realm_api_text_field_0'] ;
	    ?>
		<p>use <code>%{names}%</code> to show names </p>
		<br/>
		<textarea name="realm_api_settings[realm_api_text_field_0]" id="realm_api_text_field_0" cols="80" rows="20" value="<?php echo $settings_value?>" required="true"><?php echo $settings_value?></textarea>
	    <?php
	}

	public static function realm_to_email_field_callback(  ) {
	    $options = get_option( 'realm_api_settings' );
	    $settings_value = empty($options['realm_api_text_field_1']) ? '' : $options['realm_api_text_field_1'];
	    ?>
		<input name="realm_api_settings[realm_api_text_field_1]" id="realm_api_text_field_1" type="text" value="<?php echo $settings_value?>" required="true">
	    <?php
	}

	
	public static function realm_to_email_subject_callback(  ) {
	    $options = get_option( 'realm_api_settings' );
	    $settings_value = empty($options['realm_api_text_field_2']) ? '' : $options['realm_api_text_field_2'];
	    ?>
		<input name="realm_api_settings[realm_api_text_field_2]" id="realm_api_text_field_2" type="text" value="<?php echo $settings_value?>" required="true">
	    <?php
	}

	public static function realm_test_date_callback(  ) {
	    $options = get_option( 'realm_api_settings' );
	    $settings_value = empty($options['realm_api_text_field_3']) ? '' : $options['realm_api_text_field_3'];
	    ?>
		<input name="realm_api_settings[realm_api_text_field_3]" id="realm_api_text_field_3" type="date" value="<?php echo $settings_value?>">
		<button class="button primary reset_date">Reset date</button> <p>(This field is to modify and set a particular date as current date, for testing purpose. <br/>If you want the script to take actual current date automatically, click <strong>Reset Date</strong> button and then click <strong>Save Changes</strong> button.)<p>
	    <?php
	}
	
	public static function get_employee_data(){
		$options = get_option( 'realm_api_settings' );
		$employee_list = json_decode(
			wp_remote_retrieve_body( 
				wp_remote_get( 
					REALM_EMPLOYEES_API,  
					array( 'headers' => array('Content-Type' => 'application/json') )
				) 
			)
		);
		$do_not_send_birthday_list = json_decode(
			wp_remote_retrieve_body( 
				wp_remote_get( REALM_DO_NOT_SEND_BIRTHDAY_EMAIL_API ) 
			)					
		);

		$birthday_whish_keys = [];

		$current_date = date( 'm-d' );
		if( ! empty( $options['realm_api_text_field_3'] ) ){
			$option_date = explode('-', $options['realm_api_text_field_3'] );	
			$current_date = $option_date[1].'-'.$option_date[2];
		}

		/**
		 * Run This the Filter for user Which have birthday in Leap years.
		 */
		$check_leap_birth_user = true;
		
		/**
		 * Checking if today is not leap year and 29 Feb 2020 
		 */
		$is_leap_year = Realm_Wishes_Settings::check_year( date('Y') );

		if( ( ! $is_leap_year ) && $current_date === '02-28' ){
			$check_leap_birth_user = false;
		}

		foreach( $employee_list as $key => $employee ){

			$birthdate = date( 'm-d', strtotime( $employee->dateOfBirth ) );

			if ( in_array( $employee->id, $do_not_send_birthday_list ) ) {
				continue;
			} else if ( $employee->employmentEndDate ) {
				continue;
			} else if ( $employee->employmentStartDate == '') {
				continue;
			} else if ( $check_leap_birth_user && $birthdate == '02-29') {
				$birthday_whish_keys[] = $key;
			} else if ( $current_date == $birthdate ){
				$birthday_whish_keys[] = $key;
			} else {
				/**Nothing */
			}

		}

		if( !empty ($birthday_whish_keys) ){
			foreach( $birthday_whish_keys as $key ){
				$birthday_whish[] = $employee_list[$key];
			}
			Realm_Wishes_Settings::send_mail($birthday_whish);
		}
	}

	private static function check_year($year) {
		$year = (int)$year;
		if ($year % 400 == 0) 
			return true; 
	
		if ($year % 100 == 0) 
			return false; 
	
		if ($year % 4 == 0) 
			return true; 
		return false; 
	} 

	/**
	 * Send Email Functions For the Whishes.
	 */

	private static function send_mail( $employee_data, $mail_type = 'birthday'){
		$options = get_option( 'realm_api_settings' );
		$employee_names = [];

		$realm_data_birthday_email_sent = get_option('realm_data_birthday_email_sent');
		print_r($realm_data_birthday_email_sent);
		if( ! is_array($realm_data_birthday_email_sent) ){
			$realm_data_birthday_email_sent[] = $realm_data_birthday_email_sent;
		}
		foreach( $employee_data as $employee ){
			if ( ! in_array( $employee->id, $realm_data_birthday_email_sent ) ) {
				$employee_names[] = $employee->name.' '.$employee->lastname;
				$employee_ids[] = $employee->id;
			}
		}
		switch( $mail_type ){
			case 'birthday':
				$content = ( $options['realm_api_text_field_0'] ) ? $options['realm_api_text_field_0'] : '';
				$to_mail = ( $options['realm_api_text_field_1'] ) ? $options['realm_api_text_field_1'] : get_option('admin_email', true)  ;
				$subject = ( $options['realm_api_text_field_2'] ) ? $options['realm_api_text_field_2'] : '';
				/**
				 * Dynamic name "%{names}%"
				 */
				$employee_names = implode( ", ", $employee_names );
				$content = str_replace("%{names}%", $employee_names, $content );		
				$headers = [];
				$headers[] = 'Content-Type: text/html; charset=UTF-8'; 
				$headers[] = 'From: Admin <'.get_option('admin_email', true).'>';
				if( isset($employee_ids) && $status = wp_mail( $to_mail, $subject, $content, $headers,'' ) ) {
					echo '1';
					$employee_ids = array_merge($realm_data_birthday_email_sent, $employee_ids); 
					update_option( "realm_data_birthday_email_sent", $employee_ids );
					
				} else { 
					echo '0';
				}

			break;
			default:
			
			break;
		}
		

	}
	public static function realm_api_settings_section_callback(  ) {
	    // echo __( 'This Template will be used For the Email', 'realm' );
	}

	public static function realm_api_settings_post_chek(  ) {
		wp_enqueue_script( 'admin-send-mail', REALM_WHISHES_URL.'assets/birthday.js','', true );
		wp_localize_script( 'admin-send-mail', 'urlAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        

	}
	/**
	 * Main Email For rendering The Settings Of Page.
	 */
	public static function realm_api_options_page(  ) {
		?>
	    <form action='options.php' method='post'>
	        <h2>Realm Whishes All Settings Admin Page</h2>
	        <?php
	        settings_fields( 'realmPlugin' );
			do_settings_sections( 'realmPlugin' );
			echo "<table><tr><td>";
			submit_button(null, 'primary', 'submit', false );
			echo "</td><td>";
			submit_button('Send Email', 'secondary', 'send_email', false);
			echo "</td></tr></table>";
	        ?>
	    </form>
	    <?php
	}
}
 

/***
 * Cron Job Scheduling
 */
add_filter( 'cron_schedules', 'minute_remainder' );
function minute_remainder( $schedules )
{
    $schedules['minute_remainder'] = array(
            'interval'  => DAY_IN_SECONDS,
            'display'   => __( 'Every hour', 'textdomain' )
    );
    return $schedules;
}
// Schedule an action if it's not already scheduled
 if ( ! wp_next_scheduled( 'minute_remainder' ) ) {
    wp_schedule_event( time(), 'minute_remainder', 'minute_remainder' );
}
// Hook into that action that'll fire every three minutes
add_action( 'minute_remainder', 'case_remainder_hour_function' );
function case_remainder_hour_function()
{
	$employee_ids = array();
	/**
	 * Clearing First the Email sent Users After 1 day in Cron job.
	 */
	update_option( "realm_data_birthday_email_sent", $employee_ids );
	Realm_Wishes_Settings::get_employee_data();
    
}