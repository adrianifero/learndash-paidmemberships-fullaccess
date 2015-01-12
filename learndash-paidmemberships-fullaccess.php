<?php
/**
 * @package LearnDash & Paid Memberships Pro Full Access Add-on
 */
/*
/*
Plugin Name: LearnDash & Paid Memberships Pro Full Access Add On
Plugin URI: http://www.platanocafe.ca
Description: LearnDash Add On that integrates with the Paid Memberships Pro to allow access to all courses.  When an user adquires a membership he gets access to every single course regardless of the price for each course.  This plugin doesn't modify course's prices neither their settings.
Version: 0.0.2
Author: Adrian Toro
Author URI: http://www.platanocafe.ca
*/


/* subscribe new members to all courses when they registrer */
/* -------------------------------------------------------------------------------- */
function ldpmp_fullaccess_pmpro_after_change_membership_level($level_id, $user_id) {
	
	// Get all courses:	
	$filter = array( 'post_type' => 'sfwd-courses', 'posts_per_page' => -1, 'post_status' => 'publish', 'cat' => 122 );
	$loop = new WP_Query( $filter );
	
	while ( $loop->have_posts() ) : $loop->the_post();
		$course_list[] = get_the_ID();
	endwhile;
	wp_reset_query(); 
	
	// Check if the user has access to some courses (paid courses):
	$paid_courses = get_user_meta( $user_id, '_previously_enrolled_courses', true );
	
	// TODO:  Let the admin choose which courses he wants to grant access tosfwd_lms_has_access
	
	
	// Get the current user membership level:
	$membership_level = pmpro_getMembershipLevelForUser($user_id); 
		
	// Decide if granting or revoking access for all courses:
		// TODO:  Currently membership will overwrite access to paid courses.  The plugin should preserve the info of who paid which course so when membership expires the user can still access his paid courses.
	
	if ( !empty($membership_level) && $membership_level->id >0 ):
		foreach ( $course_list as $course_id ) {
			if( !sfwd_lms_has_access($course_id, $user_id) )	{ 
				//Check if this user is already enrolled on this course.
				ld_update_course_access($user_id, $course_id, $remove = false);	
			}elseif ( empty($paid_courses) || !is_array($paid_courses) ) {
				// If the user already has access but our private variable "_previously_enrolled_courses" is not created preserve his access by saving the course ID into a variable:
				$paid_courses[] = $course_id; 
			}
		}
		update_user_meta( $user_id, '_previously_enrolled_courses', $paid_courses );
		
	elseif ( empty($membership_level) ):
		foreach ( $course_list as $course_id ) {
			if ( !in_array( $course_id, $paid_courses ) ) {
				ld_update_course_access($user_id, $course_id, $remove = true);	
			}
		}
	endif;
	
	
		
	
	
}
add_action("pmpro_after_change_membership_level", "ldpmp_fullaccess_pmpro_after_change_membership_level", 15, 2);

