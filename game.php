<?php

// Game constants
$sides_of_die = 6;
$number_of_players = 4;
$number_of_rounds = $number_of_players;
$dice_per_round = 5;

// Have players enter info
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

// Play the game
for($round = 1; $round <= $number_of_rounds; $round++) {
    foreach($players as $player => $score) {
        echo PHP_EOL . "########################################" . PHP_EOL;
        echo $player . "'s turn!!!" . PHP_EOL;

        $number_of_saved_dice = 0;

        // Continue until the player rolls all dice, this represents a round
        while($number_of_saved_dice < $dice_per_round) {
            $saved_dice_this_roll = array();

            echo PHP_EOL . "Roll!!!" . PHP_EOL;
            $dice_to_roll = $dice_per_round - $number_of_saved_dice;
            $dice = roll_dice($dice_to_roll, $sides_of_die);
            print_roll($dice);

            echo "Please select one die to save: " . PHP_EOL;
            $status_update = play($player, $dice, $saved_dice_this_roll, $number_of_saved_dice);
            $roll_again = $status_update['roll_again'];
            $saved_dice_this_roll = $status_update['saved_dice_this_roll'];
            $number_of_saved_dice = $status_update['number_of_saved_dice'];

            // Have the player either keep selecting dice, or roll again, represents a single roll
            while(!$roll_again && $number_of_saved_dice < $dice_per_round) {
                echo "Type ROLL, to roll remaining dice, or select another die to save:" . PHP_EOL;
                $status_update = play($player, $dice, $saved_dice_this_roll, $number_of_saved_dice);
                $roll_again = $status_update['roll_again'];
                $saved_dice_this_roll = $status_update['saved_dice_this_roll'];
                $number_of_saved_dice = $status_update['number_of_saved_dice'];
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

// Sort the players by score so we can show the final tally in order
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

function play($player_name, $dice, $already_saved_dice_this_roll, $total_saved_dice) {
    switch($player_name) {
        case 'RandBot':
            return random_bot($dice, $already_saved_dice_this_roll, $total_saved_dice);
            break;
        case 'AllBot':
            return all_bot($dice, $already_saved_dice_this_roll, $total_saved_dice);
            break;
        case 'CheatBot':
            return cheat_bot($dice, $already_saved_dice_this_roll, $total_saved_dice);
            break;
        case 'SmartBot':
            return smart_bot($dice, $already_saved_dice_this_roll, $total_saved_dice);
            break;
        default:
            return read_and_record_die_selection($dice,
                $already_saved_dice_this_roll, $total_saved_dice);
    }
}

// Prompt for user command and return updated status info
function read_and_record_die_selection($dice, $already_saved_dice_this_roll, $total_saved_dice) {
    $die_selected = read_stdin();

    // Keep prompting until the user provides a valid entry
    while(!is_valid_die($die_selected, count($dice), $already_saved_dice_this_roll)
            && (empty($already_saved_dice_this_roll) || !is_roll_string($die_selected))) {
        echo "That is not a valid selection, please try another: " . PHP_EOL;
        $die_selected = read_stdin();
    }

    if(is_roll_string($die_selected)) {
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice);
    } else {
        $already_saved_dice_this_roll[$die_selected] = $dice[$die_selected];
        return array('roll_again' => False,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice + 1);
    }
}

// Bot that randomly selects a single die per roll
function random_bot($dice, $already_saved_dice_this_roll, $total_saved_dice) {
    if(!empty($already_saved_dice_this_roll)) {
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice);
    } else {
        $die_selected =  rand(0, count($dice) - 1);
        echo 'Selected Die Number ' . $die_selected . PHP_EOL;
        $already_saved_dice_this_roll[$die_selected] = $dice[$die_selected];
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice + 1);
    }
}

// Bot that selects all dice
function all_bot($dice, $already_saved_dice_this_roll, $total_saved_dice) {
    if(!empty($already_saved_dice_this_roll)) {
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice);
    } else {
        for($i = 0; $i < count($dice); $i++) {
            echo 'Selected Die Number ' . $i . PHP_EOL;
            $already_saved_dice_this_roll[$i] = $dice[$i];
        }
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice + count($already_saved_dice_this_roll));
    }
}

// Bot that only selects 4s. Can roll all die again if there are no 4s
function cheat_bot($dice, $already_saved_dice_this_roll, $total_saved_dice) {
    if(!empty($already_saved_dice_this_roll)) {
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice);
    } else {
        for($i = 0; $i < count($dice); $i++) {
            if($dice[$i] == 4) {
                echo 'Selected Die Number ' . $i . PHP_EOL;
                $already_saved_dice_this_roll[$i] = $dice[$i];
            }
        }
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice + count($already_saved_dice_this_roll));
    }
}

// Bot that either selects the lowest die, or all die with value of 1, 2, or 4
// which is less than the expected value of any single roll is 2.833
function smart_bot($dice, $already_saved_dice_this_roll, $total_saved_dice) {
    if(!empty($already_saved_dice_this_roll)) {
        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice);
    } else {
        $lowest_die = array('id' => -1, 'value' => PHP_INT_MAX);
        for($i = 0; $i < count($dice); $i++) {
            if($dice[$i] < $lowest_die['value']) {
                $lowest_die = array('id' => $i, 'value' => $dice[$i]);
            }

            if($dice[$i] == 4 || $dice[$i] == 1 || $dice[$i] == 2) {
                echo 'Selected Die Number ' . $i . PHP_EOL;
                $already_saved_dice_this_roll[$i] = $dice[$i];
            }
        }

        if(empty($already_saved_dice_this_roll)) {
            echo 'Selected Die Number ' . $lowest_die['id'] . PHP_EOL;
            $already_saved_dice_this_roll[$lowest_die['id']] = $dice[$lowest_die['id']];
        }

        return array('roll_again' => True,
                'saved_dice_this_roll' => $already_saved_dice_this_roll,
                'number_of_saved_dice' => $total_saved_dice + count($already_saved_dice_this_roll));
    }
}

// Randomize the order of  an array while keeping the keys
function shuffle_assoc(&$array) {
    $keys = array_keys($array);

    shuffle($keys);

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
}

// Valid die's identifier must exist and have not been already selected in this roll
function is_valid_die($input, $number_of_dice_rolled, $already_saved_dice_this_roll) {
    return ctype_digit($input) 
            && 0 <= $input && $input < $number_of_dice_rolled 
            && !array_key_exists($input, $already_saved_dice_this_roll);
}

function is_roll_string($input) {
    return strcasecmp($input, 'roll') == 0;
}

function roll_dice($numberOfDice, $sides_of_die) {
    $dice = array();
    for($i = 0; $i < $numberOfDice; $i++) {
        array_push($dice, rand(1, $sides_of_die));
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
