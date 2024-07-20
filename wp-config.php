<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do banco de dados
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Configurações do banco de dados - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define( 'DB_NAME', 'prados_db03' );

/** Usuário do banco de dados MySQL */
define( 'DB_USER', 'root' );

/** Senha do banco de dados MySQL */
define( 'DB_PASSWORD', '' );

/** Nome do host do MySQL */
define( 'DB_HOST', 'localhost' );

/** Charset do banco de dados a ser usado na criação das tabelas. */
define( 'DB_CHARSET', 'utf8mb4' );

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define( 'DB_COLLATE', '' );

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '-{%uY#?vE<W+._;!xPtw+xY;II1KXdhslm^2k)T!sNl6Y6QHeN/oo_H2i-HuY|ja' );
define( 'SECURE_AUTH_KEY',  'qkxJN0Nd%>xAQ`kTwkqtV2%S=}7[p|8*HGx;f>r3(a:/hBMT&5n)FFkkX<cbw0}7' );
define( 'LOGGED_IN_KEY',    'M`^!=e,PHNc[7Sn-)WdNIQlb,15jZSF7V`65:OW 2)m``E){Idelo i6$?YNH&?T' );
define( 'NONCE_KEY',        '^~QpND?{~g#65^)Z-z; 9(83ZO)/ua%uc?d%xIX>LelpoUQ0*A,J[(!Q9w yqd.p' );
define( 'AUTH_SALT',        'h$%oy!TZYq:p<i>1$t/eKz{W(LOL3`iSry$PU_wswR[^&Iat]Patv7F@jswV*lC8' );
define( 'SECURE_AUTH_SALT', 'SW Zq7n+$]`c^p/I}))]9[QeRn79|Up@e(U9IXU3E0$r$F7rjGY}=j&}N)m(mOhY' );
define( 'LOGGED_IN_SALT',   '}2[{5ZY;Q>>$O~5qS9_.!Ip-nBf#Z.+8w=I@!`4&BL5u0EsZb(. 8s)BuT3rvN(f' );
define( 'NONCE_SALT',       'Gu4m5DC$2a[>_?^]^_VV=4g6/>B2tK*nr5z34B&ii&])D5U,?8:Ht@edY>[!vv0d' );

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix = 'digit_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Adicione valores personalizados entre esta linha até "Isto é tudo". */



/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Configura as variáveis e arquivos do WordPress. */
require_once ABSPATH . 'wp-settings.php';
