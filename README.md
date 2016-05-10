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

You have to override the `onClick` method. This method will be called when the server receives a request. And, that method _has to return `$this`_.

You can use the methods:

* setBody
* setMime
* setStatus
* setFileName

To make create the response.

### Method Details

* setBody:

Will need the content you would serve for that route, as the argument

* setMime:

Needs the mime type of the file you are serving as the argument.

* setStatus:

Is used to set the HTTP status code of the response you are sending. Needs the status as an argument.

* setFileName:

Set the file name from which the content has been taken. Accepts the file name as an argument. And if you are not using
any file at all, set the file name to an empty string.

*This would be used for caching HTTP responses, which is currently not implemented.*

---

You can also use the method `defaultSet` to set all those things, which needs an array with the keys, `body` (needs to contain the content to serve), `mime`(needs to contain MIME type of the content which is being served), `status`(needs to contain status of the response being sent), `fileName` (needs to contain the file name of the file from which the content is being served, if any, set to an empty string).

And the final Response class should be something like this:

```
class Response extends \Leloutama\lib\Core\Utility\AbstractResponse {
    public function onReady($fileName) {
        $this->setFileName($fileName);

        $this->setMime(mime_content_type($this->getConfig("docRoot") . $fileName));

        $this->setStatus(200);

        $body = file_get_contents($this->getConfig("docRoot") . $fileName);

        $this->setBody($body);
        
        return $this;
    }
}
```

And then simply, define response for a request, and bind the response, like:

```
// Instanciate the router
$router = new \Leloutama\lib\Core\Router\Router();

// Create a response, for maybe, the index page?
$indexResponse = new Response();

// Set the arg by which the onReady method must be called
$indexResponse->setOnReadyMethodArgs("index.html");

// And lastly, bind the exposure route and the response
$router->bind("/", $indexResponse);

// And make sure to return the router, or the server will throw a brick at your face
return $router;
```

## (NEW) Extensions

There are two types of extensions, namely:

* RouterExt (ClientExt)
* ServerExt

*Currently I have only implemented `RouterExt` or `ClientExt`.*

### RouterExt (ClientExt)

They are meant to be used within a response. An example, Ext has been bundled with the server, i.e. `FileCheckr` which, can be used to load files from local disk, and if it does not exist, instead of showing nothing to the user, it loads an `Application Error` Template.

The API is really simple.

There has to be an separate directory for all extensions, and must have an PHP Class named the Extension's name within that directory.

Any which the Ext needs to use, must be kept in that directory.

The Extension class needs to include and implement the interface `ClientExtension`.

The Extension would be constructed with the `Request` and, the `Config` for that extension only.

And the other things can be done independently by the extension.

### How to use the RouterExt's

For using any kind of extension in an response, you must call `initializeExtensionManager()` of the class.

After that, in any method, use something like this:

```
$this->extensionManager->load($extName);
```

Where `$extName` would be the name of the ext we want to load.

The above function call would return an instance of the ext, which can be used in further tasks.

### ServerExt

TBD

### How to use ServerExt?

TBD

## Bundled Ext Docs

TBD

## How to use the `cli/run.php`? or How to run the server?

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

## Configurations

The configurations for the server can be set in `/path/to/Leloutama/src/config/Core/config.json`.

The configurations for the extensions need to defined like:

```
"Extensions": {
  "ExtensionName": {
    [... options ...]
  }
}
```

## TODO
* Implement doc-blocks (v1.2)
* Implement ServerExt (v1.2)
* Implement HTTP Caching (v1.2)
* Document `FileCheckr` (v1.1.1)
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