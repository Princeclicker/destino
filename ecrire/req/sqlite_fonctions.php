<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

/**
 * Ce fichier déclare des fonctions étendant les fonctions natives de SQLite
 *
 * On mappe des fonctions absentes de sqlite (notamment donc des fonctions présentes dans mysql)
 * à des fonctions équivalentes php, que sqlite exécutera si besoin.
 *
 * entre autre auteurs : mlebas
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Déclarer à SQLite des fonctions spécifiques utilisables dans les requêtes SQL
 *
 * SQLite ne supporte nativement que certaines fonctions dans les requêtes SQL.
 * Cependant, il permet d'étendre très facilement celles-ci en déclarant de
 * nouvelles fonctions.
 *
 * C'est ce qui est fait ici, en ajoutant des fonctions qui existent aussi
 * dans d'autres moteurs, notamment en MySQL.
 *
 * @see http://www.sqlite.org/lang_corefunc.html Liste des fonctions natives
 * @see http://sqlite.org/changes.html Liste des évolutions
 *
 * @param \PDO $sqlite Connexion SQLite (PDO ou PDO\Sqlite en PHP 8.4+)
 * @return false|void
 */
function _sqlite_init_functions(&$sqlite) {

	if (!$sqlite) {
		return false;
	}

	$fonctions = [
		// A
		'ACOS' => ['acos', 1],
		'ASIN' => ['asin', 1],
		'ATAN' => ['atan', 1], // mysql accepte 2 params comme atan2… hum ?
		'ATAN2' => ['atan2', 2],

		// C
		'CEIL' => ['_sqlite_func_ceil', 1],
		'CONCAT' => ['_sqlite_func_concat', -1],
		'COS' => ['cos', 1],

		// D
		'DATE_FORMAT' => ['_sqlite_func_date_format', 2], // équivalent a date() avec args inversés et format converti
		'DAYOFMONTH' => ['_sqlite_func_dayofmonth', 1],
		'DEGREES' => ['rad2deg', 1],

		// E
		'EXTRAIRE_MULTI' => ['_sqlite_func_extraire_multi', 2], // specifique a SPIP/sql_multi()
		'EXP' => ['exp', 1],

		// F
		'FIND_IN_SET' => ['_sqlite_func_find_in_set', 2],
		'FLOOR' => ['_sqlite_func_floor', 1],

		// G
		'GREATEST' => ['_sqlite_func_greatest', -1],

		// I
		'IF' => ['_sqlite_func_if', 3],
		'INSERT' => ['_sqlite_func_insert', 4],
		'INSTR' => ['_sqlite_func_instr', 2],

		// L
		'LEAST' => ['_sqlite_func_least', -1],
		'_LEFT' => ['_sqlite_func_left', 2],

		// N
		'NOW' => ['_sqlite_func_now', 0],

		// M
		'MD5' => ['md5', 1],
		'MONTH' => ['_sqlite_func_month', 1],

		// P
		'PREG_REPLACE' => ['_sqlite_func_preg_replace', 3],

		// R
		'RADIANS' => ['deg2rad', 1],
		'RAND' => ['_sqlite_func_rand', 0], // sinon random() v2.4
		'REGEXP' => ['_sqlite_func_regexp_match', 2], // critere REGEXP supporte a partir de v3.3.2
		'RIGHT' => ['_sqlite_func_right', 2],

		// S
		'SETTYPE' => ['settype', 2], // CAST present en v3.2.3
		'SIN' => ['sin', 1],
		'SQRT' => ['sqrt', 1],
		'SUBSTRING' => ['_sqlite_func_substring' /* , 3 */], // peut etre appelee avec 2 ou 3 arguments, index base 1 et non 0

		// T
		'TAN' => ['tan', 1],
		'TIMESTAMPDIFF' => ['_sqlite_timestampdiff'    /* , 3 */],
		'TO_DAYS' => ['_sqlite_func_to_days', 1],

		// U
		'UNIX_TIMESTAMP' => ['_sqlite_func_unix_timestamp', 1],

		// V
		'VIDE' => ['_sqlite_func_vide', 0], // du vide pour SELECT 0 as x ... ORDER BY x -> ORDER BY vide()

		// Y
		'YEAR' => ['_sqlite_func_year', 1],
	];

	foreach ($fonctions as $f => $r) {
		_sqlite_add_function($sqlite, $f, $r);
	}

	# spip_log('functions sqlite chargees ','sqlite.'._LOG_DEBUG);
}

