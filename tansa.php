<?php
/**
 * Plugin Name: Tansa
 * Plugin URI: "https://wordpress.org/plugins/tansa/"
 * Version: 5.0.1.19
 * Author: Tansa Systems AS
 * Author URI: https://www.tansa.com
 * Description:TANSA IS AN ADVANCED text proofing system that can process thousands of words per second. Not only will it correct nearly all spelling, usage, style, punctuation and hyphenation errors in the blink of an eye, it also ensures that everyone in your organization follows a common set of rules.
 * License: GPLv2 or later */

$settingsSectionId = 'settings_section';
$settingsMenuSlugId = 'settings_page_slug';
$serverUrlSettingsFieldId = 'tansa_server_url';
$licenseKeySettingsFieldId = 'tansa_license_key';
$readUserNameOptionSettingsFieldId = 'tansa_user_name_option';
// $tansaDevServerURL = 'https://d02.tansa.com/';
$emailOptionValue = 'email'; // user_email
$userNameOptionValue = 'username'; // user_login
$showFloatingMenuSettingsFieldId = 'tansa_show_floating_menu';
$tansaPluginVersion = '5.0.1.19';
$shortSystemName = '';
$uiLangaugeStringXML = array();

/*
	Sends extension info to plugin.
*/
function register_tansa_extension_info() {
	global $tansaPluginVersion, $serverUrlSettingsFieldId, $licenseKeySettingsFieldId, $readUserNameOptionSettingsFieldId, $tansaDevServerURL, $userNameOptionValue, $showFloatingMenuSettingsFieldId, $uiLangaugeStringXML, $shortSystemName;

	$tansaFolderName = 'Tansa';
	$licenseKey =  get_option($licenseKeySettingsFieldId);
	$tansaServerURL = get_option($serverUrlSettingsFieldId);
	$readUserNameOption = get_option($readUserNameOptionSettingsFieldId);
	$showFloatingTansaMenu = get_option($showFloatingMenuSettingsFieldId);

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$tansaIniFilePath = getenv('programdata') . DIRECTORY_SEPARATOR . $tansaFolderName . DIRECTORY_SEPARATOR . 'TS4.ini';
		if (file_exists ($tansaIniFilePath)) {
			$tansaIniFileData = parse_ini_file($tansaIniFilePath, true);
			$licenseKey = $tansaIniFileData['License']['key'];
			$tansaServerURL = reset($tansaIniFileData['WebClient']);
		}
	}

	// if(empty($tansaServerURL)){
	// 	$tansaServerURL = $tansaDevServerURL;
	// }

	$current_user = wp_get_current_user();
	$wpUserId = $current_user->data->user_email; // default is email address.
	if($readUserNameOption == $userNameOptionValue){
		$wpUserId = $current_user -> data -> user_login;
	}

	wp_register_script('register_tansa_extension_info', null);
	$variables = array (
		'wpVersion' => get_bloginfo('version'),
		'version' => $tansaPluginVersion,
		'parentAppLangCode' => get_locale(),
		'wpUserId' => $wpUserId,
		'licenseKey' => $licenseKey,
		'tansaServerURL' => $tansaServerURL,
		'showFloatingTansaMenu' => $showFloatingTansaMenu,
		'uiLangaugeStrings' => $uiLangaugeStringXML,
		'shortSystemName' => "".$shortSystemName,
		'pluginPath' => plugins_url('dist', __FILE__));
	wp_localize_script('register_tansa_extension_info', 'tansaExtensionInfo', $variables);
	wp_enqueue_script('register_tansa_extension_info');
}


/*
	Adds common js files for both tinymce and gutenberg extension.
*/
function register_common_js_tansa() {
	global $tansaPluginVersion;

	wp_enqueue_script('tansa-init-js', plugins_url('/dist/javascriptapp/init.js?' . $tansaPluginVersion . '_' . uniqid(), __FILE__));
	wp_enqueue_style('tansa-main-css', plugins_url('/dist/javascriptapp/css/main.css?' . $tansaPluginVersion . '_' . uniqid(), __FILE__));
}

