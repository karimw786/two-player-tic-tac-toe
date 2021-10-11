# two-player-tic-tac-toe
Two-player tic-tac-toe game written in PHP, HTML, CSS (Bootstrap), and JavaScript (JQuery). Uses web sockets for real-time communication.

# Deployment Instructions

* Download the code (and unzip it), or git clone it:

``` git clone https://github.com/karimw786/two-player-tic-tac-toe.git ```

* Using a text editor, change the value of $WEBSERVER_ADDRESS in two-player-tic-tac-toe/includes/vars.php to be the IP address of the machine on which the code will be deployed (e.g. "192.168.0.5").

* Change into the directory:

``` cd two-player-tic-tac-toe ```

* If deploying on a Docker container, run the following commands:

``` docker build -t two-player-tic-tac-toe . ```
``` docker run -d -p 80:80 -p 8080:8080 two-player-tic-tac-toe ```

Otherwise, simply put the code in your web server's document root, change into that directory from the terminal, and run the websockets server manually:

``` php -q websockets_server.php & ```

* Players can play the game by opening a web browser window and navigating to the value previously entered for $WEBSERVER_ADDRESS (e.g. http://192.168.0.5).