/**
 * Déclare une fonction à SQLite
 *
 * @note
 *     Permet au besoin de charger des fonctions
 *     ailleurs par _sqlite_init_functions();
 *
 * @uses _sqlite_is_version()
 *
 * @param \PDO $sqlite Connexion SQLite (PDO ou PDO\Sqlite en PHP 8.4+)
 * @param string $f Nom de la fonction à créer
 * @param array $r Tableau indiquant :
 *     - le nom de la fonction à appeler,
 *     - le nombre de paramètres attendus de la fonction (-1 = infini, par défaut)
 */
function _sqlite_add_function(&$sqlite, &$f, &$r): void {
	// PHP 8.4+
	if (
		\PHP_VERSION_ID >= 80400
		&& class_exists(Pdo\Sqlite::class)
		&& $sqlite instanceof \Pdo\Sqlite
	) {
		isset($r[1])
			? $sqlite->createFunction($f, $r[0], $r[1])
			: $sqlite->createFunction($f, $r[0]);
		return;
	}

	isset($r[1])
		? $sqlite->sqliteCreateFunction($f, $r[0], $r[1])
		: $sqlite->sqliteCreateFunction($f, $r[0]);
}

/**
 * Mapping de `CEIL` pour SQLite
 *
 * @param float $a
 * @return int
 */
function _sqlite_func_ceil($a) {
	return ceil($a);
}

/**
 * Mapping de `CONCAT` pour SQLite
 *
 * @param string[] ...$args
 * @return string
 */
function _sqlite_func_concat(...$args) {
	return join('', $args);
}

/**
 * Mapping de `DAYOFMONTH` pour SQLite
 *
 * @uses _sqlite_func_date()
 *
 * @param string $d
 * @return string
 */
function _sqlite_func_dayofmonth($d) {
	return _sqlite_func_date('d', $d);
}

/**
 * Mapping de `FIND_IN_SET` pour SQLite
 *
 * @param string $num
 * @param string $set
 * @return int
 */
function _sqlite_func_find_in_set($num, $set) {
	$rank = 0;
	foreach (explode(',', $set) as $v) {
		if ($v == $num) {
			return ++$rank;
		}
		$rank++;
	}

	return 0;
}

/**
 * Mapping de `FLOOR` pour SQLite
 *
 * @param float $a
 * @return int
 */
function _sqlite_func_floor($a) {
	return floor($a);
}

/**
 * Mapping de `IF` pour SQLite
 *
 * @param bool $bool
 * @param mixed $oui
 * @param mixed $non
 * @return mixed
 */
function _sqlite_func_if($bool, $oui, $non) {
	return ($bool) ? $oui : $non;
}

/**
 * Mapping de `INSERT` pour SQLite
 *
 * Retourne une chaine de caractères à partir d'une chaine dans laquelle "chaine"
 * à été inserée à la position "index" en remplacant "longueur" caractères.
 *
 * @param string $s
 * @param int $index
 * @param int $longueur
 * @param string $chaine
 * @return string
 */
function _sqlite_func_insert($s, $index, $longueur, $chaine) {
	return
		substr($s, 0, $index)
		. $chaine
		. substr(substr($s, $index), $longueur);
}

/**
 * Mapping de `INSTR` pour SQLite
 *
 * @param string $s
 * @param string $search
 * @return int
 */
function _sqlite_func_instr($s, $search) {
	return strpos($s, $search);
}

/**
 * Mapping de `LEAST` pour SQLite
 *
 * @param int[] ...$args
 * @return int
 */
function _sqlite_func_least(...$args) {
	return min($args);
}

/**
 * Mapping de `GREATEST` pour SQLite
 *
 * @param int[] ...$args
 * @return int
 */
