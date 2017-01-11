<?php
/**
 * @author Fei Ren <feiren1995@g.ucla.edu>
 * @link(GitHub) https://github.com/Felix-Ren/
 */
 
// // initialize variables
// $deck_arr = [];

// *function definition section begin*
/** 
	Filter all players' hands and only keep the ones we are interested.
	@param 		array 	$playerCards 	complete array of all players' hands
	@param 		array 	$indexArr 		indicates players we are interested in.
	@returns	array
*/
function filterPlayer($playerCards, $indexArr)
{
	$ret = array();
	foreach($indexArr as $index){
		$ret["$index"] = $playerCards[$index];	
	}
	return $ret;	
}

/** 
	Decode cards from numbers to number (1-13) plus suits, eg. 13clubs
	@param 		string		$encrypted		a number representing the card including its suit info
	@returns	string
*/
function decode($encrypted)
{
	$ret = "";
	if($encrypted > 130000){
		$suit = "♠";             // clubs (♣), diamonds (♦), hearts (♥) and spades (♠)
		$rank = $encrypted / 1000000;
		$ret = $rank.$suit;
	} elseif($encrypted > 1300){
		$suit = "♥";
		$rank = $encrypted / 10000;
		$ret = $rank.$suit;
	} elseif($encrypted > 13){
		$suit = "♦";
		$rank = $encrypted / 100;
		$ret = $rank.$suit;
	} else{
		$suit = "♣";
		$rank = $encrypted;
		$ret = $rank.$suit;
	}
	return $ret;
}

/**	
	Create a deck of cards from exisiting txt files and shuffle it.
	@param 		string		$fileName 	name of the file used to import cards
	@returns 	array 		an array of numbers representing encrypted cards.
*/
function createDeck($fileName)
{
	$inputFile = fopen($fileName,"r") or die("Cannot find file!");
	if(!feof($inputFile)){
		$deck_str = fgets($inputFile);
		$deck_arr = explode(",", $deck_str);
	}
	fclose($inputFile);
	
	// shuffle the deck
	shuffle($deck_arr);
	return $deck_arr;
}

/**	
	Write the deck to the given txt files. (overwrite exisiting content)
	@param 		array 	$deck	the deck of cards after cards dealt to all players
	@param 		string 	$file 	the destination to output the deck to.
*/
function outputDeck($deck, $file)
{
	$deck_arr = implode(",", $deck);
	file_put_contents ($file , $deck_arr);
}

/**
	decode all cards and print each player's cards line by line 
	@param 		array 	$playerCards 	complete array of all players' hands
	@returns	string
 */
function printPlayerCard($playerCards)
{
	global $numOfPlayer;
	$msg = "";
	
	$msg = $msg."<h3>Cards each player holds: </h3>";
	for($i = 0; $i < $numOfPlayer; ++$i){
		$msg = $msg."Player";
		$msg = $msg.strval($i+1);
		$msg = $msg.": ";
		// loop through all cards this player has
		foreach($playerCards[$i] as $encryptedCard){
			$msg = $msg.decode($encryptedCard).", ";
		}
		$msg = $msg."<br/>";
	}
	return $msg;
}

/** 
	compare two cards for reverse sorting. 
	@param		mixed	$lhs 	left hand side card
	@param		mixed	$rhs 	right hand side card
	@returns 	int		1 if lhs is smaller
	@returns	int		-1 if lhs is larger
	@returns 	int		0 when lhs === rhs
*/
function cmp($lhs, $rhs)
{
	// connvert cards to numbers
	settype($lhs, "int");
	settype($rhs, "int");
	
	if($lhs === 1 && $rhs != 1){
		return -1; // indicating lhs is larger
	} elseif($rhs === 1 && $lhs != 1){
		return 1;
	} elseif($rhs != $lhs) {
		return ($lhs < $rhs) ? 1:-1;
	} else { // $rhs === $lhs
		return 0;
	}
}

