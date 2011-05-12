<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'freiland');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'Lu1sM1gu3l');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'w,NwGXviHv_[ub/#f-P9!Nxl 3cQq2y|n)m$b7W>=9}P0}6Oq>)6/4p?@*tI7nWg');
define('SECURE_AUTH_KEY',  'z.581EB@UbH]-l0CHbIM&V8M-6;2M*`=Gqa<KB9J{%Z9UV0ubwZ^|X.-10-Jy;tT');
define('LOGGED_IN_KEY',    '-,l&4rxEKZj)iK3`YzQrRdFj*R we50:,`Ts~jszDUa^]>byNppli5v4|*c]Qc@U');
define('NONCE_KEY',        'mI;^28-`|wTxe(3/l|I=5)K@VceuA3.JwE5}cuNM%QF7boeReSCxw})WQ_);Hrge');
define('AUTH_SALT',        'PaIzewaT%xClQWUQ>M9%c~7PX!^ry p(+W4txm1m4W,+0#0|H3t|H=nHMz.*UJ+-');
define('SECURE_AUTH_SALT', '}Qh4&M92-M7lvf+J`eYrq6tgX?G2i5)#T)%1j%T#[eQ/;+]{y4?Pq+tErghc0@b4');
define('LOGGED_IN_SALT',   'IIcJK_<Yj<kuQ+vptz-2vY-E,LZ[[_y`@`a3ghE)b_niUzB9.R_H{e7x8bC$/3)W');
define('NONCE_SALT',       '2FKw@>ja:Cb7~ptq=Lt/dG iZ+:U:257Zc7#*/jZ-LYTlQ{GMzBB&sdQ@~2+0]i5');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* disaple wp_cron */
define('DISABLE_WP_CRON', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
