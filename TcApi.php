<?php
	Class TcApi
	{
		public $url;
		public $api_key;
		public $options;
		
		
		public function tc_get( $param = [] ) 
		{
			
			$this->options = get_option( 'tc_option' );
			$this->api_key = isset($this->options['api_key']) ? $this->options['api_key'] : '';
			$this->url = isset($this->options['ac_url']) ? $this->options['ac_url'] : '';
			$response = [];
			// Collect the args
			$params = 	[
			'method'    => isset($param['method']) ? $param['method'] : 'GET',
			'sslverify' => false,
			'headers'   => [
			'Content-Type' => 'application/json',
			'accept' => 'application/json',
			'user-agent' => $_SERVER['HTTP_USER_AGENT'],
			'Api-Token'     => $this->api_key,
			],
			'body' => isset($param['body']) ? $param['body'] : null,
			'data_format' => 'body',
			];
			if (!isset($param['body']))
			unset($params['body'],$params['data_format']);
			$endpoint = isset($param['endpoint']) ? $param['endpoint'] : '';
			// Generate the URL
			$url = $this->url . '/api/3/' . $endpoint;
			// Make API request
			$response = wp_remote_post($url,$params );
			return $response;
		}
		
		public function test_api()
		{
			if (isset($_GET['settings-updated'])){
				$param = [];
				$err = 1;
				$response = $this->tc_get( $param );
				if (is_wp_error($response)){
					echo '<div class="notice notice-error is-dismissible"><p>Something wrong : ' . $response->get_error_message() . '</p></div>';
					} else 	if ($response['response']['code'] == 200){
					echo '<div class="notice notice-success is-dismissible"><p>Setting Saved</p></div>';
					} else {
					echo '<div class="notice notice-error is-dismissible"><p>Something wrong : ' . $response['response']['message'] . ', Please check your credentials</p></div>';
				}
			}
		}
		
		public function tc_create_list_callback()
		{
			// Make request
			if (isset($_POST['action'])){
				$name = $_POST['list_name'];
				$desc  = $_POST['list_desc'];
				$param = [];
				$newlist = [
				'name' => sanitize_text_field($name), 
				'stringid' => sanitize_title_with_dashes($name), 
				'sender_url' => get_site_url(), 
				'sender_reminder' => sanitize_textarea_field($desc),
				
				];
				
				$param['method'] = 'POST';
				$param['endpoint'] = 'lists';
				$param['body'] = json_encode(['list' => $newlist]);
				$response = $this->tc_get( $param );
				$response_code = wp_remote_retrieve_response_code( $response );
				
				if ( 200 == $response_code || 201 == $response_code ){
					$paramc = [];
					$paramc['method'] = 'GET';
					$paramc['endpoint'] = 'lists';
					$response = $this->tc_get( $paramc );
					$lists = [];
					if (isset($response['body']) && !empty($response['body'])) {
						$lists = json_decode($response['body']);
						foreach ($lists->lists as $list){
							echo '<option value="' . $list->id . '">' . $list->name . '</option>';
						}
					}		
				}
			}
			wp_die();
			
		}
		
		public function tc_add_contact()
		{
			// Make request to create contact
			if (isset($_POST['action'])){
				$email = sanitize_email($_POST['email']);
				$fname = sanitize_text_field($_POST['fname']);
				$lname = sanitize_text_field($_POST['lname']);
				$list_id = $_POST['list'];
				$param = [];
				$newlist = [
				'email' => $email, 
				'firstName' => $fname, 
				'lastName' => $lname, 
				];
				
				$param['method'] = 'POST';
				$param['endpoint'] = 'contacts';
				$param['body'] = json_encode(['contact' => $newlist]);

				$response = $this->tc_get( $param );
				$response_code = wp_remote_retrieve_response_code( $response );

				$contact = json_decode($response['body']);
				// append contact to a list
				if (isset($contact->contact)){
					$contact_id = $contact->contact->id;
					
					$newlist = [
					'list' => $list_id,
					'contact' => $contact_id,
					'status' => 1,
					];
					$param['endpoint'] = 'contactLists';
					$param['body'] = json_encode(['contactList' => $newlist]);
					$response = $this->tc_get( $param );
					$contact = json_decode($response['body']);
					// add contact to a custom post type
					$post = get_post( 10 ); // edit this id
					$post->post_content .= "{$fname} {$lname},{$email}\n";
					
					wp_update_post( $post );
					
				}
				
			}
			wp_die();
		}
	}		