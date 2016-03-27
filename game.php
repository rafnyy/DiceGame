<?php

$sides_of_die = 6;
$number_of_players = 4;
$number_of_rounds = $number_of_players;
$dice_per_round = 5;

$players = array();
for ($i = 1; $i <= $number_of_players; $i++) {
    echo "Please enter Player " . $i . "'s name: " . PHP_EOL;
    $name = read_stdin();

    while(array_key_exists($name, $players)) {
        echo "That name is already in use, please try another: " . PHP_EOL;
        $name = read_stdin();
    }

    // Add name to $players so that the key is the name and the value is the player's current score
    $players[$name] = 0;
}

// Randomly set the order or the players, and thus who starts
shuffle_assoc($players);

for($round = 1; $round <= $number_of_rounds; $round++) {

    foreach($players as $player => $score) {
        echo PHP_EOL . "########################################" . PHP_EOL;
        echo $player . "'s turn!!!" . PHP_EOL;

        $saved_dice = 0;

        while($saved_dice < $dice_per_round) {
            $saved_dice_this_roll = array();

            echo PHP_EOL . "Roll!!!" . PHP_EOL;
            $dice_to_roll = $dice_per_round - $saved_dice;
            $dice = roll_dice($dice_to_roll);
            print_roll($dice);

            echo "Please select one die to save: " . PHP_EOL;
            read_and_record_die_selection($dice_to_roll, True);

            $done = False;
            while(!$done && $saved_dice < $dice_per_round) {
                echo "Type ROLL, to roll the remaining dice, or select another die to save:" . PHP_EOL;
                read_and_record_die_selection($dice_to_roll, False);
            }

            $players[$player] += sum_dice($saved_dice_this_roll);
        }
    }

    // Move the first player to the back of the associative array so they go last next round
    reset($players);
    $first_key = key($players);
    $first_player = array($first_key => array_shift($players));
    $players = array_merge($players, $first_player);

    // skip displaying last round's score since we will later show ordered final score
    if($round != $number_of_rounds) {
        echo "Scores after round " . $round . PHP_EOL;
        print_r($players);
    }
}
asort($players);
echo "Final Scores!!!" . PHP_EOL;
print_r($players);

// our function to read from the command line
function read_stdin()
{
        $fr=fopen("php://stdin","r");   // open our file pointer to read from stdin
        $input = fgets($fr, 128);       // read a maximum of 128 characters
        $input = rtrim($input);         // trim any trailing spaces.
        fclose ($fr);                   // close the file handle
        return $input;                  // return the text entered
}

function read_and_record_die_selection($dice_to_roll, $firstRoll) {
    $die_selected = read_stdin();

    while(!is_valid_die($dice_to_roll, $die_selected) && ($firstRoll || !is_roll_string($die_selected))) {
        echo "That is not a valid selection, please try another: " . PHP_EOL;
        $die_selected = read_stdin();
    }

    if(is_roll_string($die_selected)) {
        $GLOBALS['done'] = True;
    } else {
        $GLOBALS['saved_dice']++;
        $GLOBALS['saved_dice_this_roll'][$die_selected] = $GLOBALS['dice'][$die_selected];
    }
}

function shuffle_assoc(&$array) {
    $keys = array_keys($array);

    shuffle($keys);

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
}

function is_valid_die($number_of_dice_rolled, $input) {
    return ctype_digit($input) 
            && 0 <= $input && $input < $number_of_dice_rolled 
            && !array_key_exists($input, $GLOBALS['saved_dice_this_roll']);
}

function is_roll_string($input) {
    return strcasecmp($input, 'roll') == 0;
}

function roll_dice($numberOfDice) {
    $dice = array();
    for($i = 0; $i < $numberOfDice; $i++) {
        array_push($dice, rand(1, $GLOBALS['sides_of_die']));
    }

    return $dice;
}

function sum_dice($dice) {
    $sum = 0;
    foreach($dice as $die) {
        if($die != 4) {
            $sum += $die;
        }
    }

    return $sum;
}

function print_roll($dice) {
    foreach($dice as $key => $value) {
        echo "Die Number " . $key . "\n";
        print_die($value);
    }
}

function print_die($number) {
    switch($number) {
        case 1:
            printOne();
            break;
        case 2:
            printTwo();
            break;
        case 3:
            printThree();
            break;
        case 4:
            printFour();
            break;
        case 5:
            printFive();
            break;
        case 6:
            printSix();
            break;
    }
}

function printOne() {
    echo " _______\n";
    echo "|       |\n";
    echo "|   .   |\n";
    echo "|       |\n";
    echo "|_______|\n";
}

function printTwo() {
    echo " _______\n";
    echo "|   .   |\n";
    echo "|       |\n";
    echo "|   .   |\n";
    echo "|_______|\n";
}

function printThree() {
    echo " _______\n";
    echo "|   .   |\n";
    echo "|   .   |\n";
    echo "|   .   |\n";
    echo "|_______|\n";
}

function printFour() {
    echo " _______\n";
    echo "| .   . |\n";
    echo "|       |\n";
    echo "| .   . |\n";
    echo "|_______|\n";
}

function printFive() {
    echo " _______\n";
    echo "| .   . |\n";
    echo "|   .   |\n";
    echo "| .   . |\n";
    echo "|_______|\n";
}

function printSix() {
    echo " _______\n";
    echo "| .   . |\n";
    echo "| .   . |\n";
    echo "| .   . |\n";
    echo "|_______|\n";
}

?>
