<?php
/**
 * Fakturo description of Help Texts Array
 * -------------------------------
 * array('Text for left tab link' => array(
 * 	'field_name' => array( 
 * 		'title' => 'Text showed as bold in right side' , 
 * 		'tip' => 'Text html shown below the title in right side and also can be used for mouse over tips.' , 
 * 		'plustip' => 'Text html added below "tip" in right side in a new paragraph.',
 * )));
 */

$helptexts = array( 
	'PRODUCTS' => array( 
		'tabtitle' =>  __('Product Options', 'fakturo' ),
		'feeds' => array( 
			'title' => __('Feeds URLs.', 'fakturo' ),
			'tip' => __('You must type at least one feed url.', 'fakturo' ).'  '.
				__('(Less feeds equal less used resources when fetching).', 'fakturo' ).' '.
				__('Type the domain name to try to autodetect the feed url.', 'fakturo' ),
		),
		'itemfetch' => array( 
			'title' => __('Max items per Fetch.', 'wpematico' ),
			'tip' => __('Items to fetch PER every feed above.', 'wpematico' ).'  '.
				__('Recommended values are between 3 and 5 fetching more times to not lose items.', 'wpematico' ).'  '.
				__('Set it to 0 for unlimited.', 'wpematico' ),
		),
		'itemdate' => array( 
			'title' => __('Use feed item date.', 'wpematico' ),
			'tip' => __('Use the original date from the post instead of the time the post is created by WPeMatico.', 'wpematico' ).'  '.
				__('To avoid incoherent dates due to lousy setup feeds, WPeMatico will use the feed date only if these conditions are met:', 'wpematico' ).'  '.
				'<ul style=\'list-style-type: square;margin:0 0 5px 20px;font:0.92em "Lucida Grande","Verdana";\'>
				<li>'. __('The feed item date is not too far in the past (specifically, as much time as the campaign frequency).', 'wpematico' ).' </li>
				<li>'. __('The fetched feed item date is not in the future.', 'wpematico' ).' </li></ul>',
		),
	),
	'SECOND' => array( 
		'tabtitle' =>  __('2ND TAB', 'fakturo' ),
		'feed_url' => array( 
			'title' => __('Youtube feeds URLs.', 'wpematico' ),
			'tip' => __('Channel Videos feed and User Videos feed.', 'wpematico' ).
				'<br>'.__('Fill in the feed URL field in the standard way.', 'wpematico' ).
				'<br><br>'.__('For Youtube Channel as: https://www.youtube.com/feeds/videos.xml?channel_id=%channelid%', 'wpematico' ).
				'<br>'.__('For Youtube User as: https://www.youtube.com/feeds/videos.xml?user=%username%', 'wpematico' ).
				'<br><br>'.__('The campaign fetches the title, the image, the embebed video and the description.', 'wpematico' ),
		),
	),
	'Schedule Options' => array( 
		'schedule' => array( 
			'title' => __('Activate Scheduling.', 'wpematico' ),
			'tip' => __('Activate Automatic Mode.', 'wpematico' ).
				'<br>'.__('You can define here on what times you wants to fetch this feeds.  This has 5 min. of margin on WP-cron schedules.  If you set up an external cron en WPeMatico Settings, you\'ll get better preciseness.', 'wpematico' ),
			'plustip' => __('You can see some examples here:', 'wpematico' ) . ' <a href="https://etruel.com/question/use-cron-scheduling/" target="_blank">'.__('How to use the CRON scheduling ?', 'wpematico' ) .'</a>',
		),
	),
);


?>