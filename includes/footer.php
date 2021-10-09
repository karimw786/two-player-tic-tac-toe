    </div> <!-- end div container -->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/custom.js"></script>
    <script type="text/javascript">
        var host = "ws://<?php echo $WEBSERVER_ADDRESS; ?>:<?php echo $WEBSOCKET_PORT; ?>/websockets_server.php";
        
        if (typeof(socket) === "undefined") {
            socket = new WebSocket(host);
        }

        <?php if ($page == "register.php") { ?>
        socket.onopen = function(e) {
            send_message('usersyscheck', '{}');
        };
        <?php } else { ?>
        socket.onopen = function(e) {
            send_message('userreg', '{ "player_name":"<?php echo $_SESSION["player_name"]; ?>", "player_ip":"<?php echo $ip; ?>" }');
        };
        <?php } ?>

        socket.onmessage = function(e) {
            var response = JSON.parse(e.data);            
            var message_type = response.message_type;
            var message = response.msg;

            switch(message_type) {
                case "syssyscheck":
                    // Update socket status
                    if (message == "Online") {
                        $("#socket_status").hide();
                        $("input").prop("disabled", false);
                    }
                    break;

                case "sysreg":
                    // Initialize local player's id/name/move
                    if (typeof(p) === "undefined") {
                        p = {
                            player_id:message["player_id"],
                            player_name:message["player_name"],
                            player_move:message["player_move"],
                            player_point:message["player_point"]
                        };
                    }
                    break;

                case "sysready":
                    // Update spans under #player_moves (hidden by default)
                    $("#p1").html(message[0][1]);
                    $("#m1").html(message[0][2]);         
                    $("#p2").html(message[1][1]);
                    $("#m2").html(message[1][2]);

                    // Initialize current/next player names
                    current_player = message[0][1];
                    next_player = message[1][1];

                    // Give X (Player 1) the turn_lock
                    turn_lock = "X";

                    // Unhide #player_moves, #win_losses, #menu
                    $("#player_moves").removeClass("d-none");
                    $("#wins_losses").removeClass("d-none");
                    $("#menu").removeClass("d-none");

                    // Initialize #game_status
                    $("#game_status").html(message[0][1] + "'s move.");
                    break;

                case "sysmove":
                    // Make move
                    make_move(message["move_id"], message["move"]);
                    update_scores(message["move"], message["move_id"]);
                    var winner = check_winner();

                    if (winner) {
                        setTimeout(function(){display_winner(winner);}, 500);                
                    }
                    else {
                        // Swap current/next player names
                        var temp_player = current_player;
                        current_player = next_player;
                        next_player = temp_player;

                        // Release turn_lock
                        turn_lock = (turn_lock == "X") ? "O":"X";
                                    
                        // Set #game_status
                        $("#game_status").html(current_player + "'s move.");
                    }                        
                    break;

                case "sysscores":
                    global_scores = message.split(",").map(function(item) {
                        return parseInt(item);
                    });
                    break;
                
                case "sysquit":
                    if (p.player_id != message["player_id"]) {
                        modal_message = message["player_name"] + " has quit. You will be redirected to the Player Registration page in 5 seconds.";
                        $("#modal_message").html(modal_message);
                        $("#winner_modal").modal("show");
                        setTimeout(function(){location.href = "logout.php";}, 5000); 
                    }
                    break;
            }
        };

        $(document).ready(function() {
            $(".board .col").click(function(event) {
                if(p.player_move == turn_lock) {
                    send_message('usermove', '{ "player_id":"' + p.player_id + '", "player_name":"' + p.player_name + '", "move_id":"' + this.id + '", "move":"' + p.player_move + '" }');
                }
            });
            $("#btn_quit").click(function(event) {
                if (confirm("Are you sure you want to quit?")) {
                    try {
                        send_message('userquit', '{ "player_id":"' + p.player_id + '", "player_name":"' + p.player_name + '" }');
                    }
                    finally {
                        location.href = "logout.php";
                    }
                }
            });
        });
    </script>
</body>
</html>