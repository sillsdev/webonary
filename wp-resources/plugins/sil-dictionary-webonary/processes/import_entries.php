<?php
if (isset($xhtmlFileURL) && isset($filetype) && isset($user)) {
	echo 'This functionality has been disabled.';
}
else {
	error_log("Programming Error: File name ($xhtmlFileURL) and type ($filetype) user record ($user->ID) must be set before importing entries!");
}
