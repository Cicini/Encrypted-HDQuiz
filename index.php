<?php
/*
    * Plugin Name: Encrypted HD Quiz 
    * Description: Based on HD Quiz, it can prevent users from decoding the answer from HTML and support inserting iframe on the result page.
    * Plugin URI: https://github.com/cicini/encrypted-hdquiz
    * Author: Cicini (Original Version: Harmonic Design)
    * Author URI: https://github.com/cicini
    * Version: 2022.11.21
*/

// Want to change the new admin question pagination per-page?
// add `define("HDQ_PER_PAGE", 200);` to your theme's functions.php (set 200 to your desired number)

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!defined('HDQ_PLUGIN_VERSION')) {
    define('HDQ_PLUGIN_VERSION', '2023.01.29');
}

// custom quiz image sizes
add_image_size('hd_qu_size2', 400, 400, true); // image-as-answer

/* Include the basic required files
------------------------------------------------------- */
require dirname(__FILE__) . '/includes/settings.php'; // global settings class
require dirname(__FILE__) . '/includes/post-type.php'; // custom post types
require dirname(__FILE__) . '/includes/meta.php'; // custom meta
require dirname(__FILE__) . '/includes/functions.php'; // general functions

// function to check if HD Quiz is active
function hdq_exists()
{
    return;
}

/* Add shortcode
------------------------------------------------------- */
function hdq_add_shortcode($atts)
{
    // Attributes
    extract(
        shortcode_atts(
            array(
                'quiz' => '',
            ),
            $atts
        )
    );

    // Code
    ob_start();
    include plugin_dir_path(__FILE__) . './includes/template.php';
    return ob_get_clean();
}
add_shortcode('HDquiz', 'hdq_add_shortcode');


/* Add Gutenberg block
------------------------------------------------------- */
function hdq_register_block_box()
{
    if (!function_exists('register_block_type')) {
        return; // Gutenberg is not active.
    }
    wp_register_script(
        'hdq-block-quiz',
        plugin_dir_url(__FILE__) . 'includes/js/hdq_block.js',
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
        HDQ_PLUGIN_VERSION
    );
    register_block_type('hdquiz/hdq-block-quiz', array(
        'style' => 'hdq-block-quiz',
        'editor_style' => 'hdq-block-quiz',
        'editor_script' => 'hdq-block-quiz',
    ));
}
add_action('init', 'hdq_register_block_box');

/* Get Quiz list
 * used for the gutenberg block
------------------------------------------------------- */
function hdq_get_quiz_list()
{
    $taxonomy = 'quiz';
    $term_args = array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $tax_terms = get_terms($taxonomy, $term_args);
    $quizzes = array();
    if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
        foreach ($tax_terms as $tax_terms) {
            $quiz = new stdClass;
            $quiz->value = $tax_terms->term_id;
            $quiz->label = $tax_terms->name;
            array_push($quizzes, $quiz);
        }
    }
    echo json_encode($quizzes);
    die();
}
add_action('wp_ajax_hdq_get_quiz_list', 'hdq_get_quiz_list');

