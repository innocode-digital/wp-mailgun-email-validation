# Mailgun Email Validation

### Description

Validates email address through [Mailgun](https://www.mailgun.com/email-validation-2/).

### Install

- Preferable way is to use [Composer](https://getcomposer.org/):

    ````
    composer require innocode-digital/wp-mailgun-email-validation
    ````

    By default it will be installed as [Must Use Plugin](https://codex.wordpress.org/Must_Use_Plugins).
    But it's possible to control with `extra.installer-paths` in `composer.json`.

- Alternate way is to clone this repo to `wp-content/mu-plugins/` or `wp-content/plugins/`:

    ````
    cd wp-content/plugins/
    git clone git@github.com:innocode-digital/wp-mailgun-email-validation.git
    cd wp-mailgun-email-validation/
    composer install
    ````

If plugin was installed as regular plugin then activate **Mailgun Email Validation** from Plugins page 
or [WP-CLI](https://make.wordpress.org/cli/handbook/): `wp plugin activate wp-mailgun-email-validation`.

### Usage

Add required constant (usually to `wp-config.php`):

````
define( 'MAILGUN_API_KEY', '' );
````

or

````
define( 'MAILGUN_APIKEY', '' );
````

You could use either one of these constants. `MAILGUN_APIKEY` is used also by
[Mailgun for WordPress](https://github.com/mailgun/wordpress-plugin) plugin.

### Documentation

By default plugin skips `admin_email` from validation but it's possible to enable it:

```
add_filter( 'innocode_mailgun_email_validation_skip_admin_email', function ( $skip, $email ) {
    return $skip;
} );
```

By default plugin skips users email from validation but it's possible to enable it:

```
add_filter( 'innocode_mailgun_email_validation_skip_user_email', function ( $skip, $email ) {
    return $skip;
} );
```

By default plugin checks if email address has `deliverable` or `unknown` status and hasn't
`high` risk but it's possible to set own criteria:

```
add_filter( 'innocode_mailgun_email_validation_validated', function ( $validated, array $email ) {
    return $validated;
} );
```

It's possible also to use `validate` and `is_valid` methods from plugin:

* `innocode_mailgun_email_validation()->get_client()->validate( $email )` - validates email
address with [Mailgun Email Validation](https://documentation.mailgun.com/en/latest/api-email-validation.html).

* `innocode_mailgun_email_validation()->is_valid( $email )` - uses previous method but also
applies filters and caches result, returns boolean value.
