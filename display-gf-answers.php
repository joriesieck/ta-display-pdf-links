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
 * save 'entry id' for each practical exercise for each user -> user meta?
 * https://docs.gravityforms.com/gform_after_submission/
*/
add_action('gform_after_submission','ta_save_gf_info',10,2);
function ta_save_gf_info($entry,$form) {
	global $current_user;
	// data to store in user meta. 0->id of this entry, 1->form pdf id, 2-> form title
	$pdf_info = array(
		$entry['id'],
		array_keys($form['gfpdf_form_settings'])[0],
		$form['title']
	);
	// save data to user meta
	add_user_meta($current_user->ID,'gf_prac_ex_info',$pdf_info,false);
}


/**
 * on page, look for any entry ids and generate link to display each
 * gfpdf settings automatically only allow the pdf owner (+ admins) to view, so
 * no need to set up our own protocols besides checking that user is logged in
*/
add_action('genesis_entry_content','ta_display_gf_pdfs',10,2);
function ta_display_gf_pdfs() {
	global $current_user;
	$user_id = $current_user->ID;
	// if user is logged in & we're on the right page
	if(is_page(6648)) {
		if($user_id) {
			// check for info in user meta
			// 0->entry id, 1->pdf id, 2->form title
			$pdf_info = get_user_meta($user_id,'gf_prac_ex_info',false);

			// loop over array of pdf infos and print a view link for each -> link text = form title
			foreach($pdf_info as $entry_pdf) {
				if(is_array($entry_pdf)) {
					echo "<p>" . do_shortcode("[gravitypdf id='{$entry_pdf[1]}' entry='{$entry_pdf[0]}' type='view' text='{$entry_pdf[2]}']") . "</p>";
				}
			}
		} else {
			echo "Please log in to view this page.";
		}
	}
}

?>