function _sqlite_func_greatest(...$args) {
	return max($args);
}

/**
 * Mapping de `LEFT` pour SQLite
 *
 * @param string $s
 * @param int $lenght
 * @return string
 */
function _sqlite_func_left($s, $lenght) {
	return substr($s, $lenght);
}

/**
 * Mappnig de `NOW` pour SQLite
 *
 * @param bool $force_refresh
 * @return string
 */
function _sqlite_func_now($force_refresh = false) {
	static $now = null;
	if ($now === null || $force_refresh) {
		$now = date('Y-m-d H:i:s');
	}

	# spip_log("Passage avec NOW : $now | ".time(),'sqlite.'._LOG_DEBUG);
	return $now;
}

/**
 * Mapping de `MONTH` pour SQLite
 *
 * @uses _sqlite_func_date()
 *
 * @param string $d
 * @return string
 */
function _sqlite_func_month($d) {
	return _sqlite_func_date('m', $d);
}

/**
 * Mapping de `PREG_REPLACE` pour SQLite
 *
 * @param string $quoi
 * @param string $cherche
 * @param string $remplace
 * @return string
 */
function _sqlite_func_preg_replace($quoi, $cherche, $remplace) {
	$return = preg_replace('%' . $cherche . '%', $remplace, $quoi);

	# spip_log("preg_replace : $quoi, $cherche, $remplace, $return",'sqlite.'._LOG_DEBUG);
	return $return;
}

/**
 * Mapping pour `EXTRAIRE_MULTI` de SPIP pour SQLite
 *
 * Extrait une langue d'un texte <multi>[fr] xxx [en] yyy</multi>
 *
 * @param string $quoi le texte contenant ou non un multi
 * @param string $lang la langue a extraire
 * @return string, l'extrait trouve.
 */
function _sqlite_func_extraire_multi($quoi, $lang) {
	if (str_contains($quoi, '<')) {
		include_spip('src/Texte/Collecteur/AbstractCollecteur');
		include_spip('src/Texte/Collecteur/Multis');
		$collecteurMultis = new Spip\Texte\Collecteur\Multis();
		$quoi = $collecteurMultis->traiter($quoi, ['lang' => $lang, 'appliquer_typo' => false]);
	}
	return $quoi;
}

/**
 * Mapping de `RAND` pour SQLite
 *
 * @return float
 */
function _sqlite_func_rand() {
	return random_int(0, mt_getrandmax());
}

/**
 * Mapping de `RIGHT` pour SQLite
 *
 * @param string $s
 * @param int $length
 * @return string
 */
function _sqlite_func_right($s, $length) {
	return substr($s, 0 - $length);
}

/**
 * Mapping de `REGEXP` pour SQLite
 *
 * @param string $cherche
 * @param string $quoi
 * @return bool
 */
function _sqlite_func_regexp_match($cherche, $quoi) {
	// optimiser un cas tres courant avec les requetes en base
	if (!$quoi && !strlen($quoi)) {
		return $cherche === '^$' ? true : false;
	}
	// il faut enlever un niveau d'echappement pour être homogène à mysql
	$cherche = str_replace('\\\\', '\\', $cherche);
	$u = $GLOBALS['meta']['pcre_u'] ?? 'u';
	$return = preg_match('%' . $cherche . '%imsS' . $u, $quoi);

	# spip_log("regexp_replace : $quoi, $cherche, $remplace, $return",'sqlite.'._LOG_DEBUG);
	return $return;
}

/**
 * Mapping de `DATE_FORMAT` pour SQLite
 *
 * Transforme un un appel à DATE_FORMAT() via date() de PHP,
 * mais les chaines de format n'ont pas toujours un équivalent
 * On fait au mieux.
 *
 * @param string $date
 * @param string $conv
 * @return string
 */
function _sqlite_func_date_format($date, $conv) {
	$conv = _sqlite_func_date_format_converter($conv);
	return date($conv, is_int($date) ? $date : strtotime($date));
}

