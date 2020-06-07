<?php
/*
Plugin Name: Blog Monitization
Plugin URI: https://mrteey.com
Description: Blog Monitization Plug, Allows View of Single Post only by registred users.
Author: Mr.Teey
Version: 2.1
Author URI: https://mrteey.com
License: GPL2
*/

// Create Plugin UI
// Plans Creator
add_action( 'init', 'blog_monetizer_custom_post_type' );
add_filter( 'post_updated_messages', 'blog_monetizer_messages' );
 
function blog_monetizer_custom_post_type() {
    $labels = array(
        'name'               => 'Monetizer Plans',
        'singular_name'      => 'Monetizer Plan',
        'menu_name'          => 'Blog Monetizer',
        'name_admin_bar'     => 'Blog Monetizer',
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
        'menu_icon'     => 'dashicons-carrot',
        'taxonomies'        => array( 'plans' ),
        'supports'      => array( 'title')
    );
        register_post_type( 'monetizer', $args );
}

// Plans Custom post updated message
function plans_updated_messages( $messages ) {
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
  add_filter( 'post_updated_messages', 'plans_updated_messages' );

//   Meta Boxes
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


// Saving Plan Callback
add_action( 'save_post', 'plan_callback_box_save' );
function plan_callback_box_save( $post_id ) {
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
  $post_title = strtolower($_POST['post_title']);
  $slug = str_replace(" ", "-", $post_title);
  $callback_endpoint = "/paid?plan=".$slug;
  update_post_meta( $post_id, 'plan_callback', $callback_endpoint );
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
}

// Saving Plan Duration
add_action( 'save_post', 'plan_duration_box_save' );
function plan_duration_box_save( $post_id ) {

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
  return;

  if ( !wp_verify_nonce( $_POST['plan_duration_box_content_nonce'], plugin_basename( __FILE__ ) ) )
  return;

  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
    return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
    return;
  }
  $plan_duration = $_POST['plan_duration'];
  update_post_meta( $post_id, 'plan_duration', $plan_duration );
}
 
// function blog_monetizer_messages( $messages ) {
//     $post = get_post();
 
//     $messages['recipe'] = array(
//         0  => '',
//         1  => 'Recipe updated.',
//         2  => 'Custom field updated.',
//         3  => 'Custom field deleted.',
//         4  => 'Recipe updated.',
//         5  => isset( $_GET['revision'] ) ? sprintf( 'Recipe restored to revision from %s',wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
//         6  => 'Recipe published.',
//         7  => 'Recipe saved.',
//         8  => 'Recipe submitted.',
//         9  => sprintf(
//             'Recipe scheduled for: <strong>%1$s</strong>.',
//             date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) )
//         ),
//         10 => 'Recipe draft updated.'
//     );
 
//     return $messages;
// }


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
	
	$profile = "<h3>Name: </h3> ".$current_user->user_firstname.' '.$current_user->user_lastname."<br><h3>Current Plan:</h3> ".$plan.'<br> <h3>Expires in: </h3>'.$expiry.' days';
	return $profile;
}
add_shortcode('monetizer_user_profile', 'monetizer_user_profile');

// When plugin is activated
function prepare_plugin() {

	// Create Free Category For Free Posts
	$slug = 'Free';
	$cat_id = get_cat_ID( $slug );
	// check if thanks page exists:
	if ($cat_id == 0){
		// Create the page object
		$cat = array(
			'cat_name'     => $slug
		);
		// Insert the category into the database
		wp_insert_category( $cat );
	}

	// Check whether this pages exist
	$thanks = get_page_by_path( 'thanks' );
	$paid = get_page_by_path( 'paid' );
	$upgrade = get_page_by_path( 'upgrade' );
	$dash = get_page_by_path( 'user-account-info' );

	

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


//Remove Admin Bar Except for Administrator

// add_action('after_setup_theme', 'remove_admin_bar');
 
// function remove_admin_bar() {
// if (!current_user_can('administrator') && !is_admin()) {
//   show_admin_bar(false);
// }
// }


//Redirect from Single post if not logged in
 
add_action( 'template_redirect', 'redirect_from_post' );

function redirect_from_post() {

if ( is_single() and !is_admin()) {
	//user is logged in
	$user_id = get_current_user_id();
	$user_plan = get_user_meta ($user_id, 'user_plan', true);
	// check if current user has active plan
	if ($user_plan == 'not paid' or empty($user_plan)){
		wp_redirect( '/plans', 302 ); 
		exit;
	}
	// Redirect them to upgrade if trying to view a premium or vip post
	elseif (!has_category($user_plan) && !has_category('Free')){
		wp_redirect( '/upgrade', 302 ); 
		exit;
	}
	// Check if user has a premium plan
	// Redirect them to upgrade if trying to view a vip post
	// elseif (strpos($plan, 'premium') !== false){
	// 	if (has_category('vip')){
	// 		wp_redirect( '/upgrade', 302 ); 
	// 		exit;
	// 	}
	// }
}

}



//remove comments if user not logged in
// add_action('init', 'remove_comment_support', 100);

// function remove_comment_support() {
//     if (! is_user_logged_in()) {
//         remove_post_type_support( 'post', 'comments' );
//     }
// }

//UPDATE USER PAYMENT DETAILS ON PAYMENT
add_action( 'template_redirect', 'redirect_from_paid_page' );
function redirect_from_paid_page() {
	$referer = wp_get_referer();
	
	$plan = $_GET['plan'];

if ( is_page('Paid')){
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



// Block Access to /wp-admin for non admins.
// function custom_blockusers_init() {
//   if ( is_user_logged_in() && is_admin() && !current_user_can( 'administrator' ) ) {
//     wp_redirect( home_url() );
//     exit;
//   }
// }
// add_action( 'init', 'custom_blockusers_init' ); // Hook into 'init'


//Redirect after logout
// add_action( 'wp_logout', 'auto_redirect_external_after_logout');
// function auto_redirect_external_after_logout(){
//   wp_redirect( '/' );
//   exit();
// }


//Login & Logout Links
// add_filter('wp_nav_menu_items', 'add_login_logout_link', 10, 2);
// function add_login_logout_link($items, $args) {
//         ob_start();
//         wp_loginout('index.php');
//         $loginoutlink = ob_get_contents();
//         ob_end_clean();
//         $items .= '<li>'. $loginoutlink .'</li>';
//     return $items;
// }


//Check User Validity On Load

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

add_action('wp', 'check_plan_validity');

