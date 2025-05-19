<?php

/* -------------------------------------------------------------------------- */
/*                               MySQL Settings                               */
/* -------------------------------------------------------------------------- */

/** The name of the database **/
define('DB_NAME', getenv('DB_NAME') ? getenv('DB_NAME') : 'db_name');

/** MySQL database username */
define('DB_USER', getenv('DB_USER') ? getenv('DB_USER') : 'db_user');

/** MySQL database password */
define('DB_PASSWORD', getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : 'db_password');

/** MySQL hostname */
define('DB_HOST', getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', getenv('DB_CHARSET') ? getenv('DB_CHARSET') : 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', getenv('DB_COLLATE') ? getenv('DB_COLLATE') : '');

/** Rounding Precision - Used for formulae */
define('CONFIG_PRECISION', getenv('CONFIG_PRECISION') ? getenv('CONFIG_PRECISION') : 2);

/* -------------------------------------------------------------------------- */
/*                            Weather API Settings                            */
/* -------------------------------------------------------------------------- */

// See also: http://api.openweathermap.org/
define('OPENWEATHERMAP_API_KEY', getenv('OPENWEATHERMAP_API_KEY') ? getenv('OPENWEATHERMAP_API_KEY') : '');

/* -------------------------------------------------------------------------- */
/*                            Application Settings                            */
/* -------------------------------------------------------------------------- */

/** Enable or disable debug mode (disabled by default) */
define('DEBUG_MODE', getenv('DEBUG_MODE') ? getenv('DEBUG_MODE') : false);
// define('DEBUG_MODE', true);