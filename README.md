# Leloutama
A multi-threaded webserver, using sockets, written entirely in PHP.

### Requirements
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

You have to override the `onReady` method. This method will be called when the server receives a request.

You can use the methods:

* setBody
* setMime
* setStatus

To make create the response.

### Method Details

* setBody:

Will need the content you would serve for that route, as the argument

* setMime:

Needs the mime type of the file you are serving as the argument.

* setStatus:

Is used to set the HTTP status code of the response you are sending. Needs the status as an argument.

---

You can also use the method `defaultSet` to set all those things, which needs an array with the keys, `body` (needs to contain the content to serve), `mime`(needs to contain MIME type of the content which is being served), `status`(needs to contain status of the response being sent), `fileName` (needs to contain the file name of the file from which the content is being served, if any, set to an empty string).

And the final Response class should be something like this:

```
class Response extends \Leloutama\lib\Core\Utility\AbstractResponse {
    public function onReady($fileName) {

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

// And lastly, bind the exposure route and a route to the index page
// The argument for the onReady method must be supplied by the constructor of the Response
$router->bind("/", (new Response("/index.html")));

// And make sure to return the router, or the server will throw a brick at your face
return $router;
```

## Handling `POST` data

If some post data is sent, you can access that data using:

```
$postData = $this->getRequest()->getPostData();
```

In any method of a router instance.

Then, `$postData` would contain an array with the following structure:

```
Array(
    "raw" => <string> (Contains the raw post data),
    "parsed" => <array> (Contains the kv-pair of the data in an array format)
)
```

If there is no post data, then `$postData` would contain an empty array.

## Extensions

There are two types of extensions, namely:

* ClientExt
* ServerExt

### ClientExt

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

The Server Extensions should be stored under the directory, `/path/to/Leloutama/src/lib/Ext/ServerExt/`.

As the Client Extensions, each server extension should also have a directory, and the main class, and all the resources, inside it.

Every ServerExt has to include and implement the interface `ServerExtension`, which is basically, this:

```
interface ServerExtension {
    public function __construct(array $configuration, string $docRoot);

    public function beforeConstruct(Router $router, string $stringHeaders);

    public function afterRequestBuild(Request $request, Http $http);

    public function beforeHeaderCreationCall(string $content, string $mime, int $status, string $fileName);
    
    public function afterHeaderCreation(array $headers, string $content, string $mime, int $status, string $fileName);

    public function beforeFinalServe(array $headAndBody);
}
```

The server extension must define all of the functions above.

If the extension doesn't want to return anything, it **has to return null**.

Every extension is constructed with the following arguments:

* Configuration for that specific ext (1st arg)
* The Document root (2nd arg)

### Method Details

#### beforeConstruct -
This method is called just after initializing the extensions. It is called with the `Router` and the HTTP Headers in a string, as the arguments.

It needs to return an array in the format:

```
Array (
    "router" => Router <The router object, which the extension may or may not modify>,
    "stringHeaders" => "{HEADERS}" <The raw string headers which were reecieved>
)
```

Or `null` if the extension doesn't want the server to trigger anything.

And the server will modify the internal values accordingly.

#### afterRequestBuild -
This method is called after the request is set in the server. It is called with the arguments, `Request` holding the Request object, and the `Http` instance of the server.

Should return the `Request` after doing any modification.

Or `null` if the extension doesn't want the server to trigger anything.

#### beforeHeaderCreationCall -
This method is called before the call to the method where the headers are created according to the content, status, mime, etc. etc. The arguments passed to it are: `$content` (i.e. The content which is to be sent to the client by the server), `$mime` (i.e. The mime type of the content that is being sent to the server), `$status` (i.e. The status of the response being sent to the client), `$fileName` (i.e. file name of teh file from which, the content being served, was taken from)(The file name can be an empty string, if the file name is empty, you should not change it).

Should return an array structured like:

```
Array (
    "content" => $content <The content to be served>,
    "mime" => $miem <The mime type of the content being served>,
    "status" => $status <The status code of the response being sent>,
    "fileName" => $fileName <The FileName of the file, from which the content was taken from>
)
```

Or `null` if the extension the extension doesn't want the server to trigger anything.

#### afterHeaderCreation -
This method is called after the headers has been created. It is called with the arguments, `$headers` (i.e. The array which contains the headers)(If any header is to be added, it needs to be done in this way: `$header[] = "Header: Header Value"`),`$content` (i.e. The content which is to be sent to the client by the server), `$mime` (i.e. The mime type of the content that is being sent to the server), `$status` (i.e. The status of the response being sent to the client), `$fileName` (i.e. file name of teh file from which, the content being served, was taken from)(The file name can be an empty string, if the file name is empty, you should not change it).

Should return the `$headers` array.

If the extension doesn't want the server to trigger anything, it should return `null`.

#### beforeFinalServe -

This method is called just before serving the pages, and is supplied with an array consisting of the header and body, as the argument.

Should return an array in the format:

```
Array (
    0 => $header <The Headers>
    1 => $body <The Body>
)
```

And should return `null`, if the extension doesn't want the server to trigger anything.

### How to use ServerExts?

While creating the server instance, an array has to be passed as the second argument like:

```
$server = new \Leloutama\lib\Core\Server\Server($router, ["ServerExt1", "ServerExt2"], $host, $port);
```

## Bundled Ext Docs

**FileCheckr:**
This extension can be used to safely load files from local storage.

This extension is as minimal as it can possibly get.

Methods Overview:
After getting an instance of the class, a file can be loaded, by calling the method `load($fileName)` upon the object, where `$fileName` would contain the absolute path of the file to be loaded.
If the file is not found, it loads an application error template.

The data from the object can be got using the method `getData()`, which would return an array in the following format:

```
Array(
    "body" => string,
    "mime" => string,
    "status" => int,
    "fileName" => string
)
```

Where `body` is the content to be served, `mime` is the mime type of the content being served, `status` is the HTTP status code of the response, and the `fileName` is the file name of the file, from which the content is being served.

That data can be simply used in the response by using the method `$this->set($data)` in a method of the response class, where `$data` would be the return-ed data from `FileCheckr`.

**ErrorDocuments:**

Specify some custom error documents for some specific error.

You need to specify error document for each error code like this:

```
"{ERROR_CODE}": "{ABSOLUTE_PATH_TO_CONTENT}"
```

Under the `ErrorDocuments`' config.

An exemplary error doc is already set up, that is, this:

```
"404": "%docRoot%/404.html"
```

You can use the variable `%docRoot%` for the absolute path of the content. `%docRoot%` simply resolves to the defined `docRoot` in the config file.

## How to use the `cli/run.php`? or How to run the server?

You can get the info about the options by by running the script with option `-h`.

You need to specify three options, or you can omit `--host` and `--port`, which would be set to default if nothing is specified.

The default for `host` is `127.0.0.1` and `port` is `9000`.

But make sure to specify `--router`, which would require the name of the file you are using as the router.
Like:

```
$ php cli/run.php --router example/router.example.php 
```

And the host and the port can also be defined, like:

```
$ php cli/run.php --host 127.0.0.2 --port 7005 --router example/router.example.php
```

Server Extensions can be specified like:

```
$ php cli/run.php --server-ext ServerExt1,ServerExt2 --router router.example.php
```

*If multiple extension is to be loaded, each extension has to be separated by an, ',' ONLY.*

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
* Implement SSL
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
* No SSL/TLS support.

## PR-s

Feel free to PR something...
I really look forward for your contribution.

## Issues

Currently, none, but is sure to be coming up, with further use.