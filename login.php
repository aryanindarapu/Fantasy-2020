<?php
$error = null;
$loggedIn = false;
if(isset($_POST['username'])){
	/*
		$username set to form entered username
		$password set to form entered password
	*/
	$username = $_POST['username'];
	$password = $_POST['password'];
	//Set $email as global
	$email = true;
	//Check with RegEx for email
	if(preg_match('~(\w+)@(gmail|yahoo|icloud|hotmail|outlook|aol)(\.com|\.net)~', $username)) {
		$email = true;
	} else {
		$email = false;
	}
	//Hash password using whirlpool
	$hashed_password = hash('whirlpool', $password);
	//Connect to database
	$conn = mysqli_connect('localhost','loginUser','techhounds','fantasyfrc');
	//State if error in connecting to database
	if(mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	//Set $query as global
	$query = null;
	//Select lines from database where either email or username is $username
	if($email) {
		$query = "SELECT * FROM fantasyusers WHERE email='".$username."'";
	} else {
		$query = "SELECT * FROM fantasyusers WHERE name='".$username."'";
	}
	//$result stores number of rows and information from each row that returns from fuction above
	$result = mysqli_query($conn, $query);

	/*
		If no rows match $username, return an error
		If a row matches the $username value:
			- select row identified above
			- set $username to stored database username (Non-case-sensitivity purposes & so username will be stored and not email)
			- set $dpass to hashed password stored in database
			- Check if $dpass is equal to $hashed_password. If so:
				- set $loggedIn to true
				- set $online to 1 (true)
				- set $query "online" value to 1 (true)
				- sends $query to database, updating it
	*/
	if(mysqli_num_rows($result) === 0) {
		$error = "Incorrect Username and/or Password";
	} else {
		$row = mysqli_fetch_assoc($result);
		$username = $row['name'];
		$dbpass = $row['password'];
		if($hashed_password === $dbpass) {
			$loggedIn = true;
			$online = 1;
			$query = "UPDATE fantasyusers SET online = 1 WHERE id='".$row['id']."'";
			mysqli_query($conn,$query);
		}
	}
}

/*
	If $loggedIn is true:
		- echo $username to console
		- set cookie with name "username" to value of logged in username from above function
		- set cookie to expire in 30 days
		- redirect to index (home) page
*/
if($loggedIn) {
	$cookie_name = "username";
	$cookie_value = $username;
	echo "<script>console.log('" . $cookie_value . "');</script>";
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
	echo "<script>window.location.href = './index.php'</script>";
}
?>

<html>
	<head>
        <title>Login - FantasyFRC</title>
		<link rel="stylesheet" type="text/css" href="fantasy.css?version=12">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="fantasy.js?version=12"></script>
		<link href="https://fonts.googleapis.com/css?family=Oswald&display=swap" rel="stylesheet">
			<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.4.1.js"integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	</head>
	<body style="background-color:#cccccc; font-family: 'Oswald', sans-serif; letter-spacing: .05em;" onload="loggedIn();">
		<script src="https://kit.fontawesome.com/3ca07295df.js" crossorigin="anonymous"></script>
		<script src='nav.js?version=12'></script>
        <div class="box">
            <form method="post" name="login" action="">
                <h1 class="registerHeader">Login</h1>
				<label for="username" class="input">Username:</label></br>
				<input name="username" id="username" placeholder="Username" type="text" class="registerTextBoxes" style="width:80%" required></br>
				<label for="password" class="input">Password:</label></br>
				<input name="password" id="password" placeholder="Password" type="password" class="registerTextBoxes" style="width:80%" required></br></br>
				<input type="submit" value="Login" class="button"/>
            </form>
        </div>
	</body>
</html>
