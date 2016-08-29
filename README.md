# Leloutama
A multi-threaded webserver, written entirely in PHP.

> **DO NOT USE CURRENT MASTER RATHER A RELEASE SOURCE. THINGS ARE BREAKING, AND WILL BREAK UNTIL THE NEXT RELEASE. YOU'VE BEEN WARNED.**

### Requirements
* PHP version >= 7, and a thread safe build
* The [pthreads](https://github.com/krakjoe/pthreads) extension for PHP.

# Install the dependencies and make things get going

First of all, clone this repo, by:

```
git clone https://github.com/gourabNagDev/Leloutama && cd Leloutama
```

Get started by installing the dependencies, and writing the patches for the dependencies! Don't worry everything is automated!
Just run the script by:
```
$ install/install
```
In the root directory of the server!
**AND THE COMPOSER EXECUTABLE MUST INSTALLED GLOBALLY!**

After that to launch the example server, do this:

```
$ cli/run --router examples/router.example.php
```

it would give an output like:
```
Server started successfully.
Listening on ip: 127.0.0.1 at port: 1337
```

Now, go to `localhost:1337` and you'd get to see the sample index page!

## TODO
* Implement doc-blocks (v1.3.1)
* Implement SSL (v1.3.1)
* Do the README (v.1.3.1)
* (...And more stuffs...)

## New Features

* Supports Twig Template Usage!
* More stable
* Design Upgrades

## Why should you use it?
* Supports HTTP Caching
* Supports middleware in the form of extensions
* Supports dynamic URL (thanks to FastRoute)
* Supports GET, HEAD, and POST
* No need to download additional servers, just PHP
* etc...

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