/** 
	Sort 2d associative array according to cmp. Assume no suits.
	@param	array	$lhs_arr 	arrays of integers sorted in descending order
	@param 	array 	$rhs_arr 	arrays of integers sorted in descending order
	@returns 	int		1 if lhs is smaller
	@returns	int		-1 if lhs is larger
	@returns 	int		0 when lhs === rhs
*/
function cmp_2D($lhs_arr, $rhs_arr)
{
	reset($lhs_arr);	
	reset($rhs_arr);
	// $lhs_val = -1;
	// $rhs_val = -1;
	
	// loop through every element in both arrays and compare one by one
	while ($lhs_val = current($lhs_arr)) {
		$rhs_val = current($rhs_arr);
		if(cmp($lhs_val, $rhs_val) != 0){	
			return cmp($lhs_val, $rhs_val);
		}
		next($lhs_arr);
		next($rhs_arr);
	}
	// do{	
		// $lhs_val = current($lhs_arr);
		// $rhs_val = current($rhs_arr);	
		
		// if(cmp($lhs_val, $rhs_val) != 0){			
			// return cmp($lhs_val, $rhs_val);
		// }
	// } while(next($lhs_arr) && next($rhs_arr));
	return 0;
}

/**
	get rid of the suit symbol at the end of the cards in an array
	@param		array	$arr	array containing cards
 */
function eraseSuit(&$arr)
{
	foreach($arr as &$element){
		settype($element, "int");
	}
	unset($element);
}

/**
	Compare which player has high card
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array		array of the indexes of the winner. (0-3)
*/
function compareHigh($playerCards, $indexArr)
{
	$ret = array();
	
	// only keep certain players' hands according to $indexArr
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	uasort($givenPlayerHand, "cmp_2D"); #debug from here
	
	// get key of the first array of the sorted big array
	reset($givenPlayerHand);
	array_push($ret, key($givenPlayerHand));
	
	// check if there are multiple player with highest hands
	$highestHand = current($givenPlayerHand);
	next($givenPlayerHand);
	while ($nowHand = current($givenPlayerHand)) {
		if ($nowHand === $highestHand) {
			array_push($ret, key($givenPlayerHand));
		}
		next($givenPlayerHand);
	}
	
	return $ret;
}

/**	
	check which hand contains a pair of cards with same rank.
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array 		indices of hands with a pair
*/
function checkPair($playerCards,$indexArr)
{
	$ret = array();
	
	// only keep certain players' hands according to $indexArr
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// going over all players' hand
	foreach($givenPlayerHand as $key => &$player) {
		// check if any two neighbouring cards are of same rank
		reset($player);
		$prev_val = current($player);
		next($player);
		while ($now_val = current($player)) {
			if ($prev_val === $now_val) {
				array_push($ret, $key);
				break;
			}
			$prev_val = $now_val;
			next($player);
		}
	}
	unset($player);
	
	return $ret;
}

/** 
	compare the hands which have pair(s) based on the largest pair
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array		indices of the largest pair
*/
function comparePair($playerCards, $indexArr)
{
	$largestIndex = array();
	$largestPair = array(); // key is index of player's hand; value is the card number corresponding to the largest pair
	
	// only keep players' hands which have pairs
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// going over all players' hand  and store largest pair number to an associative array.
	// foreach($givenPlayerHand as $key => &$player) {
	foreach($givenPlayerHand as $key => $player) {
		// check if any two neighbouring cards are of same rank
		reset($player);
		$prev_val = current($player); // $prev_val and now_val are strings with suit!
		next($player);
		while ($now_val = current($player)) {
			// keep track of the number of largest pair 
			if ($prev_val === $now_val) {
				settype($now_val, "int");
				$largestPair["$key"] = $now_val;
				break;
			}
			$prev_val = $now_val;
			next($player);
		}
	}
	// unset($player);

	uasort($largestPair, "cmp");
		
	// put the hand index with the largest pair in the returning array
	reset($largestPair);
	array_push($largestIndex, key($largestPair));
	
	// check if there are multiple player with highest pair

	$prev = current($largestPair);
	next($largestPair);
	while ($now = current($largestPair)) {
		if ($now === $prev) {
			array_push($largestIndex, key($largestPair));
		}
		next($largestPair);
	}
	
	return $largestIndex;	
}

