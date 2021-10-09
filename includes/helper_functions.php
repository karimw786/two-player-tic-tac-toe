<?php
// Draws tic-tac-toe game board
function draw_board($rows = 3, $cols = 3) {
    $board = '<div class="board">';
    
    for($row = 1; $row <= $rows; $row++) {
        $board .= '<div class="row g-0">';

        for($col = 1; $col <= $cols; $col++) {
            $board .= '<div class="col" id="r' . $row . 'c' . $col . '"></div>';
        }

        $board .= "</div>";
    }

    $board .= "</div>";
    echo $board;
}

// Returns player's IP address
function get_ip() {
    if ( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
        // Check IP from internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } 
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
        // Check IP is passed from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } 
    else {
        // Get IP address from remote address
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}
?>