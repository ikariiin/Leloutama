# Leloutama
A multi-threaded webserver, using sockets, written entirely in PHP.

### Requirements
* A *nix operating system
* PHP version >= 7, and a thread safe build
* The [pthreads](https://github.com/krakjoe/pthreads) extension for PHP.

### How to get up and running with it...

Clone the project from github, and `cd` into it by using:

```
$ git clone git://github.com/gourabNagDev/Leloutama && cd Leloutama
```

For using the sample application, run:

```
$ php examples/server.example.php
```

The file basically contains the following piece of code:

```
require "../src/lib/Core/Router/Router.php";
require "../src/lib/Core/Server/Server.php";

// Initialize the Router
$router = new Leloutama\lib\Core\Router\Router();

// Set a route
$router->setRoute("/", "\\o/ You successfully started using Leloutama.\n In case you are wondering why is this thing having such a wierd name,\n This name was derived in Room 11 by @Ekn.", "text/plain");

// Set another route
$router->setRoute("/welcome", "<h1>Welcome, Alien.</h1>", "text/html");

// Initialize the server
$server = new Leloutama\lib\Core\Server\Server($router);

// Start the server
$server->startServer();
```

After running the server, you would get something like this as the output, if everything went right:

```
Server started successfully.
Listening on ip: 127.0.0.1 at port: 2406
```

And after you make a request to any defined route, you would get something like this:

```
Started a new Client Thread with the uid: fe6d0dd9245fa1de9e71d22f2dd05dbb4937a382493e6417052fbccea1920f21
Request Recieved 
 	 Time: May 04 2016-14:04:31  
 	 Requested Resource: / 
 	 Method: GET 
Closed the Client Thread with the uid: fe6d0dd9245fa1de9e71d22f2dd05dbb4937a382493e6417052fbccea1920f21
Currently running number of processes: 0
```

That's it.
