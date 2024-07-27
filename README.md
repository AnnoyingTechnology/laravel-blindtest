# Screenshots

![Login Page](blindtest_login.png)

![Chatroom](blindtest_chatroom.png)

# Requirements 

* PHP 8.2+
* a few PHP extensions, nothing exotic

# Installation

Sequentially 
* `composer install` _installs PHP dependencies_
* `npm install` _installs NodeJS dependencies_
* Place your music in `storage/app/music` (MP3 files)
* `touch database/database.sqlite` _creates an empty database_
* `mv .env.example .env` _use the default env file_
* `php artisan migrate:fresh` _rebuilds the database_
* `php artisan reverb:install` _regenerates reverb keys and config (Answer Yes)_ 
* `php artisan music:scan` _scans your music, extracts ID3 tags and inserts in the database_
* `php artisan key:generate` _generates a crypto key_

# Running

In parallel 
* `php artisan serve` _runs the local webserver_
* `php artisan reverb:start` _runs the websocket_
* `npm run dev` _runs the JS/CSS tools_

# In-game commands 

* `/next [genre|year] [genre|year]` pulls a new random track
* `/ff` fast forwards in the track
* `/giveup` shows track info. No further answers will be accepted for that track.
* `/reset` resets the scores. All Users will have their score set to zero.
* `/clue` shows 20% of the letters of artist, name, remix and lessen the scorable points by -20%. Can be called multiple times.

Note that the genre/year is not saved. 

Consider the following sequence : 
* call `/next 2000 house`
* The next track will be of genre containing House in decade 2000-2009
* call `/next`
* The next track will be totally random

# Scoring

Matching is not case sensitive.

Finding an exact match for name, remix (if any) or artist of a track gives 1 point each (maximum score of +3 by track).

Consider the track name `Two Months Off`. The track name has 3 words so each word is worth at most 0.33. 

* Each word at the right place gives a third of a point. 
* Each word at the wrong place gives a third of a point, divided by two.

* Answering `two month off` would give 1 point.
* Answering `three months off` would give 2 out of 3 words at the right place : `0.33+0.33 = 0.66` points rounded to 0.7
* Answering `off two months` would give 3 out of 3 words, but none at the right place = `0.33/2+0.33/2+0.33/2` = 0.5 points


# Security 

**There is no actual authentication.** 

Anyone can choose any username on the login page.

This application **must** not be exposed on the internet, otherwise 
* add an AuthBasic
* implement a password authentication on a fork of this project

# Tech

* Uses Websocket for real-time communication
* Uses perfect or partial artist and track title matches

# Disclaimer

I wrote this in about 7h, being new to Laravel. 

This is first and foremost something to play with a few friends. Don't expect premium code quality (even average code quality tbh).