/**	
	check which hand contains two pairs of cards with same rank.
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array 		indices of hands with two pairs
*/
function checkTwoPair($playerCards,$indexArr)
{
	$ret = array();
	
	// only keep certain players' hands according to $indexArr
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// going over all players' hand
	foreach($givenPlayerHand as $key => &$player) {
		$counter = 0; // counter for number of pairs
		// check if any two neighbouring cards are of same rank
		reset($player);
		$prev_val = current($player);
		next($player);
		while ($now_val = current($player)) {
			if ($prev_val === $now_val) {
				++ $counter;
				$prev_val = next($player);
			} else {
				$prev_val = $now_val;
			}
			next($player);
		}
		if($counter === 2){ // This player's hand has two pairs
			array_push($ret, $key);
		}
	}
	unset($player);
	
	return $ret;
}

/** 
	Compare players' hands by the ranks of the two pairs
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function compareTwoPair($playerCards, $indexArr)
{
	$largestIndex = array();
	foreach($indexArr as $i){
		$pairRank["$i"] = array(); // $pairRank is an associative array holds ranks of pairs each player has
	}
	
	// only keep players' hands which have pairs
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// going over all players' hand  and store largest pair number to an associative array.
	foreach($givenPlayerHand as $key => $player) {
		// check if any two neighbouring cards are of same rank
		reset($player);
		$prev_val = current($player);
		next($player);
		while ($now_val = current($player)) {
			if ($prev_val === $now_val) {
				array_push($pairRank["$key"], $now_val);
				$prev_val = next($player);
			} else {
				$prev_val = $now_val;
			}
			next($player);
		}
	}

	uasort($pairRank, "cmp_2D");
	
	reset($pairRank);
	array_push($largestIndex, key($pairRank));
	
	// check if there are multiple player with highest pair
	$prev = current($pairRank);
	next($pairRank);
	while ($now = current($pairRank)) {
		if ($now === $prev) {
			array_push($largestIndex, key($pairRank));
		}
		next($pairRank);
	}
	
	return $largestIndex;		
}

/** 
	Check if any player's hand contains three of a kind
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function checkThree($playerCards,$indexArr)
{
	$ret = array();
	
	// only keep certain players' hands according to $indexArr
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// loop through each player's hand
	foreach($givenPlayerHand as $key => $player){
		// assuming each player has and only has five cards
		if(($player[0]===$player[2])||($player[1]===$player[3])||($player[2]===$player[4])){
			array_push($ret, $key);
		}
	}
	return $ret;
}

/** 
	Compare player's hands based the rank of their three of a kind
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function compareThree($playerCards, $indexArr)
{
	$largestIndex = array();
	$threeRank = array(); // key is index of player's hand; value is the card number corresponding to the three of a kind
	
	// only keep players' hands which have pairs
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// going over all players' hand  and store rank of three of a kind to an associative array.
	// foreach($givenPlayerHand as $key => &$player) {
	foreach($givenPlayerHand as $key => $player) {
		if($player[0] === $player[2]){
			$threeRank["$key"] = $player[0];
		} elseif($player[0] === $player[2]){
			$threeRank["$key"] = $player[1];
		} else {
			$threeRank["$key"] = $player[2];
		}
	}
	// unset($player);

	uasort($threeRank, "cmp");
		
	// put the hand index with the largest rank to the returning array
	reset($threeRank);
	array_push($largestIndex, key($threeRank));
	
	// check if there are multiple player with highest pair
	$prev = current($threeRank);
	next($threeRank);
	while ($now = current($threeRank)) {
		if ($now === $prev) {
			array_push($largestIndex, key($threeRank));
		}
		next($threeRank);
	}
	
	return $largestIndex;	
}

/** 
	Check if part of the given array satisfies that the value is one smaller the previous value. eg. 6,5,4,3,2
	@param 		array	$arr		array of cards the player has
	@param 		int		$i_start 	an index indicating the start position
	@param 		int		$i_end 		an index indicating the end position. Assume $i_end is strictly bigger than $i_start
	@returns 	bool	true if the requirement is satified.
	@returns 	bool	false if the requirement is not satified.
*/
function isDecrementOne($arr, $i_start, $i_end)
{
	$i = $i_start + 1;
	$expected = $arr[$i_start] - 1;
	while($i <= $i_end){
		if($arr[$i] != $expected){
			return false;
		}
		++$i;
		--$expected;
	}
	return true;
}

