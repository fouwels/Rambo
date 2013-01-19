<?php
	function respond($body)
	{
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?><Response><Sms>'.$body.'</Sms></Response>';
	}
	
	require('lib/Twilio.php');
	$twilio = new Services_Twilio('ACa4435bbbdc9345a589be43e4f9924027', '273569f71f594bfee347a71c2d9c51c5');
	
	// Request
	$from = $_POST['From'];
	$to = $_POST['To'];
	$body = $_POST['Body'];
	
	// Database
	$db = new mysqli('localhost', 'root', 'root', 'rambo');
	
	$user = $db->query("SELECT * FROM users WHERE number = '$from'");
	
	if (!$user->num_rows)
	{
		$parts = explode("\n", $body);
		
		if (count($parts) == 4)
		{
			$number = $from;
			$firstname = $db->real_escape_string($parts[0]);
			$lastname = $db->real_escape_string($parts[1]);
			$birthyear = date('Y', strtotime($parts[2] . ' years ago'));
			$gender = $db->real_escape_string($parts[3]);
			
			if ($db->query("INSERT INTO users (number, firstname, lastname, birthyear, gender) VALUES ('$number', '$firstname', '$lastname', $birthyear, '$gender')") === TRUE)
				respond('You have been registered - welcome to RefUnited.');
			else
				respond('Registration failed.');
		}
		else
			respond("You need to register for RefUnited. Reply with these details, each separated by a new line.\n\nFirst name\nLast name\nAge\nGender (M/F)");
	}
	else
	{
		$user = $user->fetch_row();
		$parts = explode(' ', $body, 2);
		
		switch (strtolower($parts[0]))
		{
			case 'search':
				$query = $db->query("SELECT * FROM users WHERE number <> '$from'");
				
				while ($row = $query->fetch_row())
				{
					$sms = $client->account->sms_messages->create(
						'+442033229191',
						$row[0],
						"Do you know $user[1] $user[2]? If you do, reply CONFIRM followed by a question only $user[1] would know the answer to."
					);
				}
				break;
			
			case 'confirm':
				$sms = $client->account->sms_messages->create(
						'+442033229191',
						'+447857698335',
						"Security question. Reply ANSWER followed by your answer.\n\n$parts[1]"
					);
				break;
			
			case 'answer':
				$sms = $client->account->sms_messages->create(
						'+442033229191',
						'+447842073150',
						"James responded with this answer. Reply APPROVE to approve connection.\n\n$parts[1]"
					);
				break;
			
			case 'approve':
				$sms = $client->account->sms_messages->create(
						'+442033229191',
						'+447857698335',
						"Your connection request was approved."
					);
				break;
			
			case 'ping':
				respond('pong!');
				break;

			default:
				respond('Please enter a valid command: search');
				break;
		}
	}	
	$db->close();
