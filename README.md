# HedgeBot Web Admin

*Hedgebot, chatting around at the speed of sound*

Hedgebot is a Twitch Bot written in PHP, aimed at a server-side use. It aims to provide a
flexible and powerful setup for streamers and communities to improve their chatrooms.

This specific project is a user-friendly web interface administration.

It requires at least PHP 7.1 to be used.

## What it does

This interface provides a user-friendly web interface to handle all the functionalities of the bot.

## First steps
First, don't forget to launch Hedgebot "bot" first ;)
It will use Twitch OAUTH App token to use chat.

To install the project, just clone the repository, and inside it, run this command :

`composer install`

This will install the dependencies used by this project, and then you'll be prompted to type this command :

`php bin/console setup`

This will start a wizard asking you to enter basic info for the interface to work. Amongst them is the bot token, which should have been set in the bot configuration (or not, if you are using *unsecured* tokenless mode).

After the initial configuration, the wizard will ask you to create the first user of the interface.

## Usage specifics

### Configuration

If you want to edit configuration for the interface (for example modifying the bot address or token), you are free to do so by using the setup wizard again with this command :

`php bin/console setup`

Or you can also directly edit the file `app/parameters.yml` and set the variables there.

### Create users

To create a new interface user in command line, use this command :

`php bin/console user:create`

It will launch a friendly command-line form to create your user, similar to the one used in the setup procedure.

Now, enjoy !