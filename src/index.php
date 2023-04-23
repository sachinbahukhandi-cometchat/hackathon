<?php
require './vendor/autoload.php';

$json = file_get_contents('php://input');
$action = json_decode($json, true);

if (!empty($action['data'])) {
    $gameData = $action['data']['data']['customData']['game_data'];

    $points = $gameData['board'];

    prepareJSON($points);

    checkWin($points, $gameData);
    exit;

} else {
    echo prepareJSON(['success' => true, 'message' => 'Invalid move']);
}

function prepareJSON($data)
{
    echo json_encode($data);
    file_put_contents('webhook_data',json_encode($data, JSON_PRETTY_PRINT), FILE_APPEND | LOCK_EX);
}

function flattenArray($array)
{
    return array_merge(...$array);
}

// Convert 3x3 array  from custom data into 1x9
// This array with 9 items is an input to determine the Game Over.
$board = ["x", "o", "x", "o", "x", "o", "x", "", ""];

function hasWon($board, $symbol)
{
    $magicSquare = [4, 9, 2, 3, 5, 7, 8, 1, 6];
    for ($i = 0; $i < 9; $i++) {
        for ($j = 0; $j < 9; $j++) {
            for ($k = 0; $k < 9; $k++) {
                if ($i != $j && $i != $k && $j != $k) {
                    if (!empty($board[$i]) && !empty($board[$j]) && !empty($board[$k])) {
                        if (strtolower($board[$i]) == strtolower($symbol) && strtolower($board[$j]) == strtolower($symbol) && strtolower($board[$k]) == strtolower($symbol)) {
                            if ($magicSquare[$i] + $magicSquare[$j] + $magicSquare[$k] == 15) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
    }

    return false;
}
function checkWin($board, $data)
{
    $success = true;
    $message = "No winner yet";
    $isEnded= $isDraw= false;
    $winner= null;

    if (hasWon($board, 'x')) {
        $message = "x win!";
        $isEnded= true;
        $winner= 'x';

    } else if (hasWon($board, 'o')) {
        $message = "o win!";
        $isEnded = true;
        $winner = 'o';


    } else if (count(array_filter($board)) == 9) {
        $message = "draw";
        $isDraw= true;
        $isEnded= true;
    } else {
        $message = "No winner yet...";
    }


    if(!is_null($winner)){
         $winner= $data[$winner];
    }



    return prepareJSON([
        'isDraw'=> $isDraw,
        'isEnded'=> $isEnded,
        'winner'=> $winner

    ]);

}
