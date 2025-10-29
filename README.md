# make-post-dirty

A useful tool for populating the WP block editor __title__ and __content__.

<img width="348" height="275" alt="make-post-dirty-screenshot" src="https://github.com/user-attachments/assets/b5c89f02-ab68-4967-b9c0-4f49c93ef540" />

## Download

Download from [WordPress plugin repository](https://wordpress.org/plugins/make-post-dirty/).

You can also get the latest version from any of our [release tags](https://github.com/badasswp/make-post-dirty/releases).

## Why Make Post Dirty?

As a WP engineer, you may often find yourself creating posts to test a feature you're working on. This can get very tiresome, especially if you always have to create a __title__ and __content__ for every single post just to enable you accurately test a new feature.

This plugin makes it super easy for you to populate the `title` and `content` of your new post, so that you can just focus on testing out your new feature. It's that simple!

https://github.com/user-attachments/assets/e93a055c-b495-4719-ae26-45637bc4da64

### Hooks

#### `make_post_dirty_admin_fields`

This custom hook (filter) provides a way to filter the admin fields presented on the options page of the plugin.

```php
add_filter( 'make_post_dirty_admin_fields', [ $this, 'custom_admin_fields' ] );

public function custom_admin_fields( $fields ): array {
    $fields[] = [
        'name'    => 'name_of_your_control',
        'label'   => __( 'Control Label', 'your-text-domain' ),
        'cb'      => [ $this, 'name_of_your_control_callback' ],
        'page'    => 'make-post-dirty',
        'section' => 'make-post-dirty-section',
    ];

    return $fields;
}
```

**Parameters**

- fields _`{array}`_ By default this will be an array containing key, value options for the control.
<br/>

#### `make_post_dirty_settings`

This custom hook (filter) provides a way to customise the settings used by the notification bar.

```php
add_filter( 'make_post_dirty_settings', [ $this, 'custom_bar_settings' ] );

public function custom_bar_settings( $settings ): array {
    $settings['title'] = esc_html(
        'The Amazing Great Gatsby...'
    );

    return $settings;
}
```

**Parameters**

- settings _`{array}`_ By default this will be an associative array containing key, value options of the settings used by the notification bar on the front-end.
<br/>

## Contribute

Contributions are __welcome__ and will be fully __credited__. To contribute, please fork this repo and raise a PR (Pull Request) against the `master` branch.

### Pre-requisites

You should have the following tools before proceeding to the next steps:

- Composer
- Yarn
- Docker

To enable you start development, please run:

```bash
yarn start
```

This should spin up a local WP env instance for you to work with at:

```bash
http://make-post-dirty.localhost:9525
```

You should now have a functioning local WP env to work with. To login to the `wp-admin` backend, please use `admin` for username & `password` for password.

__Awesome!__ - Thanks for being interested in contributing your time and code to this project!
