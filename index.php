<?php session_start();

$cards = array('2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, 'T' => 10, 'J' => 10, 'Q' => 10, 'K' => 10, 'A' => 11);
$types = array('C', 'S', 'H', 'D');

$winner = false;

function notification($msg)
{
	return '<div class="alert alert-info" role="alert">'.$msg.'</div>';
}

function pullCard()
{
	global $cards;
	global $types;
	
	$tmp['card'] = array_rand($cards);
	$tmp['type'] = $types[array_rand($types)];
	
	while(in_array($tmp['card'].$tmp['type'], $_SESSION['used_cards']))
	{
		$tmp['card'] = array_rand($cards);
		$tmp['type'] = $types[array_rand($types)];
	}
	
	$_SESSION['used_cards'][] = $tmp['card'].$tmp['type'];
	
	return $tmp;
}

function calcCards($hand)
{
	global $cards;
	
	$count = 0;
	$aces = 0;
	
	foreach($hand as $card)
	{
		if($card['card'] != "A")
		{
			$count += $cards[$card['card']];
		}
		else
		{
			$aces++;
		}
	}
	
	for($x=0; $x<$aces; $x++)
	{
		if($count+11 > 21)
		{
			$count += 1;
		}
		else
		{
			$count+= 11;
		}
	}
	
	return $count;
}

function checkWinner($player, $computer)
{	
	global $winner;
	if($winner != true)
	{
		// First check if the player is > 21
		if(calcCards($player) == 21 && calcCards($computer) == 21)
		{
			print notification("Tie game");
			$winner = true;
		}
		elseif(calcCards($player) > 21 || calcCards($computer) == 21)
		{
			print notification("You lost, the computer won");
			$winner = true;
		}
		elseif(calcCards($computer) > 21 || calcCards($player) == 21)
		{
			print notification("You won, the computer lost");
			$winner = true;
		}
		else
		{
			return false;
		}
	}
}

if($_POST['reset'])
{
	session_destroy();
	session_start();
}

if(!$_SESSION['hand_player'])
{
	$_SESSION['used_cards'] = array();
	
	$hand_player = array();
	$hand_computer = array();
	
	// Initial cards player
	array_push($hand_player, pullCard());
	array_push($hand_player, pullCard());
	
	// Initial cards computer
	array_push($hand_computer, pullCard());
	array_push($hand_computer, pullCard());
	
	$_SESSION['hand_player'] = $hand_player;
	$_SESSION['hand_computer'] = $hand_computer;
	
}
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
		<title>Blackjack!</title>
	</head>
	<body>
		<div class="container">
		
			<?php
			if($_POST['hit'] && $winner != true)
			{
				array_push($_SESSION['hand_player'], pullCard());
			}
			elseif($_POST['stand'] && $winner != true)
			{
				while(calcCards($_SESSION['hand_player']) > calcCards($_SESSION['hand_computer']))
				{
					array_push($_SESSION['hand_computer'], pullCard());
					
					if(calcCards($_SESSION['hand_computer']) == 21 && calcCards($_SESSION['hand_player']) == 21)
					{
						print notification("Tie game");
						$winner = true;
						continue;
					}
					elseif(calcCards($_SESSION['hand_computer']) > 21)
					{
						print notification("You won, the computer lost");
						$winner = true;
						continue;
					}
					elseif((calcCards($_SESSION['hand_player']) < calcCards($_SESSION['hand_computer']) || calcCards($_SESSION['hand_computer']) == 21))
					{
						print notification("You lost, the computer won");
						$winner = true;
						continue;
					}
				}
				
				if(calcCards($_SESSION['hand_player']) == calcCards($_SESSION['hand_computer']))
				{
					print notification("Tie game");
					$winner = true;
				}
				
				if((calcCards($_SESSION['hand_computer']) > calcCards($_SESSION['hand_player'])) && $winner != true)
				{
					print notification("You lost, the computer won");
					$winner = true;
				}
			}
			
			checkWinner($_SESSION['hand_player'], $_SESSION['hand_computer']);
			
			?>
			<center><h2>Hand computer</h2></center>
			<table align="center">
				<tr>
				<?php $comp_count = 0; ?>
				<?php foreach($_SESSION['hand_computer'] as $card): ?>
					<td align="center">
						<?php if($comp_count != 0 || $winner == true): ?>
						<img src="./cards/<?=$card['card'].$card['type']?>.gif" /><br>
						(<?=$cards[$card['card']]?>)
						<?php else: ?>
						<img src="./cards/closed.gif" /><br>
						(?)
						<?php endif; ?>
					</td>
					<?php $comp_count++; ?>
				<?php endforeach; ?>
				</tr>
				<?php if($winner == true): ?>
				<tr>
					<td colspan="<?=count($_SESSION['hand_computer']) ?>" align="center">Total: <?=calcCards($_SESSION['hand_computer'])?></td>
				</tr>
				<?php endif; ?>
			</table>
			
			<center><h2>Your hand</h2></center>
			<form method="post">
				<table align="center">
					<tr>
					<?php foreach($_SESSION['hand_player'] as $card): ?>
						<td align="center">
							<img src="./cards/<?=$card['card'].$card['type']?>.gif" /><br>
							(<?=$cards[$card['card']]?>)
						</td>
					<?php endforeach; ?>
					</tr>
					<tr>
						<td colspan="<?=count($_SESSION['hand_player']) ?>" align="center">Total: <?=calcCards($_SESSION['hand_player'])?></td>
					</tr>
					<tr>
						<td colspan="<?=count($_SESSION['hand_player']) ?>" align="center"><input type="submit" name="hit" class="btn btn-primary" value="Hit" /> || <input type="submit" name="stand" class="btn btn-primary" value="Stand" /></td>
					</tr>
					<tr height="100" valign="middle">
						<td colspan="<?=count($_SESSION['hand_player']) ?>" align="center"><input type="submit" name="reset" class="btn btn-primary" value="Reset" /></td>
					</tr>
				</table>
			</form>
		</div>
	</body>
</html>
	<?php
	
if($winner == true)
{
	session_destroy();
}
	
