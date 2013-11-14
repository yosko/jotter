# Jotter

Jotter is a lightweight, no database, powerful web notebook that lets you create and manage notes online safely, quickly & easily.

## Features

- WYSIWYG (What You See Is What You Get) editor
- organize notes hierarchically
- manage as many notebooks as you want
- multi-user support
- no DBMS needed. Everything is stored in flat files (JSON & Markdown)

## TODO list

- Trash bin for deleted notes
- Keep last versions of notes and restore it on demand
- Option to make some notes/notebooks publicly accessible
- Share notebooks between users
- Patch the WYSIWYG and Markdown libraries to enhance behavior and avoid most common rendering problems

## Requirements

- PHP 5.3 or above
- write access to the sub-directory `data/`

## Install

1. Upload it (or `git clone` it) on your server (let's say in `/var/www/jotter`)
2. Go to the corresponding URL (lets say `http://www.example.com/jotter`)

## License

Jotter is a work by [Yosko](http://www.yosko.net), all wright reserved.

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