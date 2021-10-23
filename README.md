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
2. Copy and adapt env.template.php to env.php 
3. Change the friendly captcha widgets endpoint to user your server
4. In your backend configuration, use the your own server endpoint and

## Endpoints

Instead of `https://api.friendlycaptcha.com/api/v1/siteverify` use `https://yourserver/siteverify.php`.
Instead of `https://(eu-)api.friendlycaptcha.eu/api/v1/puzzle"` use `https://yourserver/puzzle.php`. 

## What works

* Check of signature
* Check of puzzles
* Check of timestamps

## Roadmap

* Replay check (by database? Or in memory?) APCU? 

## What's not being implemented 

* No smart difficulty scaling / we don't keep track of past requests to scale the difficulty, pull requests are welcome



