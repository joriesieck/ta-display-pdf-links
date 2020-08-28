<?php
/*
   Plugin Name: Display Gravity Forms Answers
   Version: 0.1
   Author: Jorie Sieck
   Author URI: https://www.joriesieck.com
   Description: Displays links to PDFs for a learner's completed 'Study Skills Practical Exercises' Gravity Forms
   License: GPLv3
*/

/**
 * save 'entry id' for each practical exercise for each user
*/
add_action('gform_after_submission','ta_save_gf_info',10,2);
function ta_save_gf_info($entry,$form) {
	global $current_user;
	global $post;

	$pdf_id = array_keys($form['gfpdf_form_settings'])[0];
	if($post->post_type==='sfwd-topic' && strpos($post->post_title,'Practical Exercise')>0) {
		// data to store in user meta. 0->form pdf id, 1->id of this entry
		$pdf_info = "{$pdf_id},{$entry['id']}";

		// save data to user meta
		add_user_meta($current_user->ID,"ta_prac_ex_{$form['title']}",$pdf_info,false);
	}

}

/**
 * on page with [ta_prac_ex_submissions] shortcode, look for any entry ids and generate link to display each
 * gfpdf settings automatically only allow the pdf owner (+ admins) to view, so
 * no need to set up our own protocols besides checking that user is logged in
*/
add_shortcode('ta_prac_ex_submissions','ta_display_gf_pdfs');
function ta_display_gf_pdfs($atts, $content=null, $code="") {
	global $current_user;
	$user_id = $current_user->ID;
	$output = '';
	// if user is logged in
	if($user_id) {
		global $wpdb;
		// check for info in user meta
		// 0->pdf id, 1->entry id
		$sql = "SELECT * FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'ta_prac_ex_%' AND user_id = {$user_id}";
		$pdf_info = $wpdb->get_results($sql);

		// if there aren't any PDFs to display, print a message and quit
		if(!$pdf_info) {
			$output = "<p>You haven't completed any practical exercises yet. Once you've done some exercises, you can see your work here.</p>";
			return $output;
		} else {
			$output = "<p style='margin-bottom:0;'>Click below to view your work for each of the practical exercises that you've completed.</p>";
		}

		// organize db data for printing
		$shortcode_data = array();
		foreach($pdf_info as $meta_object) {
			$key = $meta_object->meta_key;
			$title = substr($key,strpos($key,'ta_prac_ex_')+11);
			$shortcode_data[$title] = $meta_object->meta_value;
		}
		// sort shortcode data - desc alphabetical
		ksort($shortcode_data);

		// loop over shortcode data and print a view link for each pdf -> link text = form title
		foreach($shortcode_data as $title=>$entry_pdf) {
			if(gettype($entry_pdf)==='string') {
				$pdf_id = substr($entry_pdf,0,strpos($entry_pdf,','));
				$entry_id = substr($entry_pdf,strpos($entry_pdf,',')+1);
				$output .= "<p style='margin-bottom:0;'>" . do_shortcode("[gravitypdf id='{$pdf_id}' entry='{$entry_id}' type='view' text='{$title}']") . "</p>";
			}
		}
	} else {
		$output = "Please log in to view this page.";
	}

	return $output;
}

?>
