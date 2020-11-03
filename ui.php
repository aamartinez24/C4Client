<?php

class UI {

    private $response;
    private Board $board;

    public function __construct() {
        // Opens stream for stdin
        $this->response = fopen('php://stdin', 'r');
    }

    // Sets board to be printed in the console
    public function setBoard(Board $board) {
        $this->board = $board;
    }

    // Prints any message in the console
    public function showMessage(String $message) {
        print($message."\n");
    }

    // Asks user for the url that contains the connect four server; otherwise will use the default server
    public function promptServerURL(String $defaultURL) : String {
        while(true) {
            print('Enter the server URL [default: '.$defaultURL.']');
            $url = trim(fgets($this->response));
            if(empty($url)) {       // Use default url for default server
                return $defaultURL;
            }
            // Check if user input is a url
            elseif(!filter_var($url, FILTER_VALIDATE_URL) == false) {
                return $url;
            }
            $this->showMessage('Invalid URL: '.$url);
        }
    }

    // Asks user for strategy (provided by the server info) they will like the opponent to play with
    public function promptStrategy(array $strategies) : String {
        while(true) {
            print('Select the server strategy: ');
            for($i = 0; $i < count($strategies); $i++) {
                print(($i + 1).'. '.$strategies[$i].' ');   // Prints all strategies from server
            }
            print('[default: '.$strategies[0].']'); // Default strategy will be first strategy listed by the server
            $line = trim(fgets($this->response));
            if(empty($line)) {      // Use default strategy
                return $strategies[0];
            }
            $userInput = intval($line);
            if($userInput >= 1 && $userInput <= count($strategies)) { // Check if input is in the range
                return $strategies[$userInput - 1];
            }
            $this->showMessage('Invalid selection: '.$line);
        }
    }

    // Asks user to choose a slot to put a disc in the board
    public function promptMove() : int {
        while(true) {
            print('Select a slot [1 - '.$this->board->getWidth().']');  // Print range of slots of board
            $line = trim(fgets($this->response));
            $userInput = intval($line);
            // Check if user input is in the range of the board and is not full
            if($userInput > 0 && $userInput <= $this->board->getWidth() && $this->board->isSlotFree($userInput - 1)) {
                return $userInput - 1;
            }
            $this->showMessage('Invalid selection: '.$line);
        }
    }

    // Prints the board and its content in the console
    public function printBoard(int $lastMove = -1) {
        $allSlots = $this->board->getBoardSlots();
        for($i = 0; $i < count($allSlots); $i++) {      // Prints all the symbols in the board
            for($j = 0; $j < count($allSlots[$i]); $j++) {
                print($allSlots[$i][$j]->getSymbol().' ');
            }
            print("\n");
        }
        for($i = 1; $i <= $this->board->getWidth(); $i++) { // Prints the numbers associated to the columns of the board
            print($i.' ');
        }
        print("\n");
        if($lastMove >= 0) {        // Prints the symbol '*' under the column that the last move was made
            $marker = array_fill(0, $this->board->getWidth(), ' ');
            $marker[$lastMove] = '*';
            $marker = implode(' ', $marker);
            $this->showMessage($marker);
        }
    }
}