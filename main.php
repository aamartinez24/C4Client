<?php

require_once 'model.php';
require_once 'net.php';
require_once 'ui.php';

(new Controller())->startGame();

class Controller {

    // Model Classes
    private Board $board;
    private Player $player;
    private Player $opponent;
    // View Class
    private UI $ui;
    // Net Communication class (gets information from server)
    private WebClient $webClient;

    public function __construct() {
        // User disc will be represented by 'O' while servers moves will be represented by 'X'
        $this->player = new Player('O');
        $this->opponent = new Player('X');
        $this->ui = new UI();
    }

    public function startGame() {
        // Ask user for url of connect four server. Passed a default url server
        $url = $this->ui->promptServerURL(WebClient::DEFAULT_URL);
        $this->ui->showMessage('Obtaining server information .....');
        $this->webClient = new WebClient($url);
        // Get information of game (e.g., length of board and strategies)
        $result = $this->webClient->getInfo();
        if($result->isError()) {
            $this->ui->showMessage($result->error);
            return;
        }
        $info = $result->value;
        // Ask user for a strategy supported by the server
        $strategy = $this->ui->promptStrategy($info->getStrategies());
        $this->ui->showMessage('Creating a new game .....');
        // Creates a new game in the server with the specified strategy
        $result = $this->webClient->createGame($strategy);
        if($result->isError()) {
            $this->ui->showMessage($result->error);
            return;
        }
        $pid = $result->value;
        // Creates board class with the gathered info
        $this->board = new Board($info->getWidth(), $info->getHeight());
        $this->ui->setBoard($this->board);
        // Start playing a game
        $this->playGame($pid);
    }

    // Method to play connect four
    public function playGame(String $pid) {
        $lastMove = -1;
        while(true) {
            $this->ui->printBoard($lastMove);
            // Ask user for slot
            $slot = $this->ui->promptMove();
            $result = $this->webClient->playGame($pid, $slot);
            if($result->isError()) {
                $this->ui->showMessage($result->error);
                break;
            }
            $move = $result->value;
            $this->board->dropDisc($slot, $this->player);
            if($move->player->isWin) {      // User win game
                $this->board->setWinningSlots($move->player->winRow);
                $this->ui->printBoard();
                $this->ui->showMessage('You Win! :)');
                break;
            }
            $lastMove = $move->server->slot;
            $this->board->dropDisc($lastMove, $this->opponent);
            if($move->server->isWin) {      // User lost game (opponent won)
                $this->board->setWinningSlots($move->server->winRow);
                $this->ui->printBoard();
                $this->ui->showMessage('You Lost! :(');
                break;
            }
            if($move->server->isDraw) {     // Nobody won
                $this->ui->printBoard();
                $this->ui->showMessage('Its a Draw! :|');
                break;
            }
        }
    }
}