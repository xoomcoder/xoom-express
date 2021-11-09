# xoom-express

Express is a jetpack of dev tools for WP websites.

## examples


* basic WP setup 

```
set_option?name=default_comment_status&value=
set_option?name=default_ping_status&value=
set_option?name=default_pingback_flag&value=0
set_option?name=show_avatars&value=0


add_page?post_title=home
add_page?post_title=products
add_page?post_title=contact

add_menu?name=principal&title=home&url=/
add_menu?name=principal&title=products&url=/products
add_menu?name=principal&title=contact&url=/contact

set_front_page?post_title=home

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

```
