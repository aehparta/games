# Games
Games handling webui.

Also other stuff saved here mostly for our lan gaming parties.

## Prerequisites

* apache2 (or some other which ever you want)
* php7 (with some extensions, figure it out, at least mbstring, xml, and cli)
* composer (used as globally installed one in this doc)

## Install

Get sources and then in sources root:

```sh
# install depencies
composer install
# copy config template and modify it to your setup
cp config/config-local.example.yml config/config-local.yml
nano config/config-local.yml
# update system asset paths
./kehikko assets:update
# setup apache htaccess if apache is being used (remember to enable mod rewrite)
cp config/htaccess.default web/.htaccess
```


# Configuring games
## Counter-Strike 1.6
### Example configuration `cstrike/csserver.cfg`
```
// Hostname for server.
hostname "<hostname>"

// RCON - remote console password.
rcon_password "password"

// Server password - for private servers.
sv_password "password"

// Server Logging
log on
sv_logbans 1
sv_logecho 1
sv_logfile 1
sv_log_onefile 0

// disable autoaim
sv_aim 0

// disable clients' ability to pause the server
pausable 0

// maximum client movement speed
sv_maxspeed 320

// cheats off
sv_cheats 0

// load ban files
exec listip.cfg
exec banned.cfg

sv_uploadmax 10.0

mp_autoteambalance 0
mp_limitteams 0
mp_autokick 0
mp_freezetime 3
mp_timelimit 0
mp_roundtime 3
mp_winlimit 10
mp_flashlight 1

pb_minbots 0
pb_maxbots 0
pb_detailnames 0
// disable shields
pb_restrweapons 00000000000000000000000001

// speed up downloads with this
sv_downloadurl "http://<games-webui-url>/files/cs"
```

### Running the server
```sh
./hlds_run -game cstrike -strictportbind +ip 192.168.1.9 -port 27015 +clientport 27005 \
+map de_dust2 +servercfgfile csserver.cfg -maxplayers 32 -insecure +sv_lan 0
```

### Required addons

For this webui to work fully you should install theses addons:

* metamod (of course)
* amxmodx

### Suggested addons

* podbot
* semiclip (because bots)
