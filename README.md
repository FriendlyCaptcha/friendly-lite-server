# polite-server - Self Hosted Captcha Server, compatible with for Friendly Captcha

## Motivation

FriendlyCaptcha.com offers a privacy aware captcha service.
Due to various reasons (for example very strict data protection rules) it might make sense to host the service
for puzzle and verification on your own machine.

## Subscriptions

We highly recommend to subscribe to the Friendly Captcha service to support their awesome work.

## Installation

You need a web server running PHP 7.4 or later.

1. Install the public folder to the your document root.
2. Patch the recaptcha widget to use your server
3. In your backend configuration, use the your own server endpoint and


## Configurations

t.b.d.
In config.php define any secret key.

## Patching the widget

Instead of `https://api.friendlycaptcha.com/api/v1/siteverify` use `https://yourserver/siteverify.php`.
Instead of `https://(eu-)api.friendlycaptcha.eu/api/v1/puzzle"` use `https://yourserver/puzzle.php`. 

## What works

* Check of puzzles 
* Check of timestamps

## Roadmap

* Replay check (by database? Or in memory?) APCU? 

## What's not being implemented 

* No smart difficulty scaling / we don't keep track of past requests to scale the difficulty, pull requests are welcome



