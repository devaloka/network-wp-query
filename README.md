# Network WP Query [![Build Status](https://travis-ci.org/devaloka/network-wp-query.svg?branch=master)](https://travis-ci.org/devaloka/network-wp-query) [![Packagist](https://img.shields.io/packagist/v/devaloka/network-wp-query.svg)](https://packagist.org/packages/devaloka/network-wp-query)

A WordPress plugin that provides Network-wide WP_Query for Multisite
environment.

This plugin is based on / a improved version of [WP_Query_Multisite](https://github.com/miguelpeixe/WP_Query_Multisite)
(a custom version of [WP_Query_Multisite](https://github.com/ericandrewlewis/WP_Query_Multisite)).

## Manual Installation

1.  Just copy all files into `<ABSPATH>wp-content/plugins/network-wp-query/`.

## Manual Installation (as a Must-Use plugin)

1.  Just copy all files into `<ABSPATH>wp-content/mu-plugins/network-wp-query/`.

2.  Move `network-wp-query/loader/50-network-wp-query-loader.php`
    into `<ABSPATH>wp-content/mu-plugins/`.

## Installation via Composer

1.  Install via Composer.

    ```sh
    composer require devaloka/network-wp-query
    ```

## Installation via Composer (as a Must-Use plugin)

1.  Install via Composer.

    ```sh
    composer require devaloka/network-wp-query
    ```

2.  Move `network-wp-query` directory into
    `<ABSPATH>wp-content/mu-plugins/`.

3.  Move `network-wp-query/loader/50-network-wp-query-loader.php`
    into `<ABSPATH>wp-content/mu-plugins/`.

## Example Usage

### Standard Loop

```php
<?php $query = new WP_Query(['network' => true]); ?>

<?php if ($query->have_posts()): ?>
    <?php while ($query->have_posts()): $query->the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
```

### Query to several specific Sites

```php
<?php $query = new WP_Query(['network' => true, 'site__in' => [1, 2, 3]]); ?>

<?php if ($query->have_posts()): ?>
    <?php while ($query->have_posts()): $query->the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
```

### Query excluding several specific Sites

```php
<?php $query = new WP_Query(['network' => true, 'site__not_in' => [1, 2, 3]]); ?>

<?php if ($query->have_posts()): ?>
    <?php while ($query->have_posts()): $query->the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
```

### Limit the number of posts per Site

```php
<?php $query = new WP_Query(['network' => true, 'posts_per_site' => 1]); ?>

<?php if ($query->have_posts()): ?>
    <?php while ($query->have_posts()): $query->the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
```

## Parameters

| Name           | Type    | Description                               |
| -------------- |:-------:| ----------------------------------------- |
| network        | boolint | Whether perform network-wide query.       |
| site__in       | bool    | Blog IDs to include in the query.         |
| site__not_in   | bool    | Blog IDs to excluded from the query.      |
| posts_per_site | int     | The number of posts per Site to retrieve. |

## References

*   [ericandrewlewis/WP_Query_Multisite](https://github.com/ericandrewlewis/WP_Query_Multisite)
*   [miguelpeixe/WP_Query_Multisite](https://github.com/miguelpeixe/WP_Query_Multisite)
*   [#22816 (Multisite WP_Query) – WordPress Trac](https://core.trac.wordpress.org/ticket/22816)
