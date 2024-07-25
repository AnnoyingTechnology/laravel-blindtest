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
* `php artisan music:scan` _scan your music and extracts ID3 tags_

# Running

In parallel 
* `php artisan serve` _runs the local webserver_
* `php artisan reverb:start` _runs the websocket_
* `npm run dev` _runs the JS/CSS tools_

# In-game commands 

* `/next [genre]` pulls a new random track
* `/ff` fast forwards in the track
* `/giveup` shows track info. No further answers will be accepted for that track.
* `/reset` resets the scores. All Users will have their score set to zero.

# Security 

**There is no actual authentication.** 

Anyone can choose any username on the login page.

This application **must** not be exposed on the internet, otherwise 
* add an AuthBasic
* implement a password authentication on a fork of this project

# Tech

* Uses Websocket for real-time communication
* Uses perfect or partial artist and track title matches