/**
 * Convertit un format demandé pour DATE_FORMAT() de mysql en un format
 * adapté à date() de php.
 *
 * Certains paramètres ne correspondent pas et doivent être remplacés,
 * d'autres n'ont tout simplement pas d'équivalent dans date() :
 * dans ce cas là on loggue, car il y a de grandes chances que le résultat
 * soit inadapté.
 */
function _sqlite_func_date_format_converter(string $conv): string {
	// ok : %a %b %d %e %H %I %l w%j %k %m %p %r %S %T %w %y %Y
	// on ne sait pas en gérer certains...
	static $mysql_to_php_date_not_ok = ['%j', '%U', '%u', '%V', '%X'];
	$mysql_to_php_date = [
		'%a' => 'D',     // 	Abbreviated weekday name (Sun to Sat)
		'%b' => 'M',   // 	Abbreviated month name (Jan to Dec)
		'%c' => 'n',   // 	Numeric month name (0 to 12)
		'%D' => 'jS',   // 	Day of the month as a numeric value, followed by suffix (1st, 2nd, 3rd, ...)
		'%d' => 'd',   // 	Day of the month as a numeric value (01 to 31)
		'%e' => 'j',   // 	Day of the month as a numeric value (0 to 31)
		'%f' => 'u',   // 	Microseconds (000000 to 999999)
		'%H' => 'H',   // 	Hour (00 to 23)
		'%h' => 'h',   // 	Hour (00 to 12)
		'%I' => 'h',   // 	Hour (00 to 12)
		'%i' => 'i',   // 	Minutes (00 to 59)
		'%j' => '??', // Approx 'z',   // 	Day of the year (SQL:001 to 366  | PHP:000 to 365)
		'%k' => 'G',   // 	Hour (0 to 23)
		'%l' => 'g',   // 	Hour (1 to 12)
		'%M' => 'F',   // 	Month name in full (January to December)
		'%m' => 'm',   // 	Month name as a numeric value (00 to 12)
		'%p' => 'A',   // 	AM or PM
		'%r' => 'h:i:s A',   // 	Time in 12 hour AM or PM format (hh:mm:ss AM/PM)
		'%S' => 's',   // 	Seconds (00 to 59)
		'%s' => 's',   // 	Seconds (00 to 59)
		'%T' => 'H:i:s',   // 	Time in 24 hour format (hh:mm:ss)
		'%U' => '??',   // 	Week where Sunday is the first day of the week (00 to 53)
		'%u' => '??', // Approx 'W',   // 	Week where Monday is the first day of the week (00 to 53)
		'%V' => '??',   // 	Week where Sunday is the first day of the week (01 to 53). Used with %X
		'%v' => 'W',   // 	Week where Monday is the first day of the week (01 to 53). Used with %x
		'%W' => 'l',   // 	Weekday name in full (Sunday to Saturday)
		'%w' => 'w',   // 	Day of the week where Sunday=0 and Saturday=6
		'%X' => '??',   // 	Year for the week where Sunday is the first day of the week. Used with %V
		'%x' => 'o',   // 	Year for the week where Monday is the first day of the week. Used with %v
		'%Y' => 'Y',   // 	Year as a numeric, 4-digit value
		'%y' => 'y',   // 	Year as a numeric, 2-digit value
	];
	static $to_php_date = [];
	if (!isset($to_php_date[$conv])) {
		$to_php_date[$conv] = str_replace(array_keys($mysql_to_php_date), $mysql_to_php_date, $conv);
		$count = 0;
		str_replace($mysql_to_php_date_not_ok, '', $conv, $count);
		if ($count > 0) {
			spip_log("DATE_FORMAT : At least one parameter can't be parsed by php date() with format '$conv' => '" . $to_php_date[$conv] . "'", 'sqlite.' . _LOG_ERREUR);
		}
	}
	return $to_php_date[$conv];
}

/**
 * Mapping de `DAYS` pour SQLite
 *
 * Nombre de jour entre 0000-00-00 et $d
 *
 * @see http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_to-days
 *
 * @param string $d
 * @return int
 */
