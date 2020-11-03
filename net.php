<?php

class WebClient {

    //public const DEFAULT_URL = 'https://cssrvlab01.utep.edu/Classes/cs3360/aamartinez24';
    public const DEFAULT_URL = 'http://www.cs.utep.edu/cheon/cs3360/project/c4';
    private const INFO = '/info/';
    private const NEW = '/new/';
    private const PLAY = '/play/';

    private String $url;
    private ResponseParser $responseParser;

    public function __construct(String $url) {
        $this->url = $url;
        $this->responseParser = new ResponseParser();
    }

    // Retrieves info of game from server
    public function getInfo() : Result {
        $fileContent = file_get_contents($this->url.self::INFO);
        if($fileContent) {
            return $this->responseParser->parseInfo($fileContent);
        }
        return new Result(null, 'Unable to connect with the server.');
    }

    // Creates a new game by passing the strategy of the game to the server
    public function createGame($strategy) : Result {
        $fileContent = file_get_contents($this->url.self::NEW.'?strategy='.$strategy);
        if($fileContent) {
            return $this->responseParser->parseNew($fileContent);
        }
        return new Result(null, 'Unable to connect with the server.');
    }

    // Starts playing a game with the specified pid number of the game and the move of the user
    public function playGame($pid, $slot) : Result {
        $response = file_get_contents($this->url.self::PLAY.'?pid='.$pid.'&move='.$slot);
        if($response) {
            return $this->responseParser->parsePlay($response);
        }
        return new Result(null, 'Unable to connect with the server.');
    }
}

class ResponseParser {

    /* Example of info format:
     * {"width": 7, "height": 6, "strategies": ["Smart","Random"]}
     */

    // Decode json and organizes content information and sets it to a Info class
    public function parseInfo(String $jsonString) : Result {
        $jsonContent = json_decode($jsonString, true);
        if($jsonContent) {
            try {
                $strategies = $jsonContent['strategies']; // List of strategies supported by the server
                if(!is_array($strategies)) {
                    throw Exception();
                }
                $width = $jsonContent['width'];
                $height = $jsonContent['height'];
                $info = new Info($width, $height, $strategies);
                return new Result($info);
            } catch (Exception $e) {
            }
        }
        return new Result(null, 'Information in wrong format');
    }

    /* Example of new format:
     * {"response": true, "pid": "57cdc4815e1e5"}
     * {"response": false, "reason": "Strategy not specified"}
     * {"response": false, "reason": "Unknown strategy"}
     */

    // Decode json and organizes content information and returns pid of the new game
    public function parseNew(String $jsonString) : Result {
        $jsonContent = json_decode($jsonString, true);
        if($jsonContent) {
            try {
                $response = $jsonContent['response'];
                if($response) {     // Response is accepted by server therefore server generates pid
                    $pid = $jsonContent['pid'];
                    return new Result($pid);
                }
                $reason = $jsonContent['reason'];   // Prints the reason why input was not accepted
                return new Result(null, 'Server: '.$reason);
            } catch (Exception $e) {
            }
        }
        return new Result(null, 'Information in wrong format');
    }

    /* Example of play format:
     * {"response": true
     *      "ack_move": {
     *          "slot": 3,
     *          "isWin": false,   // winning move?
     *          "isDraw": false,  // draw?
     *          "row": []},       // winning row if isWin is true
     *      "move": {
     *          "slot": 4,
     *          "isWin": false,
     *          "isDraw": false,
     *          "row": []}}
     *
     * {"response": false, "reason": "Pid not specified"}
     *
     * {"response": false, "reason": "Move not specified"}
     * {"response": false, "reason": "Unknown pid"}
     *
     * {"response": false, "reason": "Invalid slot, 10"}
     */

    // Decode json and organizes content information and Play class that holds all information
    public function parsePlay(String $jsonString) : Result {
        $arr = json_decode($jsonString, true);
        if($arr) {
            try {
                $response = $arr['response'];
                if($response) {         // Response is accepted by server therefore server generates move information
                    $user = $this->parseMove($arr['ack_move']);
                    $opponent = array_key_exists('move', $arr) ? $this->parseMove($arr['move']) : null;
                    $play = new Play($user, $opponent);
                    return new Result($play);
                }
                $reason = $arr['reason'];
                return new Result(null, 'Server: '.$reason);
            } catch (Exception $e) {
            }
        }
    }

    // Saves all move information of a player to a class
    public function parseMove(array $arr) : Move {
        $row = $arr['row'];
        if(!is_array($row)) {
            throw Exception();
        }
        $slot = $arr['slot'];
        $isWin = $arr['isWin'];
        $isDraw = $arr['isDraw'];
        return new Move($slot, $isWin, $isDraw, $row);
    }
}

// Class to hold different objects or string errors (result wrapper)
class Result {

    // Dynamic objects since different types of objects will be saved
    public $value;
    public $error;

    public function __construct($value, $error = null) {
        $this->value = $value;
        $this->error = $error;
    }

    public function isValue() : bool {
        return $this->value != null;
    }

    public function isError() : bool {
        return $this->error != null;
    }
}

// Class to hold all info information of a server
class Info {

    private int $width;
    private int $height;
    private array $strategies;

    public function __construct(int $width, int $height, array $strategies) {
        $this->width = $width;
        $this->height = $height;
        $this->strategies = $strategies;
    }

    public function getWidth() : int{
        return $this->width;
    }

    public function getHeight() : int {
        return $this->height;
    }

    public function getStrategies() : array {
        return $this->strategies;
    }
}

// Class to hold move information of both the user and the server
class Play {

    public Move $player;
    public $server;

    public function __construct($player, $server) {
        $this->player = $player;
        $this->server = $server;
    }
}

// Class to hold all information of a move
class Move {

    public int $slot;
    public bool $isWin;
    public bool $isDraw;
    public array $winRow;

    public function __construct(int $slot, bool $isWin, bool $isDraw, array $winRow) {
        $this->slot = $slot;
        $this->isWin = $isWin;
        $this->isDraw = $isDraw;
        $this->winRow = $winRow;
    }
}