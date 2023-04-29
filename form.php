<?php
    $tc_form = new tcForm(); 
    class tcForm extends TcApi
    {
        
        public function __construct()
        {
            $this->tc_add_actions();
            $this->tc_add_shortcode();
        }
        public function tc_add_actions()
        {
            add_action('wp_ajax_nopriv_tc_form', array($this, 'tc_add_contact'));
            add_action('wp_ajax_tc_form', array($this, 'tc_add_contact'));
            add_action('wp_enqueue_scripts', array($this, 'tc_enqueue_styles'), 99999);
        }
        public function tc_enqueue_styles()
        {
            /*
                * include the js & css
            */
            wp_enqueue_style('tc_form_style', plugin_dir_url(__FILE__) . 'js/tc_form.css');
            wp_enqueue_script('tc_form_script', plugin_dir_url(__FILE__) . 'js/tc.js', array('jquery'));
            // ajax url
            wp_localize_script('tc_form_script', 'tc_forms', array('ajax_url' => admin_url('admin-ajax.php')) );
        }
        // shortcode [my ac list=id]
        public function tc_add_shortcode()
        {
            add_shortcode('my_ac', array($this, 'my_ac_form'));
        }
        // shortcode handler
        public function my_ac_form($atts)
        {
            $tc_list = shortcode_atts( array(
            'list' => 1,
            ), $atts );
            $form = '
            <div class="tc_form_container">
            <form action="" method="post" class="tc-ajax" 
            enctype="multipart/form-data">
            
            <h3>Subscribe</h3>
            
            <label><b>First Name</b></label>
            
            <input type="text" placeholder="First Name" id="fname" name="fname" 
            required class="tc_form_input">
            <label><b>Last Name</b></label>
            
            <input type="text" placeholder="Last Name" id="lname" name="lname" 
            required class="tc_form_input">
            <label><b>Email</b></label>
            
            <input type="email" placeholder="Enter your Email" id="email_id" name="email" 
            required class="tc_form_input">
            
            <input type="hidden" id="list_id" name="list_id" value="' . $tc_list['list'] . '">
            <div class="tc-submit">
            <button type="submit" class="tc_form_submitbtn">Subscribe</button>
            <div class="tc-form-loading"><div class="tc-spin">
            <div></div>
            </div></div>
            </div>
            <div class="success_msg tc-msg">Thank you</div>
            
            <div class="error_msg tc-msg">Sorry, There is some error.</div>
            
            </form>
            
            </div>';
            return $form;
        }
    }        