/** 
	Check if any player has a straight. "A" can be either above "K" or below "2"
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function checkStraight($playerCards,$indexArr)
{
	$ret = array();
	
	// only keep certain players' hands according to $indexArr
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// loop through each player's hand
	foreach($givenPlayerHand as $key => $player){
		if($player[0] === 1){
			if(($player[1] === 13)||($player[1] === 5)){
				if(isDecrementOne($player,1,4)){
					array_push($ret, $key);
				}
			}
		}else{
			if(isDecrementOne($player,0,4)){
				array_push($ret, $key);
			}
		}
	}
	return $ret;
}

/** 
	Compare player's hands based on the highest rank of straight
	@param		array	 	$playerCards	storing cards (int) players holds (e.g. [[10,9,8,7,6],[1,9,9,3,2]])
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function compareStraight($playerCards, $indexArr)
{
	$largestIndex = array();
	$highestRank = array(); // key is index of player's hand; value is the card number corresponding to the three of a kind
	
	// only keep players' hands which have pairs
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// going over all players' hand  and store rank of largest card in the straight.
	// foreach($givenPlayerHand as $key => &$player) {
	foreach($givenPlayerHand as $key => $player) {
		if($player[0] === 1){
			if($player[1] === 13){ // 1,13,12,11,10
				$highestRank["$key"] = 1;
			}else{ //1,5,4,3,2
				$highestRank["$key"] = 5;
			}
		} else {
			$highestRank["$key"] = $player[0];
		}
	}
	// unset($player);

	uasort($highestRank, "cmp");
		
	// put the hand index with the largest rank to the returning array
	reset($highestRank);
	array_push($largestIndex, key($highestRank));
	
	// check if there are multiple player with highest pair
	$prev = current($highestRank);
	next($highestRank);
	while ($now = current($highestRank)) {
		if ($now === $prev) {
			array_push($largestIndex, key($highestRank));
		}
		next($highestRank);
	}
	
	return $largestIndex;		
}

/** 
	Check if any player has a flush
	@param 		array		$playerCards	an array of arrays of strings representing the player's hand in descending order. e.g. [["13♦","8♣","7♣","4♣","3♣"],["10♣","6♣","6♥","6♠","4♣"]
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function checkFlush($playerCards,$indexArr)
{
	$ret = array();
	
	// only keep certain players' hands according to $indexArr
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// loop through each player's hand
	foreach($givenPlayerHand as $key => $player){
		if((substr($player[0],-1)===substr($player[1],-1))&&
			(substr($player[0],-1)===substr($player[2],-1))&&
			(substr($player[0],-1)===substr($player[3],-1))&&
			(substr($player[0],-1)===substr($player[4],-1))){
				array_push($ret, $key);	
		}
	}

	return $ret;
}

/** 
	Check if the player's hand satisfy two pairs and three of a kind simultaneously. Doesn't have to be a full house
	@param 		array		$playerCards	an array of arrays of strings representing the player's hand in descending order. e.g. [["13♦","8♣","7♣","4♣","3♣"],["10♣","6♣","6♥","6♠","4♣"]
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function checkFullHouse($playerCards,$indexArr)
{
	// get rid of suits to use checkTwoPair and checkThree	
	foreach($playerCards as &$player) {
		eraseSuit($player);
	}
	unset($player);
	
	$ret = checkThree($playerCards, $indexArr);
	$ret = checkTwoPair($playerCards, $ret);

	return $ret;
}

/** 
	Check if any player has four of a kind
	@param 		array		$playerCards	an array of arrays of strings representing the player's hand in descending order. e.g. [["13♦","8♣","7♣","4♣","3♣"],["10♣","6♣","6♥","6♠","4♣"]
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array		of indices of players with four of a kind
*/
function checkFour($playerCards,$indexArr)
{
	$ret = array();
	
	// only keep certain players' hands according to $indexArr
	$givenPlayerHand = filterPlayer($playerCards, $indexArr);
	
	// get rid of suits to use checkTwoPair and checkThree	
	foreach($givenPlayerHand as &$player) {
		eraseSuit($player);
	}
	unset($player);

	// loop through each player's hand
	foreach($givenPlayerHand as $key => $player){
		if(($player[0]===$player[3])||($player[1]===$player[4])){
				array_push($ret, $key);	
		}
	}
	return $ret;
}

