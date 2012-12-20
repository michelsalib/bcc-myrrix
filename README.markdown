# Myrrix php library

Myrrix is a recommendation engine built on [Apache Mahout libraries](http://mahout.apache.org/). If you don't know it already, you should have a look here [Myrrix website](http://myrrix.com).

The library helps you interface with the Rest API. It is build on top of [Guzzle](https://github.com/guzzle/guzzle).

[![Build Status](https://travis-ci.org/michelsalib/bcc-myrrix.png)](https://travis-ci.org/michelsalib/bcc-myrrix)

## Installation via Composer

The best way to use the library is via [Composer](http://getcomposer.org/).

Do in the command line:

```
composer require bcc/myrrix
```

Or Manually add the library to your dependencies in the composer.json file:

```
{
    "require": {
        "bcc/myrrix": "*"
    }
}
```

Then installyour dependencies:

```
composer install
```

## Usage

Before you start, don't forget to have an instance of the Myrrix server running. Simply download the [.jar excecutable](http://myrrix.com/download/) for the serving layer and run it:

```
java -jar myrrix-serving-x.y.jar --localInputDir /path/to/working/dir --port 8080
```

It will run a server on port 8080 and using the `/path/to/working/dir` directory as a backing storage. You can get more information about the server [here](http://myrrix.com/documentation-serving-layer/).

You can now access your recommendation engine in php:

``` php
// Instanciate the Myrrix service
$myrrix = new MyrrixService('localhost', 8080);

// Put a user/item assocation, here use #101 as an association of strength 0.5 with item #1000
$myrrix->setPreference(101, 1000, 0.5);

// Refresh the index
$myrrix->refresh();

// Get a recommendation for user #101
$recommendation = $myrrix->getRecommendation(101); // an array of itemId and strength (example: [[325,0.53],[98,0.499]])
```

## Authentication

You can choose to host your Myrrix server with authentication:

```
java -jar myrrix-serving-x.y.jar --localInputDir /path/to/working/dir --port 8080 --userName test --password 1234
```

In this case, you must specify username/password this way:

``` php
$myrrix = new MyrrixService('localhost', 8080, 'test', '1234');
```

## More functions

More functions include:
- Recommendation to many users
- Recommendation to anonymous
- More similar items
- Batch insertion of preferences
- ...

You can get a full list of functions in the [MyrrixService.php](https://github.com/michelsalib/bcc-myrrix/blob/master/src/BCC/Myrrix/MyrrixService.php) file.
