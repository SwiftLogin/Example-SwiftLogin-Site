<?php
// Start the session
session_start();

// URL the user is returned to after loging in (i.e. this page)
$login_url = 'https://swiftlogin.com/login?url='. rawurlencode('http://'. $_SERVER['HTTP_HOST']. '?extra=params&for=example');

function verify_swiftlogin($key)
{
	$ch = curl_init('https://swiftlogin.com/verify?key='. $key);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	
	if( ! $result = curl_exec($ch)) 
    { 
        trigger_error(curl_error($ch)); // <--- Error handling here
    }
    
    curl_close($ch); 
    return json_decode($result); 
}

////////////////////////////////////////////////////////////////////
// Login Request?
if(!empty($_GET['key']) AND is_string($_GET['key']))
{
	if($response = verify_swiftlogin($_GET['key']))
	{
		//print var_dump($response);
		
		// Valid response object (with a transmission timestamp less than a minute ago)
		if($response AND empty($response->error) AND $response->timestamp < (time()+60))
		{
			// Log user in by saving user information
			$_SESSION['email'] = $response->email;
			$user = explode('@',$response->email,2);
			$_SESSION['username'] = $user[0];
			$_SESSION['website'] = $user[1];
		}
		else
		{
			print '<h2>Invalid Login Key: '. $_GET['key'].'</h2>';
		}
	}
}



////////////////////////////////////////////////////////////////////
// Logout request?
if(isset($_GET['logout']))
{
	// Destroy the session and all data
	$_SESSION = array();
	session_destroy();
	
	// Then restart the session
	session_start();
}

?>
<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<title>Example Site using Swiftlogin.com</title>
	<style type="text/css">
	html, body, ul, ol, li, h1, h2 {margin: 0;padding: 0;}
	body {font: 1em/1.5em Verdana;background: #fff;color: #333;}
	h1, h2 {font-weight: normal; font-family: arial;}
	pre {white-space:pre; padding: 2em; background: #ddd;}
	#container {max-width: 600px;margin: 0px auto;padding: 0 2em;}
	#header {height: 100px;line-height: 100px;}
	#header h1 {float: left;}
	#header ul li {float: right;display:inline;}
	#main {background: #f1f1f1;padding: 2em;-moz-border-radius: 4px;}
	.gravatar { float: left; margin: 0 1em 1em 0; padding: 3px; background: #fff;}
	</style>
</head>
<body>
<div id="container">
	<div id="header">
		<h1>Example Site</h1>
		
		<ul>
			<?php if(empty($_SESSION['email'])) { ?>
				<li><a href="<?php print $login_url; ?>">Login / Register</a>
			<?php } else { ?>
				<li><a href="?logout=TRUE">Logout</a></li>
			<?php } ?>
		</ul>
	</div>
	
	<div id="main">
		<?php if(empty($_SESSION['email'])) { ?>
		
			<h2>Please Login</h2>
			<p>You are not logged in. Please login to access this page</p>
			
		<?php } else { ?>
		
			<img src="http://www.gravatar.com/avatar/<?php print md5($_SESSION['email']); ?>?s=80&d=wavatar&r=PG" alt="Gravatar Image" />
			
			<h2>Welcome <?php print $_SESSION['username']. ' from '. $_SESSION['website']; ?>!</h2>
			<p>You are now logged in with the email <?php print $_SESSION['email']; ?> and can access special member-only content!</p>
			
		<?php } ?>
		
		<h2>About</h2>
		<p>This site is an example of how easy it is to implement a login using PHP and SwiftLogin.com.
		All you need is some simple code to fetch a reponse from <a href="http://swiftlogin.com">SwiftLogin.com</a> 
		and you're ready to roll! You can even add custom URI parameters to the callback url and they 
		will be preserved.</p>
		
		<pre><b>$_GET Data:</b><br /><?php print_r($_GET); ?></pre>
	</div>
</div>
</body>
</html>
