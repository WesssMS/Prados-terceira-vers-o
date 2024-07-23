<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u426388430_PK1JP' );

/** Database username */
define( 'DB_USER', 'u426388430_IrHzV' );

/** Database password */
define( 'DB_PASSWORD', 'MyDJ7ZrZJJ' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'x}zBlWtLF+&;vJYBM8&apW$9#FoD@aPGNC7OTQ3a:y]}O*4F*O5g8T_HsBtB0R6T' );
define( 'SECURE_AUTH_KEY',   'vXRwMs+ u@L{w^@/$8v?/QZe92rIvLy8LD.s1tBwT[U2$s,wewaeQ3Z>|7EKs22Y' );
define( 'LOGGED_IN_KEY',     '.2u<+DpZ-,:Xd[8p21{yjT?|jnR[9K_U6tK<&z0^x!A.Z4.v[|Pft|mPnii+;(r0' );
define( 'NONCE_KEY',         '4HFoj9D`fkA(loPVK}`.*;|t*!.]ecK-V:pY6<o4M<X,}=bmW.DM_*pLRm`8<o>W' );
define( 'AUTH_SALT',         'W#fUU@9fT`LaK10L5qv}iC-}MpHHw)zbNZJPC]Rwxq+{n%]e@?+zq:O3*{u}U =G' );
define( 'SECURE_AUTH_SALT',  ')VPt/L!0w?`05s!+Y[(-*+/9UFV0(P`f]f;6!-~A}S@-RBP5Ar$bE?{OB ?xRPso' );
define( 'LOGGED_IN_SALT',    '~G1hl91!XO/{ voi-x,$!_rEJ%GNg<R4&dyof|4h :^6vxu[jw&-Er)PW.5Brv|r' );
define( 'NONCE_SALT',        '_@pdaUy/u6|eig<AO,XCbjqxw<;13D=aNMB*OeG/[HSf~v`w_[{Tn6q2yVF~Ez9(' );
define( 'WP_CACHE_KEY_SALT', 'HIHoV6VJN0.q/&33NSz8XFX139(q!8Eqgx?Rq$kE_et!DeVvQKmQ;76QY##w=x`R' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
