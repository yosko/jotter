# Jotter

![Jotter](http://www.yosko.net/data/images/jotter.png)

Jotter is a lightweight, no database, powerful web notebook that lets you create and manage notes online safely, quickly & easily.

See [the demo](http://tools.yosko.net/demos/jotter/) or install it yourself!

## Features

- WYSIWYG (What You See Is What You Get) editor
- organize notes hierarchically
- manage as many notebooks as you want
- multi-user support
- no DBMS needed. Everything is stored in flat files (JSON & Markdown)

![Jotter screenshot](http://www.yosko.net/data/images/jotter-v0.1.png)

## Requirements

- PHP 5.3 or above
- write access to the sub-directory `data/`

## Install

1. Upload it (or `git clone` it) on your server (let's say in `/var/www/jotter`)
2. Go to the corresponding URL (lets say `http://www.example.com/jotter`)

## TODO

- Next version:
  - drag & drop to move notes/directories within a notebook
- Following ones:
  - remember folded/unfolded folder (will change the save format)
  - Make the wysiwyg optional (& directly write notes in Markdown)
  - Trash bin for deleted notes
  - Keep last N versions of each note and restore it on demand
  - Option to make some notes/notebooks publicly accessible
- Not sure if possible:
  - Sync API (à la Simplenotes?) for desktop/mobile apps
  - Share notebooks between users & handle concurrent edit (à la Etherpad?)
  - Patch the WYSIWYG and Markdown libraries to enhance behavior and avoid most common rendering problems

## Version History

- v0.2 (2013-11-22)
  - fold/unfold directories (not yet saved on server)
  - moved/changed some buttons for better ergonomics
  - always keep toolbar visible
  - change notebook without returning to homepage
  - interactive source code display (whitout base64 code)
  - image button implemented
  - prefill link with 'http://'
  - FIX random sort order
  - other minor fixes and tweaks
- v0.1 (2013-11-18)
  - initial version

## License

Jotter is a work by [Yosko](http://www.yosko.net), all rights reserved.

It is licensed under the [GNU LGPL](http://www.gnu.org/licenses/lgpl.html).

## Dependencies

Everything you need to make Jotter work is already on this repository. It includes:

- [PHP Markdown](https://github.com/michelf/php-markdown/)
- [HTML To Markdown for PHP](https://github.com/nickcernis/html-to-markdown), under the MIT license.
- [YosLogin](https://github.com/yosko/yoslogin), under the GNU LGPL license. This library also includes:
  - [Secure-random-bytes-in-PHP](https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP/), under the New BSD license.
- [bootstrap-wysiwyg](http://github.com/mindmup/bootstrap-wysiwyg), under the MIT license. This library also includes:
  - [jQuery Hotkeys](http://github.com/tzuryby/jquery.hotkeys), under the MIT & GPL2 licenses.
  - [jQuery](jquery.org), under the MIT license.
  - [Bootstrap.js](http://twitter.github.com/bootstrap/), under Apache License v2.0.
