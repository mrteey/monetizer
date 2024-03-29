<?php
/*
Plugin Name: Monetizer
Plugin URI: https://mrteey.com
Description: Blog Monetization Plug, Allows View of Single Post only by registred users.
Author: Mr.Teey
Version: 2.6.3
Author URI: https://mrteey.com
License: GPL2
*/

// Create Plugin UI //Custom Post Page
// Plans Creator
add_action( 'init', 'monetizer_custom_post_type' );
 
function monetizer_custom_post_type() {
    $labels = array(
        'name'               => 'Monetizer Plans',
        'singular_name'      => 'Monetizer Plan',
        'menu_name'          => 'Monetizer',
        'name_admin_bar'     => 'Monetizer',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Plan',
        'new_item'           => 'New Plan',
        'edit_item'          => 'Edit Plan',
        'view_item'          => 'View Plan',
        'all_items'          => 'All Plans',
        'search_items'       => 'Search Plans',
        'parent_item_colon'  => 'Parent Plans:',
        'not_found'          => 'No plans found.',
        'not_found_in_trash' => 'No plans found in Trash.'
    );
 
    $args = array( 
        'public'      => true, 
        'labels'      => $labels,
        'has_archive'   => false,
        'menu_position' => 20,
        'menu_icon'     => 'dashicons-tag',
        'taxonomies'        => array( 'plans' ),
        'supports'      => array( 'title')
    );
        register_post_type( 'monetizer', $args );
}

add_filter( 'post_updated_messages', 'monetizer_updated_messages' );
// Plans Custom post updated message
function monetizer_updated_messages( $messages ) {
	global $post, $post_ID;
	$messages['monetizer'] = array(
	  0 => '', 
	  1 => __('Plan updated successfully.'),
	  2 => __('Custom field updated.'),
	  3 => __('Custom field deleted.'),
	  4 => __('Plan updated.'),
	  5 => __('Plan created successfully.')
	);
	return $messages;
  }

//   Meta Boxes
// Amount Box
add_action( 'add_meta_boxes', 'plan_amount_box' );
function plan_amount_box() {
    add_meta_box( 
        'plan_amount_box',
        __( 'Plan Amount', 'myplugin_textdomain' ),
        'plan_amount_box_content',
        'monetizer',
        'normal',
        'high'
    );
}

function plan_amount_box_content( $post ) {
	$amount = get_post_meta( get_the_ID(), 'plan_amount', TRUE );
	wp_nonce_field( plugin_basename( __FILE__ ), 'plan_amount_box_content_nonce' );
	// HIDE SLUG
	echo "<style>#edit-slug-box,#message p > a{display:none;}</style>";
	echo '<label for="plan_amount"></label>';
	echo '<input type="number" name="plan_amount" size="30" id="plan_amount" style="width:100%" placeholder="Enter plan amount" value="'.$amount.'"/>';
  }

  // Duration Box
add_action( 'add_meta_boxes', 'plan_duration_box' );
function plan_duration_box() {
    add_meta_box( 
        'plan_duration_box',
        __( 'Plan Duration', 'myplugin_textdomain' ),
        'plan_duration_box_content',
        'monetizer',
        'normal',
        'high'
    );
}

function plan_duration_box_content( $post ) {
	$duration = get_post_meta( get_the_ID(), 'plan_duration', TRUE );
	wp_nonce_field( plugin_basename( __FILE__ ), 'plan_duration_box_content_nonce' );
	echo '<label for="plan_duration"></label>';
	echo '<input type="number" name="plan_duration" size="30" id="plan_duration" style="width:100%" placeholder="Enter plan duration in days" value="'.$duration.'"/>';
  }


  //Plan Description box
add_action( 'add_meta_boxes', 'plan_description_box' );
function plan_description_box() {
    add_meta_box( 
        'plan_description_box',
        __( 'Plan Description', 'myplugin_textdomain' ),
        'plan_description_box_content',
        'monetizer',
        'normal',
        'high'
    );
} 

function plan_description_box_content( $post ) {
	$description = get_post_meta( get_the_ID(), 'plan_description', TRUE );
	wp_nonce_field( plugin_basename( __FILE__ ), 'plan_description_box_content_nonce' );
	echo '<label for="plan_description"></label>';
	echo '<textarea rows="10" name="plan_description" size="30" id="plan_description" style="width:100%">'.$description.'</textarea>';
  }


// Callback url box
add_action( 'add_meta_boxes', 'plan_callback_box' );
function plan_callback_box() {
    add_meta_box( 
        'plan_callback_box',
        __( 'Callback endpoint', 'myplugin_textdomain' ),
        'plan_callback_box_content',
        'monetizer',
        'normal',
        'high'
    );
}

