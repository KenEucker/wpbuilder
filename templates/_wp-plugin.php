<?php
/*
Plugin Name: ##BIG NAME##
Plugin URI: ##URI##
Description: ##DESCRIPTION##
Version: ##VERSION##
Author: ##AUTHOR##
Author Email: ##EMAIL##
License:

  Copyright ##YEAR## ##AUTHOR## (##EMAIL##)

  ##LICENSE##
  
*/

if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

define( '##CAPITALIZED##_VERSION', '##VERSION##' );
define( '##CAPITALIZED##_RELEASE_DATE', date_i18n( 'F j, Y', '1397937230' ) );
define( '##CAPITALIZED##_DIR', plugin_dir_path( __FILE__ ) );
define( '##CAPITALIZED##_URL', plugin_dir_url( __FILE__ ) );


if (!class_exists("##CLASSNAME##")) :

class ##CLASSNAME## {
	var $settings, $options_page;
	
	function __construct() {	

		if (is_admin()) {
			// Load example settings page
			if (!class_exists("##CLASSNAME##_Settings"))
				require(##CAPITALIZED##_DIR . '##PLUGIN_NAME##-settings.php');
			$this->settings = new ##CLASSNAME##_Settings();	
		}
		
		add_action('init', array($this,'init') );
		add_action('admin_init', array($this,'admin_init') );
		add_action('admin_menu', array($this,'admin_menu') );
		add_action('admin_menu', array($this,'create_menus') );
		
		register_activation_hook( __FILE__, array($this,'activate') );
		register_deactivation_hook( __FILE__, array($this,'deactivate') );
	}

	function network_propagate($pfunction, $networkwide) {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function 
			// for each blog id
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					call_user_func($pfunction, $networkwide);
				}
				switch_to_blog($old_blog);
				return;
			}	
		} 
		call_user_func($pfunction, $networkwide);
	}

	function activate($networkwide) {
		$this->network_propagate(array($this, '_activate'), $networkwide);
	}

	function deactivate($networkwide) {
		$this->network_propagate(array($this, '_deactivate'), $networkwide);
	}

	/*
		Enter our plugin activation code here.
	*/
	function _activate() {
		//$this->create_posttypes();	
	}

	/*
		Enter our plugin deactivation code here.
	*/
	function _deactivate() {}
	

	/*
		Load language translation files (if any) for our plugin.
	*/
	function init() {
		load_plugin_textdomain( '##CLASSNAME##', ##CAPITALIZED##_DIR . 'lang', 
							   basename( dirname( __FILE__ ) ) . '/lang' );
	}

	function create_menus()
	{
		$menu = '##SHORTNAME##-menu';

		$plugin_dir = "##PLUGIN_NAME##";
		$mainfile = str_replace("localhost/","",plugins_url('/##SHORTNAME_PLURAL##.php', __FILE__ ));
		$mainfile = $plugin_dir.'/##SHORTNAME_PLURAL##.php';
		$menu_type = "edit_others_posts";
  		add_menu_page( '##BIGSHORTNAME_PLURAL##', '##BIGSHORTNAME_PLURAL##', $menu_type, $mainfile ); 

  		///TODO: add foreach that compiles this from a variable
	    /*add_submenu_page($mainfile, 'Targets', 'Targets', $menu_type, $plugin_dir.'/targets.php' );
	    add_submenu_page($mainfile, 'Actions', 'Actions', $menu_type, $plugin_dir.'/actions.php' );
	    add_submenu_page($mainfile, 'Types', 'Types', $menu_type, $plugin_dir.'/types.php' );
	    add_submenu_page($mainfile, 'Sources', 'Sources', $menu_type, $plugin_dir.'/sources.php' );*/
	    ##PLUGIN_ADMIN_MENUS##
	}

	function create_posttypes()
	{

		$posttype = "##BIGSHORTNAME##";
		$posttype_plural = "##BIGSHORTNAME_PLURAL##";
		$posttype_plural_lower = strtolower($posttype_plural);
		$posttypespace = '##DOMAIN##_##SHORTNAME##';

		$labels = array(
		    'name'               => _x( $posttype, 'post type general name' ),
		    'singular_name'      => _x( $posttype, 'post type singular name' ),
		    'add_new'            => _x( 'Add New', 'book' ),
		    'add_new_item'       => __( 'Add New '.$posttype ),
		    'edit_item'          => __( 'Edit '.$posttype ),
		    'new_item'           => __( 'New '.$posttype ),
		    'all_items'          => __( 'All '.$posttype_plural ),
		    'view_item'          => __( 'View '.$posttype ),
		    'search_items'       => __( 'Search '.$posttype_plural ),
		    'not_found'          => __( 'No '.$posttype_plural_lower.' found' ),
		    'not_found_in_trash' => __( 'No '.$posttype_plural_lower.' found in the Trash' ), 
		    'parent_item_colon'  => '',
		    'menu_name'          => $posttype_plural
		  );
		$capabilities = array(
			'edit_post'           => 'edit_'.$posttypespace,
			'read_post'           => 'read_'.$posttypespace,
			'delete_post'         => 'delete_'.$posttypespace,
			'edit_posts'          => 'edit_'.$posttypespace,
			'edit_others_posts'   => 'edit_others_'.$posttypespace,
			'publish_posts'       => 'publish_'.$posttypespace,
			'read_private_posts'  => 'read_private_'.$posttypespace,
		);
		$new_posttype = array(
			'label'               => __( '##SHORTNAME##', $posttypespace ),
			'description'         => __( '##TYPE_DESCRIPTION##', $posttypespace ),
			'labels'              => $labels,
			'supports'            => array( 'comments', 'trackbacks', 'custom-fields', 'page-attributes', ),
			'taxonomies'          => array( $posttype_plural ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'query_var'           => $posttypespace,
			'capabilities'        => $capabilities,
		);
  		register_post_type( '##SHORTNAME##', $new_posttype );
	}

	function admin_init() {
	}

	function admin_menu() {
	  add_submenu_page('options-general.php', '##PLUGIN_NAME##', '##BIGSHORTNAME_PLURAL##', 'manage_options', 'wpautop-control-menu', 'wpautop_control_options');
	}


	/*
		Example print function for debugging. 
	*/	
	function print_example($str, $print_info=TRUE) {
		if (!$print_info) return;
		__($str . "<br/><br/>\n", '##CLASSNAME##' );
	}
	
	function javascript_redirect($location) {
		// redirect after header here can't use wp_redirect($location);
		?>
		  <script type="text/javascript">
		  <!--
		  window.location= <?php echo "'" . $location . "'"; ?>;
		  //-->
		  </script>
		<?php
		exit;
	}

} // end class
endif;

// Initialize our plugin object.
global $##CLASSNAME##;
if (class_exists("##CLASSNAME##") && !$##CLASSNAME##) {
    $##CLASSNAME## = new ##CLASSNAME##();	
}	
?>