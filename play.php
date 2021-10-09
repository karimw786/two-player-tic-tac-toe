<?php
session_start();
require_once("includes/helper_functions.php");
require_once("includes/vars.php");

// If user is NOT already registered, force them to register
if (!isset($_SESSION["player_name"])) {
    header("location: register.php");
}
else {
    $ip = get_ip();
}

// Initialize win/loss/draw session variables, if not already set
if (!isset($_SESSION["player_wins"])) {
    $_SESSION["player_wins"] = 0;
}
if (!isset($_SESSION["player_losses"])) {
    $_SESSION["player_losses"] = 0;
}
if (!isset($_SESSION["player_draws"])) {
    $_SESSION["player_draws"] = 0;
}

// If win/loss/draw post variables are set, increment associated session variables
if (isset($_POST["player_wins"])) {
    $_SESSION["player_wins"] += $_POST["player_wins"];
}
elseif (isset($_POST["player_losses"])) {
    $_SESSION["player_losses"] += $_POST["player_losses"];
}
elseif (isset($_POST["player_draws"])) {
    $_SESSION["player_draws"] += $_POST["player_draws"];
}

require_once("includes/header.php");
?> 

    <div class="row">
        <div class="col">
            <h1 class="text-center">Tic-Tac-Toe</h1>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div id="player_moves" class="d-none">
                <?php echo "<strong><span id='p1'>" . $_SESSION["player_name"] . "</span></strong>: <span id='m1'>X</span><br />"; ?>
                <?php echo "<strong><span id='p2'>Player 2</span></strong>: <span id='m2'>O</span><br />"; ?>
            </div>
            <div id="game_status">
                Waiting for second player
            </div>
        </div>
        <div class="col">
            <div id="wins_losses" class="d-none">
                <strong>Wins</strong>: <span id="wins"><?php echo $_SESSION["player_wins"]; ?></span> | 
                <strong>Losses</strong>: <span id="losses"><?php echo $_SESSION["player_losses"]; ?></span> | 
                <strong>Draws</strong>: <span id="draws"><?php echo $_SESSION["player_draws"]; ?></span>
            </div>
        </div>
        <div class="col">
            <div id="menu" class="d-none">
                <a class="btn btn-primary" href="play.php" role="button" id="btn_play_again">Play Again</a> <a class="btn btn-danger" role="button" id="btn_quit">Quit</a>
            </div>
        </div>
    </div>

    <?php draw_board(3, 3); ?>

    <div class="modal" id="winner_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Result</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modal_message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php
$page = "play.php"; 
require_once("includes/footer.php"); 
?>