function plan_callback_box_content( $post ) {
	$callback = get_post_meta( get_the_ID(), 'plan_callback', TRUE );
	wp_nonce_field( plugin_basename( __FILE__ ), 'plan_callback_box_content_nonce' );
	echo '<label for="plan_callback"></label>';
	echo '<input type="text" readonly name="plan_callback" size="30" value="'.$callback.'" id="plan_callback" style="width:100%"/>';
  }


// SHORTCODES
// Create Profile Shortcode
function monetizer_user_profile(){
	//Buid User Profile
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$plan = get_user_meta($user_id, 'user_plan', true);
	$payment_date = get_user_meta($user_id, 'payment_date', true);
	//User plan info from custom post type ==> monetizer
	$args = array(
		'name' => $plan,
		'post_type' => 'monetizer'
	);
	$plan_info = get_posts($args)[0];
	$duration = get_post_meta($plan_info->ID, 'plan_duration', true);
	$diff = time() - strtotime($payment_date);
	$expiry = $duration - round($diff / (60 * 60 * 24));
	
	$profile = "<h4>Name: </h4> ".$current_user->user_firstname.' '.$current_user->user_lastname."<br><h4>Current Plan:</h4> ".$plan.'<br> <h3>Expires in: </h3>'.$expiry.' days';
	return $profile;
}
add_shortcode('monetizer_user_profile', 'monetizer_user_profile');

// User email Shortcode
function monetizer_user_name(){
	//Get User Name
	$current_user = wp_get_current_user();
	return $current_user->user_firstname.' '.$current_user->user_lastname;
}
add_shortcode('monetizer_user_name', 'monetizer_user_name');

// User email Shortcode
function monetizer_user_email(){
	//Get User email
	$current_user = wp_get_current_user();
	return $current_user->user_email;
}
add_shortcode('monetizer_user_email', 'monetizer_user_email');

// User plan Shortcode
function monetizer_user_plan(){
	//Get User Plan
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$plan = get_user_meta($user_id, 'user_plan', true);
	return $plan;
}
add_shortcode('monetizer_user_plan', 'monetizer_user_plan');

// User plan Shortcode
function monetizer_user_plan_expiry(){
	//Get User Plan
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$plan = get_user_meta($user_id, 'user_plan', true);
	$payment_date = get_user_meta($user_id, 'payment_date', true);
	//User plan info from custom post type ==> monetizer
	$args = array(
		'name' => $plan,
		'post_type' => 'monetizer'
	);
	$plan_info = get_posts($args)[0];
	$duration = get_post_meta($plan_info->ID, 'plan_duration', true);
	$diff = time() - strtotime($payment_date);
	$expiry = $duration - round($diff / (60 * 60 * 24));
	return $expiry;
}
add_shortcode('monetizer_user_plan_expiry', 'monetizer_user_plan_expiry');

// Create Plans Page Shortcode
function monetizer_plans(){
	// Content of Plans Page
	// PRICING TABLE
	$table_style = "<style>
	* { box-sizing: border-box; }
	.columns {
		float: left;
		width: 33.3%;
		padding: 8px;
	}

	.price {
	list-style-type: none;
	border: 1px solid #eee;
	margin: 0;
	padding: 0;
	-webkit-transition: 0.3s;
	transition: 0.3s;
	}

	.price:hover {
		box-shadow: 0 8px 12px 0 rgba(0,0,0,0.2)
	}

	.price .header {
	background-color: #111;
	color: white;
	font-size: 25px;
	}

	.price li {
	border-bottom: 1px solid #eee;
	padding: 20px;
	text-align: center;
	}

	.price .grey {
	background-color: #eee;
	font-size: 20px;
	}

	.button {
	background-color: #4CAF50;
	border: none;
	color: white;
	padding: 10px 25px;
	text-align: center;
	text-decoration: none;
	font-size: 18px;
	}

	@media only screen and (max-width: 600px) {
	.columns {
		width: 100%;
	}
	}
	</style>";

	
	// Get all monetizer plans
	$args = array(
		'post_type' => 'monetizer',
		'posts_per_page' => -1
	);
	$plans = get_posts($args);

	$table_header = "<h2 style='text-align:center'>".count($plans)." Available Plans</h2><p style='text-align:center'>Select a plan to make payment!</p>";

	$available_plans = "";
	foreach ($plans as $plan){
		$name = $plan->post_title;
		$amount = get_post_meta($plan->ID, 'plan_amount', TRUE);
		$description = get_post_meta($plan->ID, 'plan_description', TRUE);
		$slug = $plan->post_name;
		$available_plans = ''.$available_plans.' '."<div class='columns'><ul class='price'> <li class='header'>".$name."</li> <li class='grey'>₦".$amount."</li><li>".$description."</li><li class='grey'><a style='color:white' href=\'".$slug."' class='button'>Subscribe</a></li></ul></div>";
	}

	return $table_style.$table_header.$available_plans;
}
add_shortcode('monetizer_plans', 'monetizer_plans');