function check_user_access() {
	// Check if the logged in WordPress User can edit Posts or Pages
	// Check if the logged in WordPress User has the Visual Editor enabled
	if ( (! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' )) || get_user_option( 'rich_editing' ) !== 'true'  ) {
		return false;
	}
	return true;
}

/**
 * Loads Tansa plugin for gutenberg
 */
function load_tansa_gutenberg() {
	if(check_user_access()){
		$blockPath = '/dist/gutenberg/sidebar.js';
		$stylePath = '/dist/gutenberg/sidebar.css';

		register_tansa_extension_info();
		register_common_js_tansa();
		// Enqueue the bundled block JS file
		wp_enqueue_script('tansa-block-js',
			plugins_url($blockPath, __FILE__),
			[ 'wp-i18n', 'wp-blocks', 'wp-edit-post', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-plugins', 'wp-edit-post', 'wp-api'],
			filemtime(plugin_dir_path(__FILE__) . $blockPath));

		// Enqueue frontend and editor block styles
		wp_enqueue_style('tansa-block-css',
			plugins_url($stylePath, __FILE__),
			'',
			filemtime(plugin_dir_path(__FILE__) . $stylePath));
	}
}

/**
* Check if the current user can edit Posts or Pages, and is using the Visual Editor
* If so, add some filters so we can register our plugin
*/
function load_tansa_tinymce() {
	if(check_user_access()){
		register_tansa_extension_info();
		add_filter('mce_external_plugins', 'add_tansa_plugin');
		add_filter('mce_buttons', 'add_tansa_toolbar_button');
	}
}

/**
* Adds a TinyMCE plugin compatible JS file to the TinyMCE Visual Editor instance *
* @param array $plugin_array Array of registered TinyMCE Plugins
* @return array Modified array of registered TinyMCE Plugins */
function add_tansa_plugin($plugin_array) {
	register_common_js_tansa();
	$plugin_array['tansa'] = plugin_dir_url(__FILE__) . 'dist/tinymce/plugin.min.js';
	return $plugin_array;
}

/**
* Adds a button to the TinyMCE Visual Editor which the user can click
* to insert a link with a custom CSS class .  *
* @param array $buttons Array of registered TinyMCE Buttons
* @return array Modified array of registered TinyMCE Buttons */
function add_tansa_toolbar_button($buttons) {
	array_push($buttons, 'tansaButton');
	return $buttons;
}

function tansa_plugin_create_menu() {
	//create new top-level menu
	add_menu_page(getUILanguageStringValue('connectionDialog.pluginSetting'), getUILanguageStringValue('connectionDialog.settingMenu'), 'manage_options', 'tansaSettingsPage', 'tansa_plugin_settings_page', plugins_url('dist/img/TS.png', __FILE__) );
	//call register settings function
	add_action( 'admin_init', 'register_tansa_plugin_settings' );
}

function getLangCode($langCode){

	$supportedLangCodes = array("en-US", "en-GB", "en-CA", "en-AU", "fr-FR", "fr-CA", "es-ES", "de-DE", "pt-BR", "sv-SE", "da-DK", "nn-NO", "nb-NO", "nl-NL", "nl-BE");
	$langCode = str_replace("_", "-", $langCode);

	if(strlen($langCode) == 2){ // for backward compatibility of Tansa Client.

		switch(strtoupper($langCode)){

			case "EN" :
				$langCode = "en-US";
				break;
				
			case "FR" :
				$langCode = "fr-FR";
				break;
				
			case "ES" :
				$langCode = "es-ES";
				break;
				
			case "DE" :
				$langCode = "de-DE";
				break;
				
			case "SV" :
			case "SE" :
				$langCode = "sv-SE";
				break;
				
			case "NN" :
				$langCode = "nn-NO";
				break;
			
			// CLOUD-3086 : Tansa Web Admin - Bokmål is selected as the customer's GUI language, but Nynorsk is displayed when you run Tansa 
			case "NO" :
			case "NB" :
				$langCode = "nb-NO";
				break;
				
			case "DK" :
				$langCode = "da-DK";
				break;
				
			case "PO" :
			case "PT" :
				$langCode = "pt-BR";
				break;
				
			case "NL" :
				$langCode = "nl-NL";
				break;
		}
	}

	if(!in_array($langCode, $supportedLangCodes)){
		if(strlen($langCode) > 2)
			$langCode = getLangCode(substr($langCode, 0, 2));
		else 
			$langCode = "en-US";
	}
	return $langCode;
}

function getUILanguageStringsXML($langCode){
	global $shortSystemName;
	
	$langCode = getLangCode($langCode);
	$clientTextXmlData = simplexml_load_file(dirname( __FILE__ ) . "/ClientTexts.xml");
	$shortSystemName = $clientTextXmlData->general->shortSystemName['tansa'];
	$langaugeStringXML = ($clientTextXmlData->xpath("language[@code='$langCode']"))[0];
	return $langaugeStringXML; 
}

function isNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

function getUILanguageStringValue($key){
	global $uiLangaugeStringXML, $shortSystemName;

	$key = "client/".str_replace(".", "/", $key); // replace . with / for xpath
	$node = ($uiLangaugeStringXML->xpath("".$key))[0];
	$text = $node['text'];
	if(isNullOrEmptyString($text)){
		$text = $node['tansa'];
	}

	return str_replace("%SHORTSYSTEMNAME%", $shortSystemName, $text);
}

if (is_admin()) {
	global $uiLangaugeStringXML;
	$wpLangCode = get_locale(); // WordPress locale.
	$uiLangaugeStringXML = getUILanguageStringsXML($wpLangCode);

	add_action('admin_menu', 'tansa_plugin_create_menu');
	// Hook scripts function into block editor hook
	add_action('enqueue_block_assets', 'load_tansa_gutenberg');
	//Hook for tinyMCE editor
	add_action('init', 'load_tansa_tinymce');
}

function register_tansa_plugin_settings() {
	//register our settings
	global $settingsSectionId, /* $tansaDevServerURL,*/ $userNameOptionValue, $emailOptionValue, $showFloatingTansaMenu;

	add_settings_section( $settingsSectionId, '', false, $GLOBALS['settingsMenuSlugId']);
	$fields = array(
        array(
            'uid' => $GLOBALS['serverUrlSettingsFieldId'],
            'label' => getUILanguageStringValue('connectionDialog.serverUrl'),
            'section' => $settingsSectionId,
            'type' => 'text',
            'options' => false,
            'placeholder' => getUILanguageStringValue('connectionDialog.serverPlaceholderText'),
            'helper' => '',
            'supplemental' => getUILanguageStringValue('connectionDialog.serverURLCommentText'),
            // 'default' => $tansaDevServerURL
			'default' => ''
		),
		array(
            'uid' => $GLOBALS['readUserNameOptionSettingsFieldId'],
            'label' => getUILanguageStringValue('connectionDialog.username'),
            'section' => $settingsSectionId,
            'type' => 'radio',
            'options' => array(
				getUILanguageStringValue('connectionDialog.emailAdd') => $emailOptionValue,
				getUILanguageStringValue('connectionDialog.username') => $userNameOptionValue,
			),
            'helper' => '',
            'supplemental' => getUILanguageStringValue('connectionDialog.usernameCommentText'),
            'default' => $emailOptionValue
        ),
		array(
            'uid' => $GLOBALS['licenseKeySettingsFieldId'],
            'label' => getUILanguageStringValue('connectionDialog.licenseKey'),
            'section' => $settingsSectionId,
            'type' => 'text',
            'options' => false,
            'placeholder' => getUILanguageStringValue('connectionDialog.licenseKeyPlaceholderText'),
            'helper' => '',
            'supplemental' => getUILanguageStringValue('connectionDialog.licenseKeyCommentText'),
            'default' => ''
        ),
		array(
            'uid' => $GLOBALS['showFloatingMenuSettingsFieldId'],
            'label' => getUILanguageStringValue('connectionDialog.showFloatingMenu'),
            'section' => $settingsSectionId,
            'type' => 'checkbox',
            'options' => array(
				'' => true
			),
			'placeholder' => '',
            'helper' => '',
			'supplemental' => getUILanguageStringValue('connectionDialog.showFloatingMenuCommentText'),
            'default' => false
        )
    );

	foreach( $fields as $field ) {
		global $settingsMenuSlugId;	
		add_settings_field( $field['uid'], $field['label'], 'field_callback', $settingsMenuSlugId, $field['section'], $field );
	
		if(!key_exists( 'tansa_server_url', $_POST ) || $_POST['tansa_server_url'] == '')
		{	
			register_setting( $settingsMenuSlugId, 'tansa_server_url', 'validation_callback');
		}
		else if(empty($_POST['tansa_show_floating_menu'])) 
		{
			$_POST['tansa_show_floating_menu'] = '0';
			register_setting($settingsMenuSlugId, $field['uid']);
		}
		else 
		{
			register_setting($settingsMenuSlugId, $field['uid']);
		}
	}
}

function validation_callback($input){
	if ( empty( $input ) ) {
        add_settings_error( 'tansa_server_url', 'invalid_field_1', getUILanguageStringValue('connectionDialog.serverUrlValidation') );
    }
    return $input;
}



function field_callback( $arguments ) {
	//printf(json_encode($arguments));
    $value = get_option( $arguments['uid'] ); // Get the current value, if there is one
    if( !$value ) { // If no value exists
        $value = $arguments['default']; // Set to our default
    }

    // Check which type of field we want
    switch( $arguments['type'] ){
		case 'text': // If it is a text field
			printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" title="%3$s" value="%4$s" class="regular-text" />', esc_attr( $arguments['uid'] ), esc_attr( $arguments['type'] ), esc_attr( isset($arguments['placeholder']) ? $arguments['placeholder'] : '' ), esc_attr( $value ) );
			break;
		case 'textarea': // If it is a textarea
			printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', esc_attr( $arguments['uid'] ), esc_attr( isset($arguments['placeholder']) ? $arguments['placeholder'] : '' ), esc_textarea( $value ));
			break;
		case 'select': // If it is a select dropdown
			if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
				$options_markup = '';
				foreach( $arguments['options'] as $key => $label ){
					$options_markup .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ));
				}
				printf( '<select name="%1$s" id="%1$s">%2$s</select>', esc_attr( $arguments['uid'] ), $options_markup );
			}
			break;
		case 'radio': // If it is a radio button
			if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
				$options_markup = '';
				foreach( $arguments['options'] as $key => $optionValue ){
					$options_markup .= sprintf('<li><input name="%1$s" type="%2$s" title="%3$s" value="%4$s" id="%4$s" %5$s /> <label for="%4$s" style="vertical-align: initial;" >%6$s</label> </li>', esc_attr( $arguments['uid'] ), esc_attr( $arguments['type'] ), esc_attr( isset($arguments['placeholder']) ? $arguments['placeholder'] : '' ), esc_attr( $optionValue ), checked($optionValue, $value, false), esc_html( $key ));
				}
				printf('<ul style="margin:0px;">%1$s</ul>', $options_markup );
			}
			break;
	        case 'checkbox': // If it is checkbox
			if( !empty ( $arguments['options']) && is_array( $arguments['options'])) {
				$options_markup = '';
				foreach( $arguments['options'] as $key => $optionValue) {
					$options_markup .= sprintf('<li><input name="%1$s" type="%2$s" title="%3$s" value="%4$s" id="%4$s" %5$s /> <label for="%4$s" style="vertical-align: initial;" >%6$s</label> </li>', esc_attr( $arguments['uid'] ), esc_attr( $arguments['type'] ), esc_attr( isset($arguments['placeholder']) ? $arguments['placeholder'] : '' ), esc_attr( $optionValue ), checked($optionValue, $value, false), esc_html( $key ));
				}
				printf('<ul style="margin:0px;">%1$s</ul>', $options_markup );
			}
			break;
	}

    // If there is help text
    if( $helper = $arguments['helper'] )
    {
	printf( '<span class="helper"> %s</span>', esc_html( $helper ) ); // Show it
    }

    // If there is supplemental text
    if( $supplimental = $arguments['supplemental'] ){
		printf( '<p class="description">%s</p>', esc_html( $supplimental ) ); // Show it
    }
}

function tansa_plugin_settings_page() {
	include 'settings.php';
}