/* Disable Canonical redirection for paginated quizzes
------------------------------------------------------- */
function hdq_disable_redirect_canonical($redirect_url)
{
    global $post;
    if (!isset($post->post_content)) {
        return;
    }
    if (has_shortcode($post->post_content, 'HDquiz')) {
        $redirect_url = false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'hdq_disable_redirect_canonical');

/* Create HD Quiz Settings page
------------------------------------------------------- */
function hdq_create_settings_page()
{
    if (hdq_user_permission()) {
        function hdq_register_quizzes_page()
        {
            add_menu_page('HD Quiz', 'HD Quiz', 'publish_posts', 'hdq_quizzes', 'hdq_register_quizzes_page_callback', 'dashicons-clipboard', 5);
            add_menu_page('HD Quiz Addons', 'HDQ Addons', 'edit_posts', 'hdq_addons', 'hdq_register_addons_page_callbak', '', 99);
            add_menu_page('HD Quiz Tools', 'HDQ Tools', 'edit_posts', 'hdq_tools', 'hdq_register_tools_page_callbak', '', 99);

            add_menu_page('HD Quiz Tools - CSV Importer', 'HDQ Tools CSV', 'edit_posts', 'hdq_tools_csv_importer', 'hdq_register_tools_csv_importer_page_callback', '', 99);
            add_menu_page('HD Quiz Tools - Data Upgrade', 'HDQ Tools DATA', 'edit_posts', 'hdq_tools_data_upgrade', 'hdq_register_tools__data_upgrade_page_callback', '', 99);

            remove_menu_page('hdq_addons');
            remove_menu_page('hdq_tools');
            remove_menu_page('hdq_tools_csv_importer');
            remove_menu_page('hdq_tools_data_upgrade');
        }
        add_action('admin_menu', 'hdq_register_quizzes_page');

        function hdq_register_settings_page()
        {
            $addon_text = "";
            $new_addon = get_option("hdq_new_addon");
            if ($new_addon != null && $new_addon != "") {
                $new_addon = array_map("sanitize_text_field", $new_addon);
                if ($new_addon[0] === "yes") {
                    $addon_text = ' <span class="awaiting-mod">NEW</span>';
                }
            }
            add_submenu_page('hdq_quizzes', 'Quizzes', 'Quizzes', 'publish_posts', 'hdq_quizzes', 'hdq_register_quizzes_page_callback');
            add_submenu_page('hdq_quizzes', 'HD Quiz About', 'About / Options', 'publish_posts', 'hdq_options', 'hdq_register_settings_page_callback');
            add_submenu_page('hdq_quizzes', 'Addons', 'Addons' . $addon_text, 'manage_options', 'admin.php?page=hdq_addons');
            add_submenu_page('hdq_quizzes', 'Tools', 'Tools', 'manage_options', 'admin.php?page=hdq_tools');
        }
        add_action('admin_menu', 'hdq_register_settings_page', 11);
    }

    $hdq_version = sanitize_text_field(get_option('HDQ_PLUGIN_VERSION'));

    if ($hdq_version != "" && $hdq_version != null && $hdq_version < "1.8") {
        update_option("hdq_remove_data_upgrade_notice", "yes");
        update_option("hdq_data_upgraded", "occured");
        hdq_update_legacy_data();
    } else {
        update_option("hdq_data_upgraded", "all good");
    }

    if (HDQ_PLUGIN_VERSION != $hdq_version) {
        update_option('HDQ_PLUGIN_VERSION', HDQ_PLUGIN_VERSION);

        // start new addon cron. Runs once a day
        wp_schedule_event(time() + 30, "daily", "hdq_check_for_updates");
    }
}
add_action('init', 'hdq_create_settings_page');

function hdq_check_for_updates()
{
    $data = get_option("hdq_new_addon");
    if ($data != null && $data != "" && is_array($data)) {
        $data = array_map("sanitize_text_field", $data);
    } else {
        $data = array("", "");
    }

    $remote = wp_remote_get("https://raw.githubusercontent.com/Cicini/Encrypted-HDQuiz/main/addons.txt");
    if (is_array($remote)) {
        $remote = sanitize_text_field($remote["body"]);
        if ($remote > $data[1]) {
            update_option("hdq_new_addon", array("yes", $remote));
        }
    }
}
add_action('hdq_check_for_updates', 'hdq_check_for_updates', 10, 0);

function hdq_deactivation()
{
    wp_clear_scheduled_hook('hdq_check_for_updates');
}
register_deactivation_hook(__FILE__, 'hdq_deactivation');

function hdq_validate_answers()
{
	$postData = $_POST["data"];
	$data = array();
	
	// sanitize bby
	foreach($postData as $d){
		
		$a = $d["answer"];
		if($a !== ""){
			if(!is_numeric($a)){
				$a = sanitize_text_field($a);
			} else {
				$a = intval($a) + 1;
			}
		}
		
		
		$dd = array(
			"question" => intval($d["id"]),
			"answer" => $a
		);
		array_push($data, $dd);
	}
	
	// $data is now a clean and sanitized array of each question and its selected answer
	// so now we need to query each and every question one-by-one to figure out what 
	// the correct answer was and see if we match

	foreach($data as $k => $question){
		$q = get_hdq_question($question["question"]);		
		$question_type = $q["question_type"]["value"];
		
		if($question_type !== "text_based"){
			$correct = intval($q["selected"]["value"][0]);	
			if($correct === $question["answer"]){
				$data[$k]["status"] = "correct";
			} else {
				$data[$k]["status"] = "wrong";
			}
		} else {
			// text based
			$correct = $q["answers"]["value"]; // array of allowed answers
			
			// now we need to recreate the JavaScript function 
			// to figure out if our answer is allowed
	
			// convert all to uppercase
			$a = strtoupper($question["answer"]);
			$status = false;
			if($a !== ""){
				foreach($correct as $kk => $c){
					$correct[$kk]["answer"] = strtoupper($c["answer"]);
					if($correct[$kk]["answer"] === $a){
						$status = true;
						break;
					}
				}	
			}
			
			if($status === true){
				$data[$k]["status"] = "correct";
			} else {
				$data[$k]["status"] = "false";
			}				
		}
	}
	
	echo json_encode($data);
	die();
}
add_action('wp_ajax_hdq_validate_answers', 'hdq_validate_answers');
add_action('wp_ajax_nopriv_hdq_validate_answers', 'hdq_validate_answers');
function hdq_get_result_text()
{
	$quiz_ID = intval($_POST["data"]["quiz_ID"]);
	$status = sanitize_text_field($_POST["data"]["status"]);	
	
	$quiz_settings = get_hdq_quiz($quiz_ID);
    $pass_text = $quiz_settings["quiz_pass_text"]["value"];
    $fail_text = $quiz_settings["quiz_fail_text"]["value"];
	
	if($status === "pass"){
		echo $pass_text;
	} else {
		echo $fail_text;
	}
	die();
}
add_action('wp_ajax_hdq_get_result_text', 'hdq_get_result_text');
add_action('wp_ajax_nopriv_hdq_get_result_text', 'hdq_get_result_text');
