<?php
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!class_exists("##CLASSNAME##_Settings")) :

class ##CLASSNAME##_Settings {

	public static $default_settings = 
		array( 	
			  	'##PLUGIN_NAME##_db_host' => '##DBHOST##',
			  	'##PLUGIN_NAME##_db_name' => '##DBNAME##',
			  	'##PLUGIN_NAME##_db_user' => '##DBUSER##',
			  	'##PLUGIN_NAME##_db_pass' => '##DBPASS##',
			  	'##PLUGIN_NAME##_db_port' => '##DBPORT##'
				);

	var $names;
	var $pagehook, $page_id, $settings_field, $options;

	
	function __construct() {	
		$this->page_id = '##PLUGIN_NAME##';
		// This is the get_options slug used in the database to store our plugin option values.
		$this->settings_field = '##PLUGIN_NAME##_options';
		$this->options = get_option( $this->settings_field );

		add_action('admin_init', array($this,'admin_init'), 20 );
		add_action( 'admin_menu', array($this, 'admin_menu'), 20);
		$this->names = array();
	}
	
	function admin_init() {
		register_setting( $this->settings_field, $this->settings_field, array($this, 'sanitize_theme_options') );
		add_option( $this->settings_field, ##CLASSNAME##_Settings::$default_settings );
		
		
		/* 
			This is needed if we want WordPress to render our settings interface
			for us using -
			do_settings_sections
			
			It sets up different sections and the fields within each section.
		*/
		add_settings_section('##PLUGIN_NAME##_main', '',  
			array($this, 'main_section_text'), '##PLUGIN_NAME##_settings_page');

		$settings_array = array(
			'##PLUGIN_NAME##_db_host'=>'Database host',
			'##PLUGIN_NAME##_db_name'=>'Database Name',
			'##PLUGIN_NAME##_db_user' => 'Database User',
		  	'##PLUGIN_NAME##_db_pass' => 'Database Password',
		  	'##PLUGIN_NAME##_db_port' => 'Database Port',
			);
		$functions_array = array(
			'##PLUGIN_NAME##_db_host'=>'render_dbhost',
			'##PLUGIN_NAME##_db_name'=>'render_dbname',
			'##PLUGIN_NAME##_db_user' => 'render_dbuser',
		  	'##PLUGIN_NAME##_db_pass' => 'render_dbpass',
		  	'##PLUGIN_NAME##_db_port' => 'render_dbport',
			);
		$names = array_keys($settings_array);
		$this->add_setttings_fields_to($settings_array, $functions_array,'##PLUGIN_NAME##_settings_page', '##PLUGIN_NAME##_main');
	}

	function admin_menu() {
		if ( ! current_user_can('update_plugins') )
			return;
	
		// Add a new submenu to the standard Settings panel
		$this->pagehook = $page =  add_options_page(	
			__('##BIG NAME##', '##PLUGIN_NAME##'), __('##BIG NAME##', '##PLUGIN_NAME##'), 
			'administrator', $this->page_id, array($this,'render') );
		
		// Executed on-load. Add all metaboxes.
		add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

		// Include js, css, or header *only* for our settings page
		add_action("admin_print_scripts-$page", array($this, 'js_includes'));
//		add_action("admin_print_styles-$page", array($this, 'css_includes'));
		add_action("admin_head-$page", array($this, 'admin_head') );
	}

	function add_setttings_fields_to($fields, $functions, $page, $section)
	{
		foreach($fields as $key => $value)	
		{
			$callback = $functions[$key];
			#echo "add_settings_field($key, $value, array(this, $callback), $page, $section);";
			add_settings_field($key, $value, array($this, $callback), $page, $section);
		}
	}

	function admin_head() { ?>
		<style>
		.settings_page_##PLUGIN_NAME## label { display:inline-block; width: 150px; }
		</style>

	<?php }

     
	function js_includes() {
		// Needed to allow metabox layout and close functionality.
		wp_enqueue_script( 'postbox' );
	}


	/*
		Sanitize our plugin settings array as needed.
	*/	
	function sanitize_theme_options($options) {
		//$options['example_text'] = stripcslashes($options['example_text']);
		return $options;
	}


	/*
		Settings access functions.
		
	*/
	protected function get_field_name( $name ) {

		return sprintf( '%s[%s]', $this->settings_field, $name );

	}

	protected function get_field_id( $id ) {

		return sprintf( '%s[%s]', $this->settings_field, $id );

	}

	public function get_field_value( $key ) {

		return $this->options[$key];

	}
		

	/*
		Render settings page.
		
	*/
	
	function render() {
		global $wp_meta_boxes;

		$title = __('##BIG NAME##', '##PLUGIN_NAME##');
		?>
		<div class="wrap">   
			<h2><?php echo esc_html( $title ); ?></h2>
		
			<form method="post" action="options.php">
				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
				</p>
                
                <div class="metabox-holder">
                    <div class="postbox-container" style="width: 99%;">
                    <?php 
						// Render metaboxes
                        settings_fields($this->settings_field); 
                        do_meta_boxes( $this->pagehook, 'main', null );
                      	if ( isset( $wp_meta_boxes[$this->pagehook]['column2'] ) )
 							do_meta_boxes( $this->pagehook, 'column2', null );
                    ?>
                    </div>
                </div>

				<p>
				<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
				</p>
			</form>
		</div>
        
        <!-- Needed to allow metabox layout and close functionality. -->
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function ($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
	<?php }
	
	
	function metaboxes() {

		// Example metabox showing plugin version and release date. 
		// Also includes and example input text box, rendered in HTML in the info_box function
		//add_meta_box( '##PLUGIN_NAME##-version', __( 'Information', '##PLUGIN_NAME##' ), array( $this, 'info_box' ), $this->pagehook, 'main', 'high' );

		// Example metabox containing two example checkbox controls.
		// Also includes and example input text box, rendered in HTML in the condition_box function
		//add_meta_box( '##PLUGIN_NAME##-conditions', __( 'Example Conditions', '##PLUGIN_NAME##' ), array( $this, 'condition_box' ), $this->pagehook, 'main' );

		// Example metabox containing an example text box & two example checkbox controls.
		// Example settings rendered by WordPress using the do_settings_sections function.
		add_meta_box( 	'##PLUGIN_NAME##-all', 
						__( '##BIGSHORTNAME_PLURAL## Database Settings', '##PLUGIN_NAME##' ), 
						array( $this, 'do_settings_box' ), $this->pagehook, 'main' );

	}

	function do_settings_box() {
		do_settings_sections('##PLUGIN_NAME##_settings_page'); 
	}
	
	/* 
		WordPress settings rendering functions
		
		ONLY NEEDED if we are using wordpress to render our controls (do_settings_sections)
	*/
																	  
																	  
	function main_section_text() {
		echo '<p></p>';
	}

	function render_textbox($id) { 
		?>
        <input id="<?php echo $id;?>" style="width:50%;"  type="text" name="<?php echo $this->get_field_name( $id ); ?>" value="<?php echo esc_attr( $this->get_field_value( $id ) ); ?>" />	
		<?php 
	}

	function render_dbname()
	{
		$this->render_textbox('##PLUGIN_NAME##_db_name');
	}
	function render_dbhost()
	{
		$this->render_textbox('##PLUGIN_NAME##_db_host');
	}
	function render_dbuser()
	{
		$this->render_textbox('##PLUGIN_NAME##_db_user');
	}
	function render_dbpass()
	{
		$this->render_textbox('##PLUGIN_NAME##_db_pass');
	}
	function render_dbport()
	{
		$this->render_textbox('##PLUGIN_NAME##_db_port');
	}

} // end class
endif;
?>