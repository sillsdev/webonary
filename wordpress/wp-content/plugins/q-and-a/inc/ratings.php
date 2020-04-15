<?php

function qa_add_custom_fields($post_ID) {
	global $wpdb;
	if(!wp_is_post_revision($post_ID)) {
		add_post_meta($post_ID, 'votes_count', '0', true);
	}
}

add_action('publish_qa_faqs', 'qa_add_custom_fields');