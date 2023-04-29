<?php
	class TcPage extends TcApi
	{
		/**
			* Holds the values to be used in the fields callbacks
		*/
		public $options;
		
		/**
			* Start up
		*/
		public function __construct()
		{
			add_action( 'admin_menu', array( $this, 'tc_plugin_page' ) );
			add_action( 'admin_menu', array( $this, 'tc_list_page' ) );
			add_action( 'admin_init', array( $this, 'tc_page_init' ) );
			add_action( 'init', array($this,'tc_posttype' ) );
			add_action('admin_enqueue_scripts', array($this, 'tc_load_scripts'));
			add_action('wp_ajax_tc_create_list', array($this, 'tc_create_list_callback'));
			add_filter('wp_insert_post_data', array($this,'tc_posttype_status') );
			
		}
		public function tc_load_scripts($hook)
		{
			// load on this page only
			if( $hook === 'tc-campaign_page_tc_list_page' ){
				wp_enqueue_script( 'tc-custom-js', plugin_dir_url(__FILE__) . 'js/tc_admin.js', array('jquery'));
				
			}
		}
		/* Custom Post Type Start */
		
		public function tc_posttype() {
			register_post_type( 'tc_contact',
			// CPT Options
			
			array(
			'labels' => array(
			'name' => __( 'Contacts' ),
			'singular_name' => __( 'Contact' )
			),
			'public' => true,
			'has_archive' => false,
			'rewrite' => array('slug' => 'contacts'),
			)
			);
		}
		
		function tc_posttype_status($post)
		{
			if ($post['post_status'] != 'trash' 
			&& $post['post_status'] != 'draft' 
			&& $post['post_status'] != 'auto-draft'
			&& $post['post_type'] == 'tc_contact')
			{
				$post['post_status'] = 'private';
			}
			return $post;
		}
		/* Custom Post Type End */		
		/**
			* Add options page
		*/
		public function tc_plugin_page()
		{
			// This page will be under "Settings"
			add_menu_page(
            'Settings Admin', 
            'TC Campaign', 
            'manage_options', 
            'tc_setting', 
            array( $this, 'tc_admin_page' )
			);
		}
		
		/**
			* Options page callback
		*/
		public function tc_admin_page()
		{
			
			$this->options = get_option( 'tc_option' );
			$this->test_api();
			
		?>
        <div class="wrap">
            <h1>Active Campaign Setting</h1>
			<p>Your API URL can be found in your Active Campaign account on the Settings page under the "Developer" tab.</p> 
            <form class="tc-form-api" method="post" action="options.php">
				<?php
					// This prints out all hidden setting fields
					settings_fields( 'tc_option_group' );
					do_settings_sections( 'tc_setting' );
					submit_button();
				?>
			</form>
		</div>
        <?php
		}
		
		/**
			* Register and add settings
		*/
		public function tc_page_init()
		{        
			register_setting(
            'tc_option_group', // Option group
            'tc_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
			);
			
			add_settings_section(
            'tc_setting_section_id', // ID
            false, // Title
            false, // Callback
            'tc_setting' // Page
			);  
			
			add_settings_field(
            'api_key', // ID
            'API Key', // Title 
            array( $this, 'api_key_callback' ), // Callback
            'tc_setting', // Page
            'tc_setting_section_id' // Section           
			);      
			
			add_settings_field(
            'ac_url', 
            'AC URL', 
            array( $this, 'acurl_callback' ), 
            'tc_setting', 
            'tc_setting_section_id'
			);      
		}
		
		/**
			* Sanitize each setting field as needed
			*
			* @param array $input Contains all settings fields as array keys
		*/
		public function sanitize( $input )
		{
			$new_input = array();
			if( isset( $input['api_key'] ) )
            $new_input['api_key'] = sanitize_text_field( $input['api_key'] );
			
			if( isset( $input['ac_url'] ) )
            $new_input['ac_url'] = sanitize_text_field( $input['ac_url'] );
			
			return $new_input;
		}
		
		/** 
			* Get the settings option array and print one of its values
		*/
		public function api_key_callback()
		{
			printf(
            '<input class="regular-text" type="text" id="api_key" name="tc_option[api_key]" value="%s" required />',
            isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
			);
		}
		
		/** 
			* Get the settings option array and print one of its values
		*/
		public function acurl_callback()
		{
			printf(
            '<input class="regular-text" type="text" id="ac_url" name="tc_option[ac_url]" value="%s" required />',
            isset( $this->options['ac_url'] ) ? esc_attr( $this->options['ac_url']) : ''
			);
		}
		
		/**
			* Adds a submenu page under a custom page setting parent.
		*/
		public function tc_list_page() {
			add_submenu_page(
			'tc_setting',
			'Active Campaign Lists',
			'Lists',
			'manage_options',
			'tc_list_page',
			array($this, 'tc_lists_page_callback')
			);
		}
		
		/**
			* Display callback for the submenu page.
		*/
		public function tc_lists_page_callback()
		{ 
		?>
        <div class="wrap">
            <h1>Active Campaign Lists</h1>
            <form class="tc-ajax" method="post" action="">
				
				<table class="form-table">
					<h2 class="title">Create List</h2>
					<p>After create a list, you can see the newly created list in the dropdown bellow.</p>
					<tr>
						<th scope="row"><label for="tc_list_name">List Name</label></th>
						<td><input type="text" class="regular-text" name="tc_list_name" id="tc_list_name" value="" required></td>
					</tr>
					<tr>
						<th scope="row"><label for="tc_list_desc">List Description</label></th>
						<td><textarea name="tc_list_desc" rows="5" cols="30" id="tc_list_desc" class="large-text code" required></textarea></td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="Submit">
					<span style="display:none;vertical-align:bottom;" class="spin"><img width="30" height="30" alt="spinner" src="<?php echo get_admin_url();?>images/spinner-2x.gif"/></span> 
				</p>
			</form>			
			
			<?php
				$param = [];
				$param['method'] = 'GET';
				$param['endpoint'] = 'lists';
				$response = $this->tc_get( $param );
				$lists = [];
				if (is_wp_error($response)){
					
					} else if (isset($response['body']) && !empty($response['body'])) {
					$lists = json_decode($response['body']);
					$shortcode = '';
					if ($lists->lists)
					$shortcode = "[my_ac list='0']";
					echo '<p>The shortcode <code>list</code> id represent the list name in the the dropdown.</p>';
					echo '<table class="form-table">';
					echo '<tr>';
					echo '<th scope="row"><label for="tc_list">Choose a list:</label></th>';
					echo '<td><select name="tc_list" id="tc_list">';
					echo '<option value="0">Select List</option>';
					if ($lists->lists){
						foreach ($lists->lists as $list){
							echo '<option value="' . $list->id . '">' . $list->name . '</option>';
						}
						} else {
						echo '<option value="1">Please Create List</option>';
					}
					
					echo '</select></td>';
					echo '<td><input type="text" class="shortcode code" value="' . $shortcode . '"></td>';
					echo '</tr>';
					
					echo '</table>';
				}
				
			?>
			
		</div>
		<?php
		}	
	}
	
	if( is_admin() )
$tc_settings_page = new TcPage();