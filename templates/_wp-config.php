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
define('DB_NAME', '##DBNAME##');

/** MySQL database username */
define('DB_USER', '##DBUSER##');

/** MySQL database password */
define('DB_PASSWORD', '##DBPASS##');

/** MySQL hostname */
define('DB_HOST', '##DBHOST##');

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
define('AUTH_KEY',         's*<C|ZD81kolg7BLi:?~X[c,7:X`Dg?=u(,O7^oFSXC(h-b8`OquA4_)~x_1Fc>u');
define('SECURE_AUTH_KEY',  'N:UhX n6/j-yL8ncLZ<-e(<~0Ko+*lps!6g;[%vq5}>pf$%6kcw22Zq;9z9=--@>');
define('LOGGED_IN_KEY',    'sa,#FMY4A.,s_VdK>e6`9f#n-u _:1--_G3~dx.^&X=,(S;,B|@M(ka-|MyAeI^|');
define('NONCE_KEY',        'OwiY2?R=u`86-VN}/-`co!4b3LIJ/~5;ETus|.V(ACdA[-0FU^@ O,JpL5>$CKA-');
define('AUTH_SALT',        'i@I,[v&HMA>iYZ;g<v %+-eQ,N3*a+^Z~x+Syv{t;t?Xf+ %+7-#|QKmy}g.h]+r');
define('SECURE_AUTH_SALT', 'J,.*|5Z^NdjT.6(}h33CXy}|97$d~ri]Zc|;sXo0?Z]8dGMP_[U/hOD@Ds[{J.3:');
define('LOGGED_IN_SALT',   'pQ++ 601q) d3S6ie2>uAk:n@*=QII^zK[~IDVsMyn_!kx6zF@vi2i{ZN) +,G;r');
define('NONCE_SALT',       'FJzCqNUiwx1%1a(J8Az;1]u.j&$BlfyH(zWaXQ@{WnvF|]mfnV%ho ~s;I^]_(mT');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
