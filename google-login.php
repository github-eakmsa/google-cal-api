<?php
session_start();

require_once('google-calendar-api.php');
require_once('settings.php');

// a script to handle redirection and grab response data
// Google passes a parameter 'code' in the Redirect Url
if(isset($_GET['code'])) {
	try {
		$capi = new GoogleCalendarApi();

		// Get the access token
		$data = $capi->GetAccessToken(CLIENT_ID, CLIENT_REDIRECT_URL, CLIENT_SECRET, $_GET['code']);

		// Save the access token as a session variable
		$_SESSION['access_token'] = $data['access_token'];

		// Redirect to the page where user can create event
		header('Location: index.php');
		exit();
	}
	catch(Exception $e) {
		echo $e->getMessage();
		exit();
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Login with Google</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>

<?php

$login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';

?>

<!--  app title -->
<div>
  <h1>Google Caldendar Events</h1>
</div>

<!-- login btn -->
<div class="">
<h4>
<a id="logo" href="<?= $login_url ?>">
	Login with Google
</a>
</h4>
</div>

</body>
</html>
