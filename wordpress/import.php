<?php
/**
 *
 * @package smwimport
 */

ignore_user_abort(true);

if ( !defined('ABSPATH') ) {
	/** Set up WordPress environment */
	require_once('./wp-load.php');
}
require_once(ABSPATH . "wp-content" . '/plugins/smw-import/smwimport.php');
?>

<html>
<head>
</head>
<body>
<?php
	$ret = smwimport::import_all();

	if ( is_wp_error($ret) ){
		$message = $ret->get_error_message();
		$class = "error";
	}else $message = 'Successfully imported.'."</br>".$ret;
	
	echo "$message"
?>
</body>
</html>
