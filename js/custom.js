// Game administration variables
var p;                                  // The local player
var players;                            // Both players in the game
var global_scores = [0,0,0,0,0,0,0,0]   // Scores
var moves = 0;                          // Counts number of moves
var grid_size = 3;                      // Default board will be 3x3
var turn_lock;                          // Turn lock (X or O)
var current_player;                     // Name of player who can make a move currently
var next_player;                        // Name of player who can make a move next
var socket;                             // Socket used for communication

// Sends message to socket
function send_message(message_type, msg) {
    var msg_contents = JSON.parse(msg);
    var json_msg;

    switch (message_type) {        
        case "usersyscheck":
            // Prepare JSON message
            json_msg = {
                message_type: message_type
            };
            break;

        case "userreg":
            // Prepare JSON message
            json_msg = {
                message_type: message_type, 
                player_name: msg_contents.player_name,
                player_ip: msg_contents.player_ip
            };
            break;

        case "usermove":
            // Prepare JSON message
            json_msg = {
                message_type: message_type,
                player_id: msg_contents.player_id, 
                player_name: msg_contents.player_name, 
                move_id: msg_contents.move_id, 
                move: msg_contents.move
            };
            break;

        case "userscores":
            // Prepare JSON message
            json_msg = {
                message_type: message_type, 
                scores: msg_contents.scores
            };
            break;
        
        case "userquit":
            // Prepare JSON message
            json_msg = {
                message_type: message_type,
                player_id: msg_contents.player_id, 
                player_name: msg_contents.player_name
            };
            break;

        case "userplay":
            json_msg = {
                message_type: message_type
            };
            break;
    }
    
    // Convert and send data to socket
    socket.send(JSON.stringify(json_msg));
}

// Places X or O based on the empty square selected by player
function make_move(move_id, m) {
    var rc = parse_move_id(move_id);
    var r = rc[0];                          // Row of the move to be made
    var c = rc[1];                          // Col of the move to be made
    var selector = "#r".concat(r, "c", c);  // CSS selector of the position

    // If square is empty...
    if ($(selector).is(":empty")) {
        // ...make move
        $(selector).html(m);
        moves += 1;
    }
}

// Update the scores array
function update_scores(move, move_id) {
    var point = (move == "X") ? 1:-1;
    var scores = global_scores;
    var rc = parse_move_id(move_id);
    var r = rc[0];
    var c = rc[1];

    scores[r - 1] += point;
    scores[grid_size + (c - 1)] += point;
    if (r == c) scores[2 * grid_size] += point;
    if (grid_size - 1 - (c - 1) == (r - 1)) scores[2 * grid_size + 1] += point;
    send_message('userscores', '{ "scores":"' + scores + '" }');
}

// Ruturns whether or not there is a winner
function check_winner() {
    var scores = global_scores;
    var winning_move;

    for (var i = 0; i < scores.length; i++) {
        if (scores[i] == grid_size) {
            return 1; // X won
        }
        else if (scores[i] == -(grid_size)) {
            return 2; // O won
        }
    }

    if (moves == (grid_size * grid_size)) {
        return 3; // Draw
    }

    return 0; // No winner yet
}

// Displays winner and updates win/loss/draw totals
function display_winner(win_code) {
    var modal_message;

    switch(win_code) {
        case 1: // X won

            if (p.player_move == "X") {
                modal_message = "Congrats, you win!";
                $.post("play.php", {player_wins: 1});
                $("#wins").html(parseInt($("#wins").text()) + 1);
            }
            else {
                modal_message = "Sorry, you lost. Better luck next time!";
                $.post("play.php", {player_losses: 1});
                $("#losses").html(parseInt($("#losses").text()) + 1);
            }

            break;
        case 2:

            if (p.player_move == "O") {
                modal_message = "Congrats, you win!";
                $.post("play.php", {player_wins: 1});
                $("#wins").html(parseInt($("#wins").text()) + 1);
            }
            else {
                modal_message = "Sorry, you lost. Better luck next time!";
                $.post("play.php", {player_losses: 1});
                $("#losses").html(parseInt($("#losses").text()) + 1);
            }

            break;
        case 3:
            modal_message = "It's a draw!";
            $.post("play.php", {player_draws: 1});
            $("#draws").html(parseInt($("#draws").text()) + 1);
            break;
    }

    $("#modal_message").html(modal_message);
    $("#winner_modal").modal("show");
}

// Parse move ID into row and column of move made
function parse_move_id(move_id) {
    var r = move_id.substring(1, move_id.indexOf("c"));   // Row of the move to be made
    var c = move_id.substring(move_id.indexOf("c") + 1);  // Col of the move to be made
    return [r,c];
}

// Reset game
function reset() {
    $("#p1").html(players[0][1]);
    $("#m1").html(players[0][2]);         
    $("#p2").html(players[1][1]);
    $("#m2").html(players[1][2]);
    $("#game_status").html(players[0][1] + "'s move.");
    $(".board .col").html("");
    current_player = players[0][1];
    next_player = players[1][1];
    turn_lock = "X";
    global_scores = [0,0,0,0,0,0,0,0];
    moves = 0;
}