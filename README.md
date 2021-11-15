# xoom-express

Express is a jetpack of dev tools for WP websites.

## Installation

1. Download this repo as .zip archive
2. Add the .zip archive in Plugins admin page
3. Activate the plugin Express
4. Go the new Express admin page (submenu of Plugins menu) 

## Examples


* basic WP setup 

```
set_option?name=default_comment_status&value=
set_option?name=default_ping_status&value=
set_option?name=default_pingback_flag&value=0
set_option?name=show_avatars&value=0
set_option?name=blog_public&value=0


add_page?post_title=home
add_page?post_title=news
add_page?post_title=products
add_page?post_title=contact

add_menu?name=primary&title=home&url=/
add_menu?name=primary&title=news&url=/news
add_menu?name=primary&title=products&url=/products
add_menu?name=primary&title=contact&url=/contact

set_front_page?post_title=home
set_post_page?post_title=news

```

* also unzip plugins from github or other remote site

```
git?url=https://github.com/xoomcoder/xl

git?branch=master&url=https://github.com/xoomcoder/xoom-express

unzip?url=https://github.com/xoomcoder/xoom-express/archive/refs/heads/master.zip

```

* and more

```

insert_post?post_title=home&post_status=publish&post_type=page


script?url=https://raw.githubusercontent.com/xoomcoder/xoom-express/master/assets/scripts/example1.md


add_maintenance

add_maintenance?mode=off


create_plugin?name=myplugin
create_theme?name=mytheme

```


## TODO

* create plugin     (ongoing)
* create theme      (ongoing)

* delete page
* delete post
* update page
* update post

* create user
* delete user
* update user