// On Save_Post action, Save custom meta boxes create paystack plan and related pages if paystack exist 
add_action( 'save_post', 'save_custom_boxes' );
function save_custom_boxes( $post_id ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
  return;

  if ( !wp_verify_nonce( $_POST['plan_callback_box_content_nonce'], plugin_basename( __FILE__ ) ) )
  return;

  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
    return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
    return;
  }
	//   Get Post Data
  $post_title = $_POST['post_title'];
  $slug = strtolower(str_replace(" ", "-", $post_title));
  $callback_endpoint = "/paid?plan=".$slug;
  $plan_duration = $_POST['plan_duration'];
  $plan_description = $_POST['plan_description'];
  $plan_amount = $_POST['plan_amount'];
	//   Update POST META Info
  update_post_meta( $post_id, 'plan_duration', $plan_duration );
  update_post_meta( $post_id, 'plan_callback', $callback_endpoint );
  update_post_meta( $post_id, 'plan_amount', $plan_amount );
  update_post_meta( $post_id, 'plan_description', $plan_description );
  // Add slug to categories
  	$cat_id = get_cat_ID( $slug );
	// check if thanks page exists:
		if ($cat_id == 0){
			// Create the page object
			$cat = array(
				'cat_name'     => $slug
			);
			// Insert the post into the database
			wp_insert_category( $cat );
		}
	// Check if Paystack Forms Exist
	if( function_exists( 'kkd_pff_init' ) ) {
		// If Plugin Exist
		// Check if similar form already exist
		$args = array(
			'name' => $slug,
			'post_type' => 'paystack_form'
		);
		$form = get_posts($args)[0];

		if (!$form){
			// Create a new paystack form post object
			$form = array(
				'post_type'     => 'paystack_form',
				'post_title'    => $post_title,
				'post_content'  => '[text name="Phone Number"]',
				'post_status'   => 'publish',
				'post_author'   => 1
			);
			
			// Insert the form into the database
			$post_id = wp_insert_post( $form );
			// Update form meta data
			update_post_meta( $post_id, '_amount', $plan_amount );
			update_post_meta( $post_id, '_redirect', $callback_endpoint );
			update_post_meta( $post_id, '_currency', 'NGN' );
			update_post_meta( $post_id, '_loggedin', 'no' );
			update_post_meta( $post_id, '_txncharge', 'merchant' );
			update_post_meta( $post_id, '_paybtn', 'Pay' );
			update_post_meta( $post_id, '_successmsg', 'Thank you for paying!' );
		}
		// Check if similar payment page already exist
		$payment_page = get_page_by_path( $slug );

		if (empty($payment_page)){
			// Create a new paystack form post object
			$content = array(
				'post_type'     => 'page',
				'post_title'    => $post_title,
				'post_content'  => '[pff-paystack id="'.$post_id.'"]',
				'post_status'   => 'publish',
				'post_author'   => 1
			);
			
			// Insert the page into the database
			wp_insert_post( $content );
		}
	}
}

// Action before trashing a plan
add_action( 'wp_trash_post', 'trash_related_monetizer_posts' );
function trash_related_monetizer_posts($postid){
	$post = get_post($postid);
	if ($post->post_type == 'monetizer'){
		// Delete Related Paystack Form
		$args = array(
			'name' => $post->post_name,
			'post_type' => 'paystack_form'
		);
		
		$form = get_posts($args)[0];
		if ($form){

			wp_delete_post($form->ID);
		}
		
		// Delete Related Payment Page
		$args = array(
			'name' => $post->post_name,
			'post_type' => 'page'
		);
		
		$page = get_posts($args)[0];
		if ($page){
			
			wp_delete_post($page->ID);
		}

		// Delete Related Category
		$cat_id = get_cat_ID( $slug );
		// check if thanks page exists:
		if ( $cat_id > 0 ){
			// delete the category
			wp_delete_category( $cat_id );
		}
	}
}

