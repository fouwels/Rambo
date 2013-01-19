<?php
	function respond($body)
	{
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?><Response><Sms>'.$body.'</Sms></Response>';
	}
	
	// Request
	$from = $_POST['From'];
	$to = $_POST['To'];
	$body = $_POST['Body'];
	
	// Validate
	if (!is_numeric($from)) die();
	if (!is_numeric($to)) die();
	
	// Database
	$db = new mysqli('localhost', 'root', 'root', 'rambo');
	
	$query = $db->query('SELECT * FROM users WHERE number = ' . $from);
	
	if (!$query->num_rows)
	{
		$parts = explode('\n', $body);
		
		if (count($parts) == 4)
		{
			$number = $from;
			$firstname = $db->real_escape_string($parts[0]);
			$lastname = $db->real_escape_string($parts[1]);
			$birthyear = date('Y', strtotime($parts[2] . ' years ago'));
			$gender = $db->real_escape_string($parts[3]);
			
			if ($db->query("INSERT INTO users (number, firstname, lastname, birthyear, gender) VALUES ($number, '$firstname', '$lastname', $birthyear, '$gender')") === TRUE)
				respond('You have been registered - welcome to RefUnited.');
			else
				respond('Registration failed.');
		}
		else
			respond('You need to register for RefUnited. Reply with these details, each separated by a new line.\n\nFirst name\nLast name\nAge\nGender (M/F)');
	}
	
	$db->close();
