# MemberPress Challenge

Just another wordpress plugin test

### Requirements

Using this GET accessible [endpoint](https://cspf-dev-challenge.herokuapp.com/), create an AJAX endpoint in WordPress that:

- Can be used when logged out or in;

- Calls the above endpoint to get the data to return;

- Which when called always returns the data, but regardless of when/how many times it is called
should never request the data from our server more than 1 time per hour.

- Create a shortcode for the frontend, that when loaded uses Javascript to contact your AJAX
endpoint and display the data returned formatted into a table-like display;

- Create a WP CLI command that can be used to force the refresh of this data the next time the AJAX
endpoint is called;

- Create a WordPress admin page which displays this data in the style of the admin page of the
WordPress plugin MemberPress (https://memberpress.com/wp-content/uploads/2019/05/Screen-
Shot-2019-05-13-at-6.41.08-PM.png) and add a button to refresh the data.

- Organize & package the code as a WordPress plugin zip file

Ensure to properly escape, sanitize and validate data in each step as appropriate using built in PHP and
WordPress functions.

The code should not be built from a boilerplate.

### Extras Added

- Export to csv bulk action in admin table
- Add a simple plugin logger trait for some debugging
- Add some admin side feedback messages to user after select actions
- Responsive vertical rendering of shortcode table for mobile / small tablet view 
- Docker (compose) setup

## Installation


### The Docker (compose) way

- clone the repo 

`git clone https://github.com/djleven/mpChallenge.git`

- navigate to directory

`cd mpChallenge`

- copy the '.env.dist' file and name it '.env' 

make changes in env file as you see fit

- run docker compose

`docker-compose up -d`

- navigate to localhost on your browser

- perform the GUI WP installation (only done the first time)

Note: For more info on the Docker/WP setup check here
[repo](https://github.com/nezhar/wordpress-docker-compose)

### Manually

- download the zip and extract it

- copy the `mp-challenge` folder and contents to the `plugins` folder
of your wordpress installation


## Activation

- Activate the plugin and theme in your wp admin backend


## WPCLI

### Plugin command

- To reset the cached version of the remote endpoint data

`wp mepr_challenge purgeTransient`

### WPCLI usage in current docker-compose setup

- Navigate to the root of the project and run 

`./wpcli`

or (short for)

`docker-compose exec /bin/bash`

This will launch a command prompt inside the wpcli container

Note: 
> The wcpli container shares the same database and the MemberPress Challenge plugin files with the wordpress container. 

> It does not however share the same wordpress files. Ideally, this is what you want to have.
However, for the limited purposes of testing this plugin (or any manipulation of the database) the current setup will suffice.


## Screenshots

![admin screenshot img](/mp-challenge/screenshot-admin.png?raw=true)


![shortcode screenshot img](/mp-challenge/screenshot-shortcode.png?raw=true)


![shortcode mobile screenshot img](/mp-challenge/screenshot-mobile-shortcode.png?raw=true)