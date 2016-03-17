<?php
// don't load directly 

$nonce=$_REQUEST['_wpnonce'];
if ( isset( $nonce ) ) {
	if( !(@include $_SERVER['DOCUMENT_ROOT'].'/wp-load.php') )
		if( !(@include $_SERVER['DOCUMENT_ROOT'].'../wp-load.php') )
		if( !(@include 'wp-load.php') )
		if( !(@include '../../../wp-load.php') )
		if( !(@include '../../../../wp-load.php') )
		if( !(@include '../../../../../wp-load.php') )
			die('<H1>Can\'t include wp-load. Report to Technical Support form on http://etruel.com/support</H1>');

	include(ABSPATH.'wp-includes/pluggable.php');
	if(!wp_verify_nonce($nonce, 'preview-nonce') ) wp_die('Are you sure?'); 
}
if ( isset( $_GET['p'] ) )
 	$term_id = $term_ID = (int) $_GET['p'];
elseif ( isset( $_POST['term_ID'] ) )
 	$term_id = $term_ID = (int) $_POST['term_ID'];
else
 	$term_id = $term_ID = 0;

$content = get_term_meta( $term_id, 'content');
$content = isset($content[0])?$content[0]:'';

echo stripslashes($content);

/*?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>Last Log of Campaign <?php echo $term_id.": ".get_the_title($term_id); ?></h1>
<?php
echo $log;

?></body>
</html>
 */