// When plugin is activated
function prepare_plugin() {
	// Check whether this pages exist
	$thanks = get_page_by_path( 'thanks' );
	$paid = get_page_by_path( 'paid' );
	$upgrade = get_page_by_path( 'upgrade' );
	$dash = get_page_by_path( 'user-account-info' );
	$plans_page = get_page_by_path( 'plans' );

	// Check if plans page exist
	if (empty($plans_page)){
		$content = array(
			'post_type'     => 'page',
			'post_title'    => 'Plans',
			'post_content'  => '[monetizer_plans]',
			'post_status'   => 'publish',
			'post_author'   => 1
		);
		// Push page to db
		wp_insert_post( $content );
	}

	// check if thanks page exist:
		if (empty($dash)){
			// Create the page object
			$page = array(
				'post_type'     => 'page',
				'post_title'    => 'User Account Info',
				'post_content'  => '[monetizer_user_profile]',
				'post_status'   => 'publish',
				'post_author'   => 1
			);
			
			// Insert the post into the database
			wp_insert_post( $page );
		}
	// check if paid page exists:
		if (empty($paid)){
			// Create the page object
			$page = array(
				'post_type'     => 'page',
				'post_title'    => 'Paid',
				'post_content'  => 'Thank you for your payment!.',
				'post_status'   => 'publish',
				'post_author'   => 1
			);
			
			// Insert the post into the database
			wp_insert_post( $page );
		}
	// check if upgrade page exists:
		if (empty($upgrade)){
			// Create the page object
			$page = array(
				'post_type'     => 'page',
				'post_title'    => 'Upgrade',
				'post_content'  => 'You have to upgrade your plan to view this page <button><a href="/plans" style="color:white">UPGRADE</a></button>',
				'post_status'   => 'publish',
				'post_author'   => 1
			);
			
			// Insert the post into the database
			wp_insert_post( $page );
		}

	do_action( 'prepare_plugin' );
}
register_activation_hook( __FILE__, 'prepare_plugin' );

// ALL AVAILABLE REDIRECTS

//Redirect from premium if not paid
add_action( 'template_redirect', 'redirect_from_premium_when_not_paid' );
function redirect_from_premium_when_not_paid() {
	if (is_single()){
		// Get all monetizer plans
		$args = array(
			'post_type' => 'monetizer',
			'posts_per_page' => -1
		);
		$plans = get_posts($args);

		if (is_user_logged_in() && !current_user_can('administrator')){
			// Get Current User
			$user_id = get_current_user_id();
			$user_plan = get_user_meta($user_id, 'user_plan', TRUE);

			foreach ($plans as $plan){
				$slug = $plan->post_name;
				if (has_category($slug) && !has_category($user_plan)){
					wp_redirect( '/upgrade', 302 ); 
					exit;
				}
			}
		}elseif(!is_user_logged_in()){
			foreach ($plans as $plan){
				$slug = $plan->post_name;
				if (has_category($slug)){
					wp_redirect( '/login', 302 ); 
					exit;
				}
			}
		}
	}
}

//Take User to Account Info After Login
add_action( 'wp_login', 'take_user_to_account' );
function take_user_to_account() {
	if ( !current_user_can('administrator') ){
			wp_redirect( '/user-account-info', 301 ); 
			exit;
		}
	}

//Redirect from payment page if not logged in
add_action( 'template_redirect', 'redirect_from_payment_page' );
function redirect_from_payment_page() {
	// Get all monetizer plans
	$args = array(
		'post_type' => 'monetizer',
		'posts_per_page' => -1
	);
	$plans = get_posts($args);

	foreach ($plans as $plan){
		$slug = $plan->post_name;
		if (is_page($slug) && !is_user_logged_in()){
			wp_redirect( '/login', 302 ); 
			exit;
		}
	}
}


//UPDATE USER PAYMENT DETAILS ON PAYMENT
add_action( 'template_redirect', 'redirect_from_paid_page' );
function redirect_from_paid_page() {
	$referer = wp_get_referer();
	
	$plan = $_GET['plan'];

	if ( is_page('Paid') ){
		if ( $referer && $plan ) {
			// UPDATE PLAN
			$user_id = get_current_user_id();
			$currentdate = current_time( 'mysql' );
			update_user_meta( $user_id, 'user_plan', $plan );
			update_user_meta( $user_id, 'payment_date', $currentdate );
			wp_redirect( '/thanks', 301 ); 
  			exit;
    	}
	}
}


// PLAN VALIDITY
//Check User Validity On Load
add_action('wp', 'check_plan_validity');
function check_plan_validity(){
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$user_plan = get_user_meta($user_id, 'user_plan', true);
	//User plan info from custom post type ==> monetizer
	$args = array(
		'name' => $user_plan,
		'post_type' => 'monetizer'
	);
	$plan = get_posts($args)[0];
	if ($plan){
		$duration = get_post_meta($plan->ID, 'plan_duration', true);
		$payment_date = get_user_meta($user_id, 'payment_date', true);
		
		$date_difference = time() - strtotime($payment_date);
		$expiry = $duration - round($date_difference / (60 * 60 * 24));
		
		if ($expiry <= 0){
			update_user_meta( $user_id, 'user_plan', 'not paid' );
		}
	}
}

