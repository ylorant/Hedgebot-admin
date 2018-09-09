# HedgeBot Web Admin

*Hedgebot, chatting around at the speed of sound*

Hedgebot is a Twitch Bot written in PHP, aimed at a server-side use. It aims to provide a
flexible and powerful setup for streamers and communities to improve their chatrooms.

This specific project is a user-friendly web interface administration.

It requires at least PHP 7.0 to be used.

## What it does


## First steps
First, don't forget to launch Hedgebot "bot" first ;)
It will use Twitch OAUTH App token to use chat.

Insert basic configuration into app/config/parameters.yml web-admin to begin.
Don't forget "hedgebot_api_token" to communicate with the bot directly !

To create a new interface user in command line, use this :

`php bin/console user:create`

It will launch a friendly command-line form to begin.

After logging, first important thing is to create client token with Web-Admin to use Twitch API.
This will launch Twitch confirmation to allow Hedgebot Web-Admin to launch Twitch native features, like change stream title.

Now, enjoy !