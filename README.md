# craft-db-extract plugin for Craft CMS 3.x

A small helper Plugin for CraftCMS to download the DB over HTTP requiring authorization.

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require pjanser/craft-db-extract

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for craft-db-extract.

## craft-db-extract Overview

This plugin makes a web controller action available to pull the database export.

## Using craft-db-extract

The controller action requires an admin login. Provide your credentials in the **Authoirization** header
with basic authentication.

HTTP Header:
```http
Authorization: Basic xxxxxxxxxxxx
```
**Tip:** If you experience **401 Unauthorized** eventhough you provide correct admin credentials, you can try to add the followin line to your `.htaccess`:
```apache
SetEnvIf Authorization (.+) HTTP_AUTHORIZATION=$0
```

Pulling the database in *.sql format:
```http
GET YOUR_WEBSITE/actions/craft-db-extract/db-export
```

Pulling the database in *.sql.gz format:
```http
GET YOUR_WEBSITE/actions/craft-db-extract/db-export?compression=gzip
```


## craft-db-extract Roadmap

Some things to do, and ideas for potential features:

* Release it

Brought to you by [P. Janser](https://github.com/qbasic16/)