/** 
	Check if any player has straight flush
	@param 		array		$playerCards	an array of arrays of strings representing the player's hand in descending order. e.g. [["13♦","8♣","7♣","4♣","3♣"],["10♣","6♣","6♥","6♠","4♣"]
	@param 		array		$indexArr 		array of the indexes of the winner candidates.
	@returns 	array
*/
function checkStraightFlush($playerCards,$indexArr)
{
	$ret = checkFlush($playerCards, $indexArr);
	
	// get rid of suits to use checkStraight	
	foreach($playerCards as &$player) {
		eraseSuit($player);
	}
	unset($player);

	$ret = checkStraight($playerCards, $ret);
	
	return $ret;
}

/** 
	ckeck the winner with high rules
	@param 		array	$playerCards	an array storing cards players holds
	@returns	mixed	the number of winner (1-4) [int] or string of winner numbers when multiple players win.
*/
function checkWinner($playerCards, $numOfPlayer)
{
	// set default winner candidates to include all players
	$winnerCandidates = range(0, $numOfPlayer-1);
	
	// decode all cards and sort in descending order
	foreach($playerCards as &$player){
		foreach($player as &$card){
			$card = decode($card);
		}
		unset($card);
		usort($player, "cmp"); // basically sort cards from high to low but 1 is the largest
	}
	unset($player);
	
	#!! now $playerCards looks something like [["13♦","8♣","7♣","4♣","3♣"],["10♣","6♣","6♥","6♠","4♣"],["1♣","13♠","11♦","2♣","2♠"]]
	
	// straight flush 
	$theQualified = checkStraightFlush($playerCards,$winnerCandidates); #array of index (0-3)
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){
		$winnerCandidates = $theQualified;
	} 
	
	// Four of a kind
	$theQualified = checkFour($playerCards,$winnerCandidates);
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){
		$winnerCandidates = $theQualified;
	} 
	
	// Full house
	$theQualified = checkFullHouse($playerCards,$winnerCandidates); #array of index (0-3)
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){
		$winnerCandidates = $theQualified;
	} 

	// Flush
	$theQualified = checkFlush($playerCards,$winnerCandidates); #array of index (0-3)
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){ // if there are multiple players with flush, jump to compare the highest singal card
		// get rid of suits of cards players have 
		foreach($playerCards as &$player) {
			eraseSuit($player);
		}
		unset($player);
		
		$winner = compareHigh($playerCards, $theQualified);
		if(count($winner) === 1){
			return $winner[0]+1;
		}else{ // multiple winners
			foreach($winner as &$value){
				++$value;
			}
			unset($value);
			
			// convert array to string
			return implode(",", $winner);
		}	
	} 
	
	// get rid of suits of cards players have 
	foreach($playerCards as &$player) {
		eraseSuit($player);
	}
	unset($player);
	#now $playerCards looks something like [[13,8,7,4,3],[10,6,6,6,4],[1,13,11,2,2]]
	
	// straight
	$theQualified = checkStraight($playerCards,$winnerCandidates); #array of index (0-3)
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){
		$winnerCandidates = compareStraight($playerCards, $theQualified);
		if(count($winnerCandidates) === 1){
			return $winnerCandidates[0]+1;
		}
	} 
		
	// Three of a kind
	$theQualified = checkThree($playerCards,$winnerCandidates); #array of index (0-3)
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){
		$winnerCandidates = compareThree($playerCards, $theQualified);
		if(count($winnerCandidates) === 1){
			return $winnerCandidates[0]+1;
		} else { // compare the kickers
			$winner = compareHigh($playerCards, $winnerCandidates);
			if(count($winner) === 1){
				return $winner[0]+1;
			}else{ // multiple winners
				foreach($winner as &$value){
					++$value;
				}
				unset($value);
				
				// convert array to string
				return implode(",", $winner);
			}	
		}
	} 
	
	// Two pair
	$theQualified = checkTwoPair($playerCards,$winnerCandidates); #array of index (0-3)
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){
		$winnerCandidates = compareTwoPair($playerCards, $theQualified);
		if(count($winnerCandidates) === 1){
			return $winnerCandidates[0]+1;
		}
	} 
	
	// One Pair
	$theQualified = checkPair($playerCards,$winnerCandidates); #array of index (0-3)
	if(count($theQualified) === 1){
		return $theQualified[0]+1;
	} elseif(count($theQualified)>1){
		$winnerCandidates = comparePair($playerCards, $theQualified);
		if(count($winnerCandidates) === 1){
			return $winnerCandidates[0]+1;
		}
	} 
	
	// High card
	$winner = compareHigh($playerCards, $winnerCandidates);
	if(count($winner) === 1){
		return $winner[0]+1;
	}else{ // multiple winners
		foreach($winner as &$value){
			++$value;
		}
		unset($value);
		
		// convert array to string
		return implode(",", $winner);
	}
}
// *function definition section end*