function _sqlite_func_to_days($d) {
	static $offset = 719528; // nb de jour entre 0000-00-00 et timestamp 0=1970-01-01
	$result = $offset + (int) ceil(_sqlite_func_unix_timestamp($d) / (24 * 3600));

	# spip_log("Passage avec TO_DAYS : $d, $result",'sqlite.'._LOG_DEBUG);
	return $result;
}

/**
 * Mapping de `SUBSTRING` pour SQLite
 *
 * @param string $string
 * @param int $start
 * @param int $len
 * @return string
 */
function _sqlite_func_substring($string, $start, $len = null) {
	// SQL compte a partir de 1, php a partir de 0
	$start = ($start > 0) ? $start - 1 : $start;
	if ($len === null) {
		return substr($string, $start);
	} else {
		return substr($string, $start, $len);
	}
}

/**
 * Mapping de `TIMESTAMPDIFF` pour SQLite
 *
 * Calcul de la difference entre 2 timestamp, exprimes dans l'unite fournie en premier argument
 *
 * @see https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_timestampdiff
 *
 * @param string $unit
 * @param string $date1
 * @param string $date2
 * @return int
 */
function _sqlite_timestampdiff($unit, $date1, $date2) {
	$d1 = date_create($date1);
	$d2 = date_create($date2);
	$diff = date_diff($d1, $d2);
	$inv = $diff->invert ? -1 : 1;
	switch ($unit) {
		case 'YEAR':
			return $inv * $diff->y;
		case 'QUARTER':
			return $inv * (4 * $diff->y + intval(floor($diff->m / 3)));
		case 'MONTH':
			return $inv * (12 * $diff->y + $diff->m);
		case 'WEEK':
			return $inv * intval(floor($diff->days / 7));
		case 'DAY':
			# var_dump($inv*$diff->days);
			return $inv * $diff->days;
		case 'HOUR':
			return $inv * (24 * $diff->days + $diff->h);
		case 'MINUTE':
			return $inv * ((24 * $diff->days + $diff->h) * 60 + $diff->i);
		case 'SECOND':
			return $inv * (((24 * $diff->days + $diff->h) * 60 + $diff->i) * 60 + $diff->s);
		case 'MICROSECOND':
			return $inv * (((24 * $diff->days + $diff->h) * 60 + $diff->i) * 60 + $diff->s) * 1_000_000;
	}

	return 0;
}

/**
 * Mapping de `UNIX_TIMESTAMP` pour SQLite
 *
 * @param string $d
 * @return int
 */
function _sqlite_func_unix_timestamp($d) {
	static $mem = [];
	static $n = 0;
	if (isset($mem[$d])) {
		return $mem[$d];
	}
	if ($n++ > 100) {
		$mem = [];
		$n = 0;
	}

	// 2005-12-02 20:53:53
	# spip_log("Passage avec UNIX_TIMESTAMP : $d",'sqlite.'._LOG_DEBUG);
	if (!$d) {
		return $mem[$d] = time();
	}

	// une pile plus grosse n'accelere pas le calcul
	return $mem[$d] = strtotime($d);
}

/**
 * Mapping de `YEAR` pour SQLite
 *
 * @uses _sqlite_func_date()
 *
 * @param string $d
 * @return string
 */
function _sqlite_func_year($d) {
	return _sqlite_func_date('Y', $d);
}

/**
 * Version optimisée et memoizée de date() utilisé pour certains mapping SQLite
 *
 * @param string $quoi
 *   format : Y, m, ou d
 * @param int $d
 *   timestamp
 * @return string
 */
function _sqlite_func_date($quoi, $d) {
	static $mem = [];
	static $n = 0;
	if (isset($mem[$d])) {
		return $mem[$d][$quoi];
	}
	if ($n++ > 100) {
		$mem = [];
		$n = 0;
	}

	$dec = date('Y-m-d', _sqlite_func_unix_timestamp($d));
	$mem[$d] = ['Y' => substr($dec, 0, 4), 'm' => substr($dec, 5, 2), 'd' => substr($dec, 8, 2)];

	return $mem[$d][$quoi];
}

/**
 * Mapping de `VIDE()` de SPIP pour SQLite
 */
function _sqlite_func_vide() {

}
