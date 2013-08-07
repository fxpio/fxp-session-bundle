Sonatra SessionBundle Command Initialization
============================================

## Prerequisites

[Installation and Configuration](index.md)

## Use

Execute command:

``` bash
$ php app/console init:session:pdo
```

## Configure the session handler

Edit the `app/config/config.yml`:

``` yaml
#...

framework:
    session:
        handler_id: sonatra_session.handler.pdo
```