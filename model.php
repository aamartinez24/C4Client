<?php

class Player {

    // Each player will be represented by a symbol (i.e., 'X', 'O')
    private String $symbol;

    public function __construct(String $symbol) {
        $this->symbol = $symbol;
    }

    public function getSymbol() : String {
        return $this->symbol;
    }
}

class Board {

    private int $width;
    private int $height;
    // Multidimensional array that holds the players disc; represents a real life board
    private array $boardSlots;
    // Player class that holds symbols that represent a empty slot and the slots that win the game
    private Player $emptySlots;
    private Player $winnerSlots;

    public function __construct(int $width, int $height) {
        $this->width = $width;
        $this->height = $height;
        $this->emptySlots = new Player('.');
        $this->winnerSlots = new Player('W');
        $this->boardSlots = array();
        // Initializes the multidimensional array with Player that represents empty slots ('.')
        for($i = 0; $i < $height; $i++) {
            for($j = 0; $j < $width; $j++) {
                $this->boardSlots[$i][$j] = $this->emptySlots;
            }
        }
    }

    // Checks if the column that the user drops the disc is not full
    public function isSlotFree(int $slot) : bool {
        return $this->boardSlots[0][$slot] == $this->emptySlots;
    }

    // Places a disc (Player symbol) in the board
    public function dropDisc(int $slot, Player $player) {
        // Places disc in the board slot that is free
        for($i = $this->height - 1; $i > -1; $i--) {
            if($this->boardSlots[$i][$slot] == $this->emptySlots) {
                $this->boardSlots[$i][$slot] = $player;
                return;
            }
        }
    }

    // Gets the winning row from the server and changes winning slot symbols to that of a 'W'
    public function setWinningSlots(array $winRow) {
        for($i = 0; $i < count($winRow); $i = $i + 2) {
            $this->boardSlots[$winRow[$i + 1]][$winRow[$i]] = $this->winnerSlots;
        }
    }

    public function getWidth() : int {
        return $this->width;
    }

    public function getHeight() : int {
        return $this->height;
    }

    public function getBoardSlots() : array {
        return $this->boardSlots;
    }
}