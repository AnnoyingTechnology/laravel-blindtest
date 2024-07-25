# Installation

Sequentially 
* `composer install`
* `npm install`
* Place your music in `storage/app/music` (MP3 files)
* `php artisan music:scan`

# Running

In parallel 
* `php artisan serve`
* `php artisan reverb:start`
* `npm run dev`

# In-game commands 

* `/next [genre]` pulls a new random track
* `/ff` fast forwards in the track
* `/giveup` shows track info. No further answers will be accepted for that track.
* `/reset` resets the scores. All Users will have their score set to zero.

# Security 

There is no actual authentication. Anyone can choose any username on the login page.

This _should_ not be exposed on the internet, or either 
* add an AuthBasic
* implement password authentication on a fork of this project