// if ($_SERVER['REQUEST_METHOD'] === "POST") { 
// get data from ajax call
$numOfPlayer = $_POST['num'];
$isNewGame = $_POST['isNewGame']; // $isNewGame is a string

// read in deck of cards
//if($isNewGame){
if($isNewGame === "true"){
	$deck = createDeck("completeDeckOfCards.txt");
}else{
	$deck = createDeck("deckOfCards.txt");
}

// Deal cards to each player
$playerCards = [];
$nowPlayer = 0; // index
for($i = 1; $i <= $numOfPlayer; ++$i){
	array_push($playerCards,array());
}
while(count($playerCards[$numOfPlayer-1]) < 5){
	if(count($deck) <= 0 ){// reshuffle whole deck when all cards in the deck dealt
		$deck = createDeck("completeDeckOfCards.txt");
	}
	array_push($playerCards[$nowPlayer],end($deck));
	array_pop($deck);
	
	// update player to be dealt with cards in next round
	if($nowPlayer +1 >= $numOfPlayer){
		$nowPlayer = ($nowPlayer + 1) % $numOfPlayer; 
	} else{
		++$nowPlayer;
	}
}

// output the remaining deck to "deckOfCards.txt" in case the user wants to continue
outputDeck($deck, "deckOfCards.txt");

// check winner
$winner = checkWinner($playerCards, $numOfPlayer);

// return data back to html file
$info = printPlayerCard($playerCards);

$retBackToHTML = array("playerCardInfo" => "$info", "winner" => "$winner");
echo JSON_encode($retBackToHTML);
