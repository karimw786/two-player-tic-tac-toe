<?php
session_start();
require_once("includes/vars.php");

// If user has registered, take them to the game board
if (isset($_POST["player_name"])) {
    $_SESSION["player_name"] = $_POST["player_name"];
    header("location: play.php");
}

require_once("includes/header.php");
?>

    <div class="row">
        <div class="col">
            <h1 class="text-center">Player Registration</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 offset-md-4">
            <span class="alert-danger" id="socket_status">Server status: Offline</span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 offset-md-4">
            <form action="register.php" method="post">
                <div class="form-group">
                    <label>Player name</label>
                    <input class="form-control" type="text" name="player_name" maxlength="30" required disabled />
                </div>
                <div class="form-group text-center">
                    <input class="btn btn-primary btn-lg" type="submit" value="Play Tic-Tac-Toe!" disabled />
                </div>
            </form>
        </div>        
    </div>
        
<?php 
$page = "register.php";
require_once("includes/footer.php"); 
?>