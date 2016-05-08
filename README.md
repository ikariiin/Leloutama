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
$ php cli/run.php --router examples/router.example.php
```

And it would hit you with something like:

```
Server started successfully.
Listening on ip: 127.0.0.1 at port: 9000
```

To test the server, fire up a browser, and send a blazing request to `http://127.0.0.1:9000`, and if you are lucky enough you'd get to
see something like this:

![Leloutama Screenshot](http://i.imgur.com/IpFUJ6k.png)

## Router Structure:

For creating a router instance, firstly, you need to extend an abstract class for sending an response to a request.

You have to override the following methods:

* setBody
* setMime
* setStatus
* setFileName

And have to declare a method which would be called by the server when an request comes... In the example router, that method is named
`makeUp`...

And the final Response class might be something like this:

```
class Response extends \Leloutama\lib\Core\Utility\AbstractResponse {
    
    // Overloading abstract method
    public function setBody(string $content): self {
        $this->body = $content;
        return $this;
    }

    // Overloading abstract method
    public function setFileName(string $fileName): self {
        $this->fileName = $fileName;
        return $this;
    }

    // Overloading abstract method
    public function setMime(string $mime): self {
        $this->mime = $mime;
        return $this;
    }

    // Overloading abstract method 
    public function setStatus(int $code): self {
        $this->status = $code;
        return $this;
    }

    // Method to call when the server recieves a request
    public function makeUp(string $fileName) {
        $this->setFileName($fileName);

        $this->setMime(mime_content_type($fileName));

        $this->setStatus(200);

        $body = file_get_contents($this->getConfig("docRoot") . $fileName);

        $this->setBody($body);
    }
}
```

And then simply, define response for a request, and bind the response, like:

```
// Instanciate the router
$router = new \Leloutama\lib\Core\Router\Router();

// Create a response, for maybe, the index page?
$indexResponse = new Response();
// Set the method
$indexResponse->setOnReadyMethod("makeUp");
// Set the argument
$indexResponse->setOnReadyMethodArgs("index.html");

// And lastly bind the exposure route and the response
$router->bind("/", $indexResponse);

// And make sure to return the router, or the server will throw a brick at your face
return $router;
```

## How to use the `cli/run.php`?

You need to specify three options, or you can omit `--host` and `--port`, which would be set to default if nothing is specified.

The default for `host` is `127.0.0.1` and `port` is `9000`.

But make sure to specify `--router`, which would require the name of the file you are using as the router.
Like:

```
$ php cli/run.php --rotuer example/router.example.php
```

And the host and the port can also be defined, like:

```
$ php cli/run.php --host 127.0.0.2 --port 7005 --router example/router.example.php
```

Which would present with an output similar to this:

```
Server started successfully.
Listening on ip: {YOUR_CHOSEN_HOST} at port: {YOUR_CHOSEN_PORT}
```

## TODO
* Implement doc-blocks (v1.2)
* Implement an api for creating extensions (v1.1)
* (...And more stuffs...)

## Why should you use it?
* You love PHP, and wanna swag 'bout it.
* You want to try something new.
* Reply something to those guys, who tell that PHP is good for nothing.
* Is open source ...

## Drawbacks

* Currently, doesn't have much features.
* The codebase is un-documented.
* Many HTTP header's are yet to be utilised.
* No HTTP caching as of now.
* No content encryption.

## PR-s

Feel free to PR something...
I really look forward for your contribution.

## Issues

Currently, none, but is sure to be coming up, with further use.