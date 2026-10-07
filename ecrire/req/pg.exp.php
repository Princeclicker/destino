<?php

/* *************************************************************************\
 *  SPIP, Système de publication pour l'internet                           *
 *                                                                         *
 *  Copyright © avec tendresse depuis 2001                                 *
 *  Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribué sous licence GNU/GPL.     *
\*/

/**
 * Ce fichier contient les fonctions gerant
 * les instructions SQL pour PostgreSQL
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

define('_DEFAULT_DB', 'spip');

/**
 * Créer la première connexion à un serveur PG
 *
 * Se connecte et retourne le nom de la fonction a connexion persistante
 * A la premiere connexion de l'installation (BD pas precisee)
 * si on ne peut se connecter sans la preciser
 * on reessaye avec le login comme nom de BD
 * et si ca marche toujours pas, avec "spip" (constante ci-dessus)
 * si ca ne marche toujours pas, echec.
 *
 * @param int|string $port
 * 	Port de connexion
 * @param string $login
 * 	Nom d'utilisateur
 * @param string $pass
 * 	Mot de passe
 * @param string $db
 * 	Nom de la base
 * @param string $prefixe
 * 	Préfixe des tables SPIP
 *
 * @return array|bool
 *     - false si la connexion a échoué
 *     - tableau décrivant la connexion sinon
 */
function req_pg_dist(
	string $addr,
	$port,
	string $login,
	#[\SensitiveParameter]
	string $pass,
	string $db = '',
	string $prefixe = ''
) {
	static $last_connect = [];
	if (!extension_loaded('pgsql')) {
		return false;
	}

	// si provient de selectdb
	if (empty($addr) && empty($port) && empty($login) && empty($pass)) {
		foreach (['addr', 'port', 'login', 'pass', 'prefixe'] as $a) {
			${$a} = $last_connect[$a];
		}
	}
	[$host, $p] = array_pad(explode(';', $addr), 2, null);
	if ($p > 0) {
		$port = " port=$p";
	} else {
		$port = '';
	}
	$erreurs = [];
	if ($db) {
		@$link = pg_connect("host=$host$port dbname=$db user=$login password='$pass'", PGSQL_CONNECT_FORCE_NEW);
	} elseif (!@$link = pg_connect("host=$host$port user=$login password='$pass'", PGSQL_CONNECT_FORCE_NEW)) {
		$erreurs[] = pg_last_error();
		if (@$link = pg_connect("host=$host$port dbname=$login user=$login password='$pass'", PGSQL_CONNECT_FORCE_NEW)) {
			$db = $login;
		} else {
			$erreurs[] = pg_last_error();
			$db = _DEFAULT_DB;
			$link = pg_connect("host=$host$port dbname=$db user=$login password='$pass'", PGSQL_CONNECT_FORCE_NEW);
		}
	}
	if (!$link) {
		$erreurs[] = pg_last_error();
		foreach ($erreurs as $e) {
			spip_log('Echec pg_connect. Erreur : ' . $e, 'pg.' . _LOG_HS);
		}

		return false;
	}

	if ($link) {
		$last_connect = [
			'addr' => $addr,
			'port' => $port,
			'login' => $login,
			'pass' => $pass,
			'db' => $db,
			'prefixe' => $prefixe,
		];
	}

	spip_log(
		"Connexion vers $host, base $db, prefixe $prefixe " . ($link ? 'operationnelle' : 'impossible'),
		'pg.' . _LOG_DEBUG
	);

	return !$link ? false : [
		'db' => $db,
		'prefixe' => $prefixe ?: $db,
		'link' => $link,
	];
}

$GLOBALS['spip_pg_functions_1'] = [
	'alter' => 'spip_pg_alter',
	'count' => 'spip_pg_count',
	'countsel' => 'spip_pg_countsel',
	'create' => 'spip_pg_create',
	'create_base' => 'spip_pg_create_base',
	'create_view' => 'spip_pg_create_view',
	'date_proche' => 'spip_pg_date_proche',
	'delete' => 'spip_pg_delete',
	'drop_table' => 'spip_pg_drop_table',
	'drop_view' => 'spip_pg_drop_view',
	'errno' => 'spip_pg_errno',
	'error' => 'spip_pg_error',
	'explain' => 'spip_pg_explain',
	'fetch' => 'spip_pg_fetch',
	'seek' => 'spip_pg_seek',
	'free' => 'spip_pg_free',
	'hex' => 'spip_pg_hex',
	'in' => 'spip_pg_in',
	'insert' => 'spip_pg_insert',
	'insertq' => 'spip_pg_insertq',
	'insertq_multi' => 'spip_pg_insertq_multi',
	'listdbs' => 'spip_pg_listdbs',
	'multi' => 'spip_pg_multi',
	'optimize' => 'spip_pg_optimize',
	'query' => 'spip_pg_query',
	'quote' => 'spip_pg_quote',
	'replace' => 'spip_pg_replace',
	'replace_multi' => 'spip_pg_replace_multi',
	'repair' => 'spip_pg_repair',
	'select' => 'spip_pg_select',
	'selectdb' => 'spip_pg_selectdb',
	'set_charset' => 'spip_pg_set_charset',
	'get_charset' => 'spip_pg_get_charset',
	'showbase' => 'spip_pg_showbase',
	'showtable' => 'spip_pg_showtable',
	'table_exists' => 'spip_pg_table_exists',
	'update' => 'spip_pg_update',
	'updateq' => 'spip_pg_updateq',
];

/**
 * Exécuter une requête PG, munie d'une trace à la demande
 *
 * Par ou ca passe une fois les traductions faites
 *
 * @param string $query
 * 	Requête
 * @param string $serveur
 * 	Nom de la connexion
 *
 * @return PgSql\Result|bool|array
 *     - PgSql\Result|bool : Jeu de résultats ou false en cas d'erreur
 *     - array : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_trace_query(
	string $query,
	string $serveur = ''
) {
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$prefixe = $connexion['prefixe'];
	$link = $connexion['link'];
	$db = $connexion['db'];

	if (isset($_GET['var_profile'])) {
		include_spip('public/tracer');
		$t = trace_query_start();
		$e = '';
	} else {
		$t = 0;
	}

	$connexion['last'] = $query;
	$r = spip_pg_query_simple($link, $query);

	// Log de l'erreur eventuelle
	if ($e = spip_pg_errno($serveur)) {
		$e .= spip_pg_error($query, $serveur);
	} // et du fautif
	return $t ? trace_query_end($query, $t, $r, $e, $serveur) : $r;
}

/**
 * Exécuter une requête PG, munie d'une trace à la demande
 *
 * Fonction de requete generale quand on est sur que c'est SQL standard.
 * Elle change juste le noms des tables ($table_prefix) dans le FROM etc
 *
 * @param string $query
 * 	Requête
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return PgSql\Result|bool|string|array
 *     - PgSql\Result|bool : Si requête exécutée
 *     - string : texte de la requête si on ne l'exécute pas
 *     - array : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_query(
	string $query,
	string $serveur = '',
	bool $requeter = true
) {
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$prefixe = $connexion['prefixe'];
	$link = $connexion['link'];
	$db = $connexion['db'];

	if (preg_match('/\s(SET|VALUES|WHERE|DATABASE)\s/i', $query, $regs)) {
		$suite = strstr($query, (string) $regs[0]);
		$query = substr($query, 0, -strlen($suite));
	} else {
		$suite = '';
	}
	$query = preg_replace('/([,\s])spip_/', '\1' . $prefixe . '_', $query) . $suite;

	// renvoyer la requete inerte si demandee
	if (!$requeter) {
		return $query;
	}

	return spip_pg_trace_query($query, $serveur);
}

/**
 * Exécuter une requête SQL
 *
 * @param PgSql\Connection|resource $link
 * 	link d'une connexion
 * @param string $query
 * 	Requête
 *
 * @return PgSql\Result|bool
 * 	- Jeu de résultat pour fetch()
 * 	- false en cas d'erreur
 */
function spip_pg_query_simple(
	$link,
	string $query
) {
	# spip_log(var_export($query,true), 'pg.'._LOG_DEBUG);
	return pg_query($link, $query);
}

/**
 * Retrouver les champs 'timestamp'
 * pour les ajouter aux 'insert' ou 'replace'
 * afin de simuler le fonctionnement de mysql
 *
 * stocke le resultat pour ne pas faire
 * de requetes showtable intempestives
 *
 * @param string $table
 * 	Nom de la table
 * @param array $couples
 * 	Couples (colonne => valeur)
 * @param array $desc
 * 	Tableau de description de la table
 * 	(résultat de trouver_table)
 * 	@see base_trouver_table_dist()
 *
 * @return array
 * 	Couples avec la valeur mise à l'heure courante
 */
function spip_pg_ajouter_champs_timestamp(
	string $table,
	array $couples,
	array $desc = [],
	string $serveur = ''
) {
	static $tables = [];

	if (!isset($tables[$table])) {
		if (!$desc) {
			$trouver_table = charger_fonction('trouver_table', 'base');
			$desc = $trouver_table($table, $serveur);
			// si pas de description, on ne fait rien, ou on die() ?
			if (!$desc) {
				return $couples;
			}
		}

		// recherche des champs avec simplement 'TIMESTAMP'
		// cependant, il faudra peut etre etendre
		// avec la gestion de DEFAULT et ON UPDATE
		// mais ceux-ci ne sont pas utilises dans le core
		$tables[$table] = [];
		foreach ($desc['field'] as $k => $v) {
			$v = strtolower(ltrim($v));
			// ne pas ajouter de timestamp now() si un default est specifie
			if (str_starts_with($v, 'timestamp') && !str_contains($v, 'default')) {
				$tables[$table][] = $k;
			}
		}
	}

	// ajout des champs type 'timestamp' absents
	foreach ($tables[$table] as $maj) {
		if (!array_key_exists($maj, $couples)) {
			$couples[$maj] = 'NOW()';
		}
	}

	return $couples;
}

/**
 * Modifier une structure de table PG
 *
 * Alter en PG ne traite pas les index
 *
 * @param string $query
 * 	Requête SQL (sans 'ALTER ')
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - bool   : Si requête exécutée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_alter(
	string $query,
	string $serveur = '',
	bool $requeter = true
) {
	// il faudrait une regexp pour eviter de spliter ADD PRIMARY KEY (colA, colB)
	// tout en cassant en deux alter distincts "ADD PRIMARY KEY (colA, colB), ADD INDEX (chose)"...
	// ou revoir l'api de sql_alter en creant un
	// sql_alter_table($table,array($actions));
	if (!preg_match('/\s*((\s*IGNORE)?\s*TABLE\s*([^\s]*))\s*(.*)?/is', $query, $regs)) {
		spip_log("$query mal comprise", 'pg.' . _LOG_ERREUR);

		return false;
	}
	$debut = $regs[1];
	$table = $regs[3];
	$suite = $regs[4];
	$todo = explode(',', $suite);
	// on remet les morceaux dechires ensembles... que c'est laid !
	$todo2 = [];
	$i = 0;
	$ouverte = false;
	while ($do = array_shift($todo)) {
		$todo2[$i] = isset($todo2[$i]) ? $todo2[$i] . ',' . $do : $do;
		$o = (str_contains($do, '('));
		$f = (str_contains($do, ')'));
		if ($o && !$f) {
			$ouverte = true;
		} elseif ($f) {
			$ouverte = false;
		}
		if (!$ouverte) {
			$i++;
		}
	}
	$todo = $todo2;
	$query = $debut . ' ' . array_shift($todo);

	if (!preg_match('/^\s*(IGNORE\s*)?TABLE\s+(\w+)\s+(ADD|DROP|CHANGE|MODIFY|RENAME)\s*(.*)$/is', $query, $r)) {
		spip_log("$query incompris", 'pg.' . _LOG_ERREUR);
	} else {
		if ($r[1]) {
			spip_log("j'ignore IGNORE dans $query", 'pg.' . _LOG_AVERTISSEMENT);
		}
		$f = 'spip_pg_alter_' . strtolower($r[3]);
		if (function_exists($f)) {
			$f($r[2], $r[4], $serveur, $requeter);
		} else {
			spip_log("$query non prevu", 'pg.' . _LOG_ERREUR);
		}
	}
	// Alter a plusieurs args. Faudrait optimiser.
	if ($todo) {
		spip_pg_alter("TABLE $table " . join(',', $todo), $serveur, $requeter);
	}
}

/**
 * Modifier une structure de table PG pour les cas suivants
 * 	- changer le nom d'une colonne
 * 	- changer le type d'une colonne
 * 	- changer la valeur par défaut d'une colonne
 * 	- changer une contrainte de nullité d'une colonne
 *
 * @param string $table
 * 	Nom de la table
 * @param string $arg
 * 	Changement dans la syntaxe Mysql
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - bool   : Si requête exécutée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_alter_change(
	string $table,
	string $arg,
	string $serveur = '',
	bool $requeter = true
) {
	if (!preg_match('/^`?(\w+)`?\s+`?(\w+)`?\s+(.*?)\s*(DEFAULT .*?)?(NOT\s+NULL)?\s*(DEFAULT .*?)?$/i', $arg, $r)) {
		spip_log("alter change: $arg  incompris", 'pg.' . _LOG_ERREUR);
	} else {
		[, $old, $new, $type, $default, $null, $def2] = $r;
		$actions = ["ALTER $old TYPE " . mysql2pg_type($type)];
		if ($null) {
			$actions[] = "ALTER $old SET NOT NULL";
		} else {
			$actions[] = "ALTER $old DROP NOT NULL";
		}

		if ($d = ($default ?: $def2)) {
			$actions[] = "ALTER $old SET $d";
		} else {
			$actions[] = "ALTER $old DROP DEFAULT";
		}

		spip_pg_query("ALTER TABLE $table " . join(', ', $actions));

		if ($old != $new) {
			spip_pg_query("ALTER TABLE $table RENAME $old TO $new", $serveur);
		}
	}
}

/**
 * Modifier une structure de table PG pour les cas suivants
 * 	- ajouter une colonne
 * 	- ajouter une contrainte
 * 	- ajouter un index
 *
 * @param string $table
 * 	Nom de la table
 * @param string $arg
 * 	Changement dans la syntaxe Mysql
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - bool   : Si requête exécutée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_alter_add(
	string $table,
	string $arg,
	string $serveur = '',
	bool $requeter = true
) {
	$nom_index = null;
	if (!preg_match('/^(COLUMN|INDEX|KEY|PRIMARY\s+KEY|)\s*(.*)$/', $arg, $r)) {
		spip_log("alter add $arg  incompris", 'pg.' . _LOG_ERREUR);

		return null;
	}
	if (!$r[1] || $r[1] == 'COLUMN') {
		preg_match('/`?(\w+)`?(.*)/', $r[2], $m);
		if (preg_match('/^(.*)(BEFORE|AFTER|FIRST)(.*)$/is', $m[2], $n)) {
			$m[2] = $n[1];
		}

		return spip_pg_query("ALTER TABLE $table ADD " . $m[1] . ' ' . mysql2pg_type($m[2]), $serveur, $requeter);
	}
	if ($r[1][0] == 'P') {
		// la primary peut etre sur plusieurs champs
		$r[2] = trim(str_replace('`', '', $r[2]));
		$m = ($r[2][0] == '(') ? substr($r[2], 1, -1) : $r[2];

		return spip_pg_query(
			"ALTER TABLE $table ADD CONSTRAINT $table" . '_pkey PRIMARY KEY (' . $m . ')',
			$serveur,
			$requeter
		);
	} else {
		preg_match('/([^\s,]*)\s*(.*)?/', $r[2], $m);
		// peut etre "(colonne)" ou "nom_index (colonnes)"
		// bug potentiel si qqn met "(colonne, colonne)"
		//
		// nom_index (colonnes)
		if ($m[2]) {
			$colonnes = substr($m[2], 1, -1);
			$nom_index = $m[1];
		} else {
			// (colonne)
			if ($m[1][0] == '(') {
				$colonnes = substr($m[1], 1, -1);
				if (str_contains(',', $colonnes)) {
					spip_log('PG : Erreur, impossible de creer un index sur plusieurs colonnes'
						. " sans qu'il ait de nom ($table, ($colonnes))", 'pg.' . _LOG_ERREUR);
				} else {
					$nom_index = $colonnes;
				}
			} // nom_index
			else {
				$nom_index = $colonnes = $m[1];
			}
		}

		return spip_pg_create_index($nom_index, $table, $colonnes, $serveur, $requeter);
	}
}

/**
 * Modifier une structure de table PG pour les cas suivants
 * 	- supprimer une colonne
 * 	- supprimer une contrainte
 * 	- supprimer un index
 *
 * @param string $table
 * 	Nom de la table
 * @param string $arg
 * 	Changement dans la syntaxe Mysql
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - bool   : Si requête exécutée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_alter_drop(
	string $table,
	string $arg,
	string $serveur = '',
	bool $requeter = true
) {
	if (!preg_match('/^(COLUMN|INDEX|KEY|PRIMARY\s+KEY|)\s*`?(\w*)`?/', $arg, $r)) {
		spip_log("alter drop: $arg  incompris", 'pg.' . _LOG_ERREUR);
	} else {
		if (!$r[1] || $r[1] == 'COLUMN') {
			return spip_pg_query("ALTER TABLE $table DROP " . $r[2], $serveur);
		}
		if ($r[1][0] == 'P') {
			return spip_pg_query("ALTER TABLE $table DROP CONSTRAINT $table" . '_pkey', $serveur);
		} else {
			return spip_pg_query('DROP INDEX ' . $table . '_' . $r[2], $serveur);
		}
	}
}

/**
 * Modifier une structure de table PG dans les même cas
 * que spip_pg_alter_change()
 *
 * Pas de différence en PG entre CHANGE et MODIFY
 *
 * @param string $table
 * 	Nom de la table
 * @param string $arg
 * 	Changement dans la syntaxe Mysql
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - bool   : Si requête exécutée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_alter_modify(
	string $table,
	string $arg,
	string $serveur = '',
	bool $requeter = true
) {
	if (!preg_match('/^`?(\w+)`?\s+(.*)$/', $arg, $r)) {
		spip_log("alter modify: $arg  incompris", 'pg.' . _LOG_ERREUR);
	} else {
		return spip_pg_alter_change($table, $r[1] . ' ' . $arg, $serveur = '', $requeter = true);
	}
}

/**
 * Renommer un table PG
 *
 * attention (en pg) :
 * - alter table A rename to X = changer le nom de la table
 * - alter table A rename X to Y = changer le nom de la colonne X en Y
 * pour l'instant, traiter simplement RENAME TO X
 *
 * @param string $table
 * 	Nom de la table
 * @param string $arg
 * 	Changement dans la syntaxe Mysql
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - bool   : Si requête exécutée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_alter_rename(
	string $table,
	string $arg,
	string $serveur = '',
	bool $requeter = true
) {
	$rename = '';
	// si TO, mais pas au debut
	if (!stripos($arg, 'TO ')) {
		$rename = $arg;
	} elseif (preg_match('/^(TO)\s*`?(\w*)`?/', $arg, $r)) {
		$rename = $r[2];
	} else {
		spip_log("alter rename: $arg  incompris", 'pg.' . _LOG_ERREUR);
	}

	return $rename ? spip_pg_query("ALTER TABLE $table RENAME TO $rename") : false;
}

/**
 * Créer un INDEX pour une table PG
 *
 * @param string $nom
 * 	Nom de l'index
 * @param string $table
 * 	Table sql de l'index
 * @param string|array $champs
 * 	Liste des champs sur lesquels s'applique l'index
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - bool   : Si requête exécutée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_create_index(
	string $nom,
	string $table,
	$champs,
	string $serveur = '',
	bool $requeter = true
) {
	if (!($nom || $table || $champs)) {
		spip_log(
			"Champ manquant pour creer un index pg ($nom, $table, (" . @join(',', $champs) . '))',
			'pg.' . _LOG_ERREUR
		);

		return false;
	}

	$nom = str_replace('`', '', $nom);
	$champs = str_replace('`', '', $champs);

	// PG ne differentie pas noms des index en fonction des tables
	// il faut donc creer des noms uniques d'index pour une base pg
	$nom = $table . '_' . $nom;
	// enlever d'eventuelles parentheses deja presentes sur champs
	if (!is_array($champs)) {
		if ($champs[0] == '(') {
			$champs = substr($champs, 1, -1);
		}
		$champs = [$champs];
	}
	$query = "CREATE INDEX $nom ON $table (" . join(',', $champs) . ')';
	if (!$requeter) {
		return $query;
	}
	$res = spip_pg_query($query, $serveur, $requeter);

	return $res;
}

/**
 * Retourner une explication pour une requête SELECT (Explain) PG
 *
 * @param string $query
 * 	Texte de la requête
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|string
 *    - string : texte de la requête si on ne l'exécute pas
 * 	- array  : Tableau de l'explication si requête exécutée
 */
function spip_pg_explain(
	string $query,
	string $serveur = '',
	bool $requeter = true
) {
	if (!str_starts_with(ltrim($query), 'SELECT')) {
		return [];
	}
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$prefixe = $connexion['prefixe'];
	$link = $connexion['link'];
	if (preg_match('/\s(SET|VALUES|WHERE)\s/i', $query, $regs)) {
		$suite = strstr($query, (string) $regs[0]);
		$query = substr($query, 0, -strlen($suite));
	} else {
		$suite = '';
	}
	$query = 'EXPLAIN ' . preg_replace('/([,\s])spip_/', '\1' . $prefixe . '_', $query) . $suite;

	if (!$requeter) {
		return $query;
	}
	$r = spip_pg_query_simple($link, $query);

	return spip_pg_fetch($r, null, $serveur);
}

/**
 * Sélectionner une base de données
 *
 * @param string $db
 *     Nom de la base à utiliser
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Inutilisé
 *
 * @return bool|string
 *     - Nom de la base en cas de succès.
 *     - False en cas d'erreur.
 */
function spip_pg_selectdb(
	string $db,
	string $serveur = '',
	bool $requeter = true
) {
	// se connecter a la base indiquee
	// avec les identifiants connus
	$index = $serveur ? strtolower($serveur) : 0;

	if ($link = spip_connect_db('', '', '', '', $db, 'pg', '', '')) {
		if (($db == $link['db']) && $GLOBALS['connexions'][$index] = $link) {
			return $db;
		}
	}

	return false;
}

/**
 * Retourner les bases de données accessibles
 *
 * Retourne un tableau du nom de toutes les bases de données
 * accessibles avec les permissions de l'utilisateur SQL
 * de cette connexion.
 *
 * Attention on n'a pas toujours les droits !
 *
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Inutilisé
 *
 * @return array
 *     Liste de noms de bases de données
 */
function spip_pg_listdbs(
	string $serveur,
	bool $requeter = true
) {
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$link = $connexion['link'];
	$dbs = [];
	$res = spip_pg_query_simple($link, 'select * From pg_database');
	while ($row = pg_fetch_array($res, null, PGSQL_NUM)) {
		$dbs[] = reset($row);
	}

	return $dbs;
}

/**
 * Exécuter une requête de sélection avec PG
 *
 * Instance de sql_select (voir ses specs).
 *
 * @see sql_select()
 * @note
 *     Les `\n` et `\t` sont utiles au debusqueur.
 *
 * @param string|array $select
 * 	Champs sélectionnés
 * @param string|array $from
 * 	Tables sélectionnées
 * @param string|array $where
 * 	Contraintes
 * @param string|array $groupby
 * 	Regroupements
 * @param string|array $orderby
 * 	Tris
 * @param string $limit
 * 	Limites de résultats
 * @param string|array $having
 * 	Contraintes posts sélections
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|bool|resource|string
 *     - string : texte de la requête si on ne l'exécute pas
 *     - ressource si requête exécutée, ressource pour fetch()
 *     - false si la requête exécutée a ratée
 *     - array  : Tableau décrivant requête et temps d'exécution si var_profile actif pour tracer.
 */
function spip_pg_select(
	$select,
	$from,
	$where = '',
	$groupby = [],
	$orderby = '',
	$limit = '',
	$having = '',
	string $serveur = '',
	bool $requeter = true
) {

	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$prefixe = $connexion['prefixe'];
	$link = $connexion['link'];
	$db = $connexion['db'];

	$limit = preg_match('/^\s*(([0-9]+),)?\s*([0-9]+)\s*$/', $limit, $limatch);
	if ($limit) {
		$offset = $limatch[2];
		$count = $limatch[3];
	}

	$select = spip_pg_frommysql($select);

	// si pas de tri explicitement demande, le GROUP BY ne
	// contient que la clef primaire.
	// lui ajouter alors le champ de tri par defaut
	if (preg_match('/FIELD\(([a-z]+\.[a-z]+),/i', $orderby[0], $groupbyplus)) {
		$groupby[] = $groupbyplus[1];
	}

	$orderby = spip_pg_orderby($orderby, $select);

	if ($having) {
		if (is_array($having)) {
			$having = join("\n\tAND ", array_map('calculer_pg_where', $having));
		}
	}
	$from = spip_pg_from($from, $prefixe);
	$query = 'SELECT ' . $select
		. (!$from ? '' : "\nFROM $from")
		. (!$where ? '' : ("\nWHERE " . (!is_array($where) ? calculer_pg_where($where) : (join(
			"\n\tAND ",
			array_map('calculer_pg_where', $where)
		)))))
		. spip_pg_groupby($groupby, $from, $select)
		. (!$having ? '' : "\nHAVING $having")
		. ($orderby ? ("\nORDER BY $orderby") : '')
		. (!$limit ? '' : (" LIMIT $count" . (!$offset ? '' : " OFFSET $offset")));

	// renvoyer la requete inerte si demandee
	if ($requeter === false) {
		return $query;
	}

	$r = spip_pg_trace_query($query, $serveur);

	return $r ?: $query;

}

/**
 * Remplacer le préfixe des tables spip_
 * par celui du fichier de configuration de la base
 *
 * Le traitement des prefixes de table dans un Select se limite au FROM
 * car le reste de la requete utilise les alias (AS) systematiquement
 *
 * @param string|array $from
 * 	Nom de la table
 * @param string $prefixe
 * 	Préfixe provenant du fichier de configuration de la base
 *
 * @return string
 * 	Le nom de la table rectifié
 */
function spip_pg_from(
	$from,
	string $prefixe
) {
	if (is_array($from)) {
		$from = spip_pg_select_as($from);
	}

	return !$prefixe ? $from : preg_replace('/(\b)spip_/', '\1' . $prefixe . '_', $from);
}

/**
 * Préparer une clause ORDER BY
 *
 * Regroupe en texte les éléments si un tableau est donné
 *
 * @param string $select
 * 	Requête pour laquelle faire cet order by
 *
 * @return string
 * 	Texte du orderby préparé
 */
function spip_pg_orderby(
	$order,
	string $select
) {
	$res = [];
	$arg = (is_array($order) ? $order : preg_split('/\s*,\s*/', $order));

	foreach ($arg as $v) {
		if (preg_match('/(case\s+.*?else\s+0\s+end)\s*AS\s+' . $v . '/', $select, $m)) {
			$res[] = $m[1];
		} else {
			$res[] = $v;
		}
	}

	return spip_pg_frommysql(join(',', $res));
}

/**
 * Préparer une clause GROUP BY
 *
 * Conversion a l'arrach' des jointures MySQL en jointures PG
 * A refaire pour tirer parti des possibilites de PG et de MySQL5
 * et pour enlever les repetitions (sans incidence de perf, mais ca fait sale)
 *
 * @param string|array $groupby
 * 	Texte du groupby à préparer
 * @param string $from
 * 	Tables sélectionnées
 * @param string $select
 * 	Champs sélectionnés
 *
 * @return string
 * 	Texte du orderby préparé
 */
function spip_pg_groupby(
	$groupby,
	string $from,
	string $select
) {
	$join = strpos($from, ',');
	// ismplifier avant de decouper
	if (is_string($select)) { // fct SQL sur colonne et constante apostrophee ==> la colonne
		$select = preg_replace('/\w+\(\s*([^(),\']*),\s*\'[^\']*\'[^)]*\)/', '\\1', $select);
	}

	if ($join || $groupby) {
		$join = is_array($select) ? $select : explode(', ', $select);
	}
	if ($join) {
		// enlever les 0 as points, '', ...
		foreach ($join as $k => $v) {
			$v = str_replace('DISTINCT ', '', $v);
			// fct SQL sur colonne et constante apostrophee ==> la colonne
			$v = preg_replace('/\w+\(\s*([^(),\']*),\s*\'[^\']*\'[^)]*\)/', '\\1', $v);
			$v = preg_replace('/CAST\(\s*([^(),\' ]*\s+)as\s*\w+\)/', '\\1', $v);
			// resultat d'agregat ne sont pas a mettre dans le groupby
			$v = preg_replace('/(SUM|COUNT|MAX|MIN|UPPER)\([^)]+\)(\s*AS\s+\w+)\s*,?/i', '', $v);
			// idem sans AS (fetch numerique)
			$v = preg_replace('/(SUM|COUNT|MAX|MIN|UPPER)\([^)]+\)\s*,?/i', '', $v);
			// des AS simples : on garde le cote droit du AS
			$v = preg_replace('/^.*\sAS\s+(\w+)\s*$/i', '\\1', $v);
			// ne reste plus que les vrais colonnes, ou des constantes a virer
			if (preg_match(',^[\'"],', $v) || is_numeric($v)) {
				unset($join[$k]);
			} else {
				$join[$k] = trim($v);
			}
		}
		$join = array_diff($join, ['']);
		$join = implode(',', $join);
	}
	if (is_array($groupby)) {
		$groupby = join(',', $groupby);
	}
	if ($join) {
		$groupby = $groupby ? "$groupby, $join" : $join;
	}
	if (!$groupby) {
		return '';
	}

	$groupby = spip_pg_frommysql($groupby);
	// Ne pas mettre dans le Group-By des valeurs numeriques
	// issue de prepare_recherche
	$groupby = preg_replace('/^\s*\d+\s+AS\s+\w+\s*,?\s*/i', '', $groupby);
	$groupby = preg_replace('/,\s*\d+\s+AS\s+\w+\s*/i', '', $groupby);
	$groupby = preg_replace('/\s+AS\s+\w+\s*/i', '', $groupby);

	return "\nGROUP BY $groupby";
}

/**
 * Convertir une requête MySQL en PG
 *
 * Conversion des operateurs
 * IMPORTANT: "0+X" est vu comme conversion numerique du debut de X
 * Les expressions de date ne sont pas gerees au-dela de 3 ()
 * Le 'as' du 'CAST' est en minuscule pour echapper au dernier preg_replace
 * de spip_pg_groupby.
 * A ameliorer.
 *
 * @param array|string $arg
 * 	Liste des champ sélectionnés
 *
 * @return string
 * 	Listes des champs sélectionnés au format PG
 */
function spip_pg_frommysql($arg) {
	if (is_array($arg)) {
		$arg = join(', ', $arg);
	}

	$res = spip_pg_fromfield($arg);

	$res = preg_replace('/\brand[(][)]/i', 'random()', $res);

	$res = preg_replace(
		'/\b0\.0[+]([a-zA-Z0-9_.]+)\s*/',
		'CAST(substring(\1, \'^ *[0-9.]+\') as float)',
		$res
	);
	$res = preg_replace(
		'/\b0[+]([a-zA-Z0-9_.]+)\s*/',
		'CAST(substring(\1, \'^ *[0-9]+\') as int)',
		$res
	);
	$res = preg_replace(
		'/\bconv[(]([^,]*)[^)]*[)]/i',
		'CAST(substring(\1, \'^ *[0-9]+\') as int)',
		$res
	);

	$res = preg_replace(
		'/UNIX_TIMESTAMP\s*[(]\s*[)]/',
		' EXTRACT(epoch FROM NOW())',
		$res
	);

	// la fonction md5(integer) n'est pas connu en pg
	// il faut donc forcer les types en text (cas de md5(id_article))
	$res = preg_replace(
		'/md5\s*[(]([^)]*)[)]/i',
		'MD5(CAST(\1 AS text))',
		$res
	);

	$res = preg_replace(
		'/UNIX_TIMESTAMP\s*[(]([^)]*)[)]/',
		' EXTRACT(epoch FROM \1)',
		$res
	);

	$res = preg_replace(
		'/\bDAYOFMONTH\s*[(]([^()]*([(][^()]*[)][^()]*)*[^)]*)[)]/',
		' EXTRACT(day FROM \1)',
		$res
	);

	$res = preg_replace(
		'/\bMONTH\s*[(]([^()]*([(][^)]*[)][^()]*)*[^)]*)[)]/',
		' EXTRACT(month FROM \1)',
		$res
	);

	$res = preg_replace(
		'/\bYEAR\s*[(]([^()]*([(][^)]*[)][^()]*)*[^)]*)[)]/',
		' EXTRACT(year FROM \1)',
		$res
	);

	$res = preg_replace(
		'/TO_DAYS\s*[(]([^()]*([(][^)]*[)][()]*)*)[)]/',
		' EXTRACT(day FROM \1 - \'0001-01-01\')',
		$res
	);

	$res = preg_replace('/(EXTRACT[(][^ ]* FROM *)"([^"]*)"/', '\1\'\2\'', $res);

	$res = preg_replace('/DATE_FORMAT\s*[(]([^,]*),\s*\'%Y%m%d\'[)]/', 'to_char(\1, \'YYYYMMDD\')', $res);

	$res = preg_replace('/DATE_FORMAT\s*[(]([^,]*),\s*\'%Y%m\'[)]/', 'to_char(\1, \'YYYYMM\')', $res);

	$res = preg_replace('/DATE_SUB\s*[(]([^,]*),/', '(\1 -', $res);
	$res = preg_replace('/DATE_ADD\s*[(]([^,]*),/', '(\1 +', $res);
	$res = preg_replace('/INTERVAL\s+(\d+\s+\w+)/', 'INTERVAL \'\1\'', $res);
	$res = preg_replace('/([+<>-]=?)\s*(\'\d+-\d+-\d+\s+\d+:\d+(:\d+)\')/', '\1 timestamp \2', $res);
	$res = preg_replace('/(\'\d+-\d+-\d+\s+\d+:\d+:\d+\')\s*([+<>-]=?)/', 'timestamp \1 \2', $res);

	$res = preg_replace('/([+<>-]=?)\s*(\'\d+-\d+-\d+\')/', '\1 timestamp \2', $res);
	$res = preg_replace('/(\'\d+-\d+-\d+\')\s*([+<>-]=?)/', 'timestamp \1 \2', $res);

	$res = preg_replace('/(timestamp .\d+)-00-/', '\1-01-', $res);
	$res = preg_replace('/(timestamp .\d+-\d+)-00/', '\1-01', $res);
	# correct en theorie mais produit des debordements arithmetiques
	#	$res = preg_replace("/(EXTRACT[(][^ ]* FROM *)(timestamp *'[^']*' *[+-] *timestamp *'[^']*') *[)]/", '\2', $res);
	$res = preg_replace("/(EXTRACT[(][^ ]* FROM *)('[^']*')/", '\1 timestamp \2', $res);
	$res = preg_replace('/\sLIKE\s+/', ' ILIKE ', $res);

	return str_replace('REGEXP', '~', $res);
}

/**
 * Convertir la fonction FIELD() de Mysql
 * en un équivalent pour Postgresql
 *
 * FIELD():
 *   Renvoie l'index de « q » dans la liste de chaînes
 *
 * Mysql :
 *  FIELD(value, val1, val2, val3, ...)
 *
 * Posgresql :
 *   CASE
 *     WHEN value = val1 THEN 1
 *     WHEN value = val2 THEN 2
 *     WHEN value = val3 THEN 3
 *     ELSE 0
 *   END;
 *
 * @param string $arg
 * 	chaîne où effectuer le remplacement
 *
 * @return string
 * 	la chaîne avec le remplacement
 */
function spip_pg_fromfield(string $arg) {
	while (preg_match('/^(.*?)FIELD\s*\(([^,]*)((,[^,)]*)*)\)/', $arg, $m)) {
		preg_match_all('/,([^,]*)/', $m[3], $r, PREG_PATTERN_ORDER);
		$res = '';
		$n = 0;
		$index = $m[2];
		foreach ($r[1] as $v) {
			$n++;
			$res .= "\nwhen $index=$v then $n";
		}
		$arg = $m[1] . "case $res else 0 end "
			. substr($arg, strlen($m[0]));
	}

	return $arg;
}

/**
 * Préparer une clause WHERE pour PG
 *
 * Retourne une chaîne avec les bonnes parenthèses pour la
 * contrainte indiquée, au format donnée par le compilateur
 *
 * @param array|string $v
 *     Description des contraintes
 *     - string : texte du where
 *     - sinon tableau : A et B peuvent être de type string ou array,
 *       OP et C sont de type string :
 *       - array(A) : A est le texte du where
 *       - array(OP, A) : contrainte OP( A )
 *       - array(OP, A, B) : contrainte (A OP B)
 *       - array(OP, A, B, C) : contrainte (A OP (B) : C)
 *
 * @return string
 *     Contrainte pour clause WHERE
 */
function calculer_pg_where($v) {
	if (!is_array($v)) {
		return spip_pg_frommysql($v);
	}

	$op = str_replace('REGEXP', '~', array_shift($v));
	if (!($n = count($v))) {
		return $op;
	} else {
		$arg = calculer_pg_where(array_shift($v));
		if ($n == 1) {
			return "$op($arg)";
		} else {
			$arg2 = calculer_pg_where(array_shift($v));
			if ($n == 2) {
				return "($arg $op $arg2)";
			} else {
				return "($arg $op ($arg2) : $v[0])";
			}
		}
	}
}

/**
 * Calculer une expression pour une requête, en cumulant chaque élément
 * avec l'opérateur de liaison ($join) indiqué
 *
 * Renvoie grosso modo "$expression join($join, $v)"
 *
 * @param string $expression
 * 	Mot clé de l'expression, tel que "WHERE" ou "ORDER BY"
 * @param array|string $v
 * 	Données de l'expression
 * @param string $join
 * 	Si les données sont un tableau, elles seront groupées par cette jointure
 *
 * @return string
 * 	Texte de l'expression, une partie donc, du texte la requête.
 */
function calculer_pg_expression(
	string $expression,
	$v,
	string $join = 'AND'
) {
	if (empty($v)) {
		return '';
	}

	$exp = "\n$expression ";

	if (!is_array($v)) {
		$v = [$v];
	}

	if (strtoupper($join) === 'AND') {
		return $exp . join("\n\t$join ", array_map('calculer_pg_where', $v));
	} else {
		return $exp . join($join, $v);
	}
}

/**
 * Renvoyer des `nom AS alias`
 *
 * @param array $args
 * 	Liste des champs sélectionnés
 *
 * @return string
 * 	Sélection de colonnes pour une clause SELECT
 */
function spip_pg_select_as(array $args) {
	$argsas = '';
	foreach ($args as $k => $v) {
		if (str_ends_with($k, '@')) {
			// c'est une jointure qui se refere au from precedent
			// pas de virgule
			$argsas .= '  ' . $v;
		} else {
			$as = '';
			//  spip_log("$k : $v", _LOG_DEBUG);
			if (!is_numeric($k)) {
				if (preg_match('/\.(.*)$/', $k, $r)) {
					$v = $k;
				} elseif ($v != $k) {
					$p = strpos($v, ' ');
					if ($p) {
						$v = substr($v, 0, $p) . " AS $k" . substr($v, $p);
					} else {
						$as = " AS $k";
					}
				}
			}
			// spip_log("subs $k : $v avec $as", _LOG_DEBUG);
			// if (strpos($v, 'JOIN') === false)  $argsas .= ', ';
			$argsas .= ', ' . $v . $as;
		}
	}

	return substr($argsas, 2);
}

/**
 * Récupérer la ligne suivante d'une ressource de résultat
 *
 * @param PgSql\Result|resource $res
 * 	Jeu de résultats (issu de sql_select)
 * @param string $t
 * 	Inutilisé
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Inutilisé
 *
 * @return array|null|false
 *     - array Ligne de résultat
 *     - null Pas de résultat
 *     - false Erreur
 */
function spip_pg_fetch(
	$res,
	$t = '',
	string $serveur = '',
	bool $requeter = true
) {

	if ($res) {
		$res = pg_fetch_array($res, null, PGSQL_ASSOC);
	}

	return $res;
}

/**
 * Placer le pointeur de résultat sur la position indiquée
 *
 * @param PgSql\Result|resource $r
 * 	Jeu de résultats
 * @param int $row_number
 * 	Position. Déplacer le pointeur à cette ligne
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Inutilisé
 *
 * @return bool
 * 	True si déplacement réussi, false sinon.
 */
function spip_pg_seek(
	$r,
	int $row_number,
	string $serveur = '',
	bool $requeter = true
) {
	if ($r) {
		return pg_result_seek($r, $row_number);
	}
}

/**
 * Retourner le nombre de lignes d'une sélection
 *
 * @param array|string $from
 * 	Tables à consulter (From)
 * @param array|string $where
 * 	Conditions a remplir (Where)
 * @param array|string $groupby
 * 	Critère de regroupement (Group by)
 * @param array|string $having
 * 	Tableau des des post-conditions à remplir (Having)
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return int|string
 *     - string texte de la requête si demandé
 *     - int Nombre de lignes (0 si la requête n'a pas réussie)
 */
function spip_pg_countsel(
	$from = [],
	$where = [],
	$groupby = [],
	$having = [],
	string $serveur = '',
	bool $requeter = true
) {
	$c = !$groupby ? '*' : ('DISTINCT ' . (is_string($groupby) ? $groupby : join(',', $groupby)));
	$r = spip_pg_select("COUNT($c)", $from, $where, '', '', '', $having, $serveur, $requeter);
	if (!$requeter) {
		return $r;
	}
	if ((!$r instanceof \PgSql\Result) && (!is_resource($r))) {
		return 0;
	}
	[$c] = pg_fetch_array($r, null, PGSQL_NUM);
	pg_free_result($r);

	return $c;
}

/**
 * Retourner le nombre de lignes d’une ressource de sélection obtenue
 * avec `sql_select()`
 *
 * @param PgSql\Result|resource $res
 * 	Jeu de résultats
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Inutilisé
 *
 * @return int
 * 	Nombre de lignes
 */
function spip_pg_count(
	$res,
	string $serveur = '',
	bool $requeter = true
) {
	return !$res ? 0 : pg_num_rows($res);
}

/**
 * Libérer une ressource de résultat
 *
 * Indique à PG de libérer de sa mémoire la ressource de résultat indiquée
 * car on n'a plus besoin de l'utiliser.
 *
 * @param PgSql\Result|resource $res
 * 	Jeu de résultats
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Inutilisé
 *
 * @return bool
 * 	True si réussi
 */
function spip_pg_free(
	$res,
	string $serveur = '',
	bool $requeter = true
) {
	// rien a faire en postgres
}

/**
 * Supprimer des enregistrements d'une table
 *
 * @param string $table
 * 	Nom de la table SQL
 * @param string|array $where
 * 	Conditions à vérifier
 * @param string $serveur
 * 	Nom du connecteur
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return bool|string
 *     - int : nombre de suppressions réalisées,
 *     - texte de la requête si demandé,
 *     - false en cas d'erreur.
 */
function spip_pg_delete(
	string $table,
	$where = '',
	string $serveur = '',
	bool $requeter = true
) {

	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$table = prefixer_table_spip($table, $connexion['prefixe']);

	$query = calculer_pg_expression('DELETE FROM', $table, ',')
		. calculer_pg_expression('WHERE', $where, 'AND');

	// renvoyer la requete inerte si demandee
	if (!$requeter) {
		return $query;
	}

	$res = spip_pg_trace_query($query, $serveur);
	if ($res) {
		return pg_affected_rows($res);
	} else {
		return false;
	}
}

/**
 * Insérer une ligne dans une table
 *
 * @param string $table
 *     Nom de la table SQL
 * @param string $champs
 *     Liste des colonnes impactées,
 * @param string $valeurs
 *     Liste des valeurs,
 * @param array $desc
 *     Tableau de description des colonnes de la table SQL utilisée
 *     (il sera calculé si nécessaire s'il n'est pas transmis).
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Exécuter la requête, sinon la retourner
 *
 * @return bool|string|int|array
 *     - int|true identifiant de l'élément inséré (si possible), ou true, si réussite
 *     - texte de la requête si demandé,
 *     - false en cas d'erreur,
 *     - Tableau de description de la requête et du temps d'exécution, si var_profile activé
 */
function spip_pg_insert(
	string $table,
	string $champs,
	string $valeurs,
	array $desc = [],
	string $serveur = '',
	bool $requeter = true
) {
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$prefixe = $connexion['prefixe'];
	$link = $connexion['link'];

	if (!$desc) {
		$desc = description_table($table, $serveur);
	}
	$seq = spip_pg_sequence($table, true);
	// si pas de cle primaire dans l'insertion, renvoyer curval
	if (!preg_match(",\b$seq\b,", $champs)) {
		$seq = spip_pg_sequence($table);
		$seq = prefixer_table_spip($seq, $prefixe);
		$seq = "currval('$seq')";
	}

	$table = prefixer_table_spip($table, $prefixe);
	$ret = !$seq ? '' : (" RETURNING $seq");
	$ins = (strlen($champs) < 3)
		? ' DEFAULT VALUES'
		: "$champs VALUES $valeurs";
	$q = "INSERT INTO $table $ins $ret";
	if (!$requeter) {
		return $q;
	}
	$connexion['last'] = $q;
	$r = spip_pg_query_simple($link, $q);
	#	spip_log($q,'pg.'._LOG_DEBUG);
	if ($r) {
		if (!$ret) {
			return 0;
		}
		if ($r2 = pg_fetch_array($r, null, PGSQL_NUM)) {
			return $r2[0];
		}
	}

	return false;
}

/**
 * Insérer une ligne dans une table, en protégeant chaque valeur
 *
 * @param string $table
 *     Nom de la table SQL
 * @param array $couples
 *    Couples (colonne => valeur)
 * @param array $desc
 *     Tableau de description des colonnes de la table SQL utilisée
 *     (il sera calculé si nécessaire s'il n'est pas transmis).
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Exécuter la requête, sinon la retourner
 *
 * @return bool|string|int|array
 *     - int|true identifiant de l'élément inséré (si possible), ou true, si réussite
 *     - texte de la requête si demandé,
 *     - false en cas d'erreur,
 *     - Tableau de description de la requête et du temps d'exécution, si var_profile activé
 */
function spip_pg_insertq(
	string $table,
	array $couples = [],
	array $desc = [],
	string $serveur = '',
	bool $requeter = true
) {

	if (!$desc) {
		$desc = description_table($table, $serveur);
	}
	if (!$desc) {
		die("$table insertion sans description");
	}
	$fields = $desc['field'];

	foreach ($couples as $champ => $val) {
		$couples[$champ] = spip_pg_cite($val, $fields[$champ]);
	}

	// recherche de champs 'timestamp' pour mise a jour auto de ceux-ci
	$couples = spip_pg_ajouter_champs_timestamp($table, $couples, $desc, $serveur);

	return spip_pg_insert(
		$table,
		'(' . join(',', array_keys($couples)) . ')',
		'(' . join(',', $couples) . ')',
		$desc,
		$serveur,
		$requeter
	);
}

/**
 * Insérer plusieurs lignes d'un coup dans une table
 *
 * @param string $table
 *     Nom de la table SQL
 * @param array $tab_couples
 *     Tableau de tableaux associatifs (colonne => valeur)
 * @param array $desc
 *     Tableau de description des colonnes de la table SQL utilisée
 *     (il sera calculé si nécessaire s'il n'est pas transmis).
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Exécuter la requête, sinon la retourner
 *
 * @return int|bool|string
 *     - int|true identifiant du dernier élément inséré (si possible), ou true, si réussite
 *     - texte de la requête si demandé,
 *     - false en cas d'erreur.
 */
function spip_pg_insertq_multi(
	string $table,
	array $tab_couples = [],
	array $desc = [],
	string $serveur = '',
	bool $requeter = true
) {

	if (!$desc) {
		$desc = description_table($table, $serveur);
	}
	if (!$desc) {
		die("$table insertion sans description");
	}
	$fields = $desc['field'] ?? [];

	// recherche de champs 'timestamp' pour mise a jour auto de ceux-ci
	// une premiere fois pour ajouter maj dans les cles
	$c = $tab_couples[0] ?? [];
	$les_cles = spip_pg_ajouter_champs_timestamp($table, $c, $desc, $serveur);

	$cles = '(' . join(',', array_keys($les_cles)) . ')';
	$valeurs = [];
	foreach ($tab_couples as $couples) {
		foreach ($couples as $champ => $val) {
			$couples[$champ] = spip_pg_cite($val, $fields[$champ]);
		}
		// recherche de champs 'timestamp' pour mise a jour auto de ceux-ci
		$couples = spip_pg_ajouter_champs_timestamp($table, $couples, $desc, $serveur);

		$valeurs[] = '(' . join(',', $couples) . ')';
	}
	$valeurs = implode(', ', $valeurs);

	return spip_pg_insert($table, $cles, $valeurs, $desc, $serveur, $requeter);
}

/**
 * Mettre à jour des enregistrements d'une table SQL
 *
 * @param string $table
 *     Nom de la table
 * @param array $couples
 *     Couples (colonne => valeur)
 * @param string|array $where
 *     Conditions a remplir (Where)
 * @param array $desc
 *     Tableau de description des colonnes de la table SQL utilisée
 *     (il sera calculé si nécessaire s'il n'est pas transmis).
 * @param string $serveur
 *     Nom de la connexion
 * @param bool $requeter
 *     Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si demandé
 *     - true si la requête a réussie, false sinon
 *     - array Tableau décrivant la requête et son temps d'exécution si var_profile est actif
 */
function spip_pg_update(
	string $table,
	array $couples,
	$where = '',
	array $desc = [],
	string $serveur = '',
	bool $requeter = true
) {

	if (!$couples) {
		return;
	}
	$connexion = $GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$table = prefixer_table_spip($table, $connexion['prefixe']);

	// recherche de champs 'timestamp' pour mise a jour auto de ceux-ci
	$couples = spip_pg_ajouter_champs_timestamp($table, $couples, $desc, $serveur);

	$set = [];
	foreach ($couples as $champ => $val) {
		$set[] = $champ . '=' . $val;
	}

	$query = calculer_pg_expression('UPDATE', $table, ',')
		. calculer_pg_expression('SET', $set, ',')
		. calculer_pg_expression('WHERE', $where, 'AND');

	// renvoyer la requete inerte si demandee
	if (!$requeter) {
		return $query;
	}

	return spip_pg_trace_query($query, $serveur);
}

/**
 * Mettre à jour des enregistrements d'une table SQL et protège chaque valeur
 *
 * Protège chaque valeur transmise avec sql_quote(), adapté au type
 * de champ attendu par la table SQL
 *
 * @param string $table
 *     Nom de la table
 * @param array $couples
 *     Couples (colonne => valeur)
 * @param string|array $where
 *     Conditions a remplir (Where)
 * @param array $desc
 *     Tableau de description des colonnes de la table SQL utilisée
 *     (il sera calculé si nécessaire s'il n'est pas transmis).
 * @param string $serveur
 *     Nom de la connexion
 * @param bool $requeter
 *     Exécuter la requête, sinon la retourner
 *
 * @return array|bool|string
 *     - string : texte de la requête si demandé
 *     - true si la requête a réussie, false sinon
 *     - array Tableau décrivant la requête et son temps d'exécution si var_profile est actif
 */
function spip_pg_updateq(
	string $table,
	array $couples,
	$where = '',
	array $desc = [],
	string $serveur = '',
	bool $requeter = true
) {
	if (!$couples) {
		return;
	}
	if (!$desc) {
		$desc = description_table($table, $serveur);
	}
	$fields = $desc['field'];
	foreach ($couples as $k => $val) {
		$couples[$k] = spip_pg_cite($val, $fields[$k]);
	}

	return spip_pg_update($table, $couples, $where, $desc, $serveur, $requeter);
}

/**
 * Insérer ou mettre à jour une entrée d’une table SQL
 *
 * La clé ou les cles primaires doivent être présentes dans les données insérées.
 * La fonction effectue une protection automatique des données.
 *
 * Préférez updateq ou insertq.
 *
 * @param string $table
 *     Nom de la table SQL
 * @param array $values
 *     Couples colonne / valeur à modifier,
 * @param array $desc
 *     Tableau de description des colonnes de la table SQL utilisée
 *     (il sera calculé si nécessaire s'il n'est pas transmis).
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Exécuter la requête, sinon la retourner
 *
 * @return bool|string
 *     - true si réussite
 *     - texte de la requête si demandé,
 *     - false en cas d'erreur.
 */
function spip_pg_replace(
	string $table,
	array $values,
	array $desc,
	string $serveur = '',
	bool $requeter = true
) {
	if (!$values) {
		spip_log("replace vide $table", 'pg.' . _LOG_AVERTISSEMENT);

		return 0;
	}
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$prefixe = $connexion['prefixe'];
	$link = $connexion['link'];

	if (!$desc) {
		$desc = description_table($table, $serveur);
	}
	if (!$desc) {
		die("$table insertion sans description");
	}
	$prim = $desc['key']['PRIMARY KEY'];
	$ids = preg_split('/,\s*/', $prim);
	$noprims = $prims = [];
	foreach ($values as $k => $v) {
		$values[$k] = $v = spip_pg_cite($v, $desc['field'][$k]);

		if (!in_array($k, $ids)) {
			$noprims[$k] = "$k=$v";
		} else {
			$prims[$k] = "$k=$v";
		}
	}

	// recherche de champs 'timestamp' pour mise a jour auto de ceux-ci
	$values = spip_pg_ajouter_champs_timestamp($table, $values, $desc, $serveur);

	$where = join(' AND ', $prims);
	if (!$where) {
		return spip_pg_insert(
			$table,
			'(' . join(',', array_keys($values)) . ')',
			'(' . join(',', $values) . ')',
			$desc,
			$serveur
		);
	}
	$couples = join(',', $noprims);

	$seq = spip_pg_sequence($table);
	$table = prefixer_table_spip($table, $prefixe);
	$seq = prefixer_table_spip($seq, $prefixe);

	$connexion['last'] = $q = "UPDATE $table SET $couples WHERE $where";
	if ($couples) {
		$couples = spip_pg_query_simple($link, $q);
		#	  spip_log($q,'pg.'._LOG_DEBUG);
		if (!$couples) {
			return false;
		}
		$couples = pg_affected_rows($couples);
	}
	if (!$couples) {
		$ret = !$seq ? '' :
			(" RETURNING nextval('$seq') < $prim");
		$connexion['last'] = $q = "INSERT INTO $table (" . join(',', array_keys($values)) . ') VALUES (' . join(
			',',
			$values
		) . ")$ret";
		$couples = spip_pg_query_simple($link, $q);
		if (!$couples) {
			return false;
		}
		if ($ret) {
			$r = pg_fetch_array($couples, null, PGSQL_NUM);
			if ($r[0]) {
				$connexion['last'] = $q = "SELECT setval('$seq', $prim) from $table";
				// Le code de SPIP met parfois la sequence a 0 (dans l'import)
				// MySQL n'en dit rien, on fait pareil pour PG
				$r = @pg_query($link, $q);
			}
		}
	}

	return $couples;
}

/**
 * Insérer ou mettre à jour des entrées d’une table SQL
 *
 * La clé ou les cles primaires doivent être présentes dans les données insérés.
 * La fonction effectue une protection automatique des données.
 *
 * Préférez insertq_multi et sql_updateq
 *
 * @param string $table
 *     Nom de la table SQL
 * @param array $tab_couples
 *     Tableau de tableau (colonne / valeur à modifier),
 * @param array $desc
 *     Tableau de description des colonnes de la table SQL utilisée
 *     (il sera calculé si nécessaire s'il n'est pas transmis).
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Exécuter la requête, sinon la retourner
 *
 * @return bool|string
 *     - true si réussite
 *     - texte de la requête si demandé,
 *     - false en cas d'erreur.
 */
function spip_pg_replace_multi(
	string $table,
	array $tab_couples,
	array $desc = [],
	string $serveur = '',
	bool $requeter = true
) {
	$retour = null;
	// boucler pour traiter chaque requete independemment
	foreach ($tab_couples as $couples) {
		$retour = spip_pg_replace($table, $couples, $desc, $serveur, $requeter);
	}

	// renvoie le dernier id
	return $retour;
}

/**
 * Retourner le nom de la sequence eventuelle
 * associee à la clé primaire d'une table
 *
 * Pas extensible pour le moment,
 *
 * @param string $table
 * 	Nom de la table SQL
 * @param bool $raw
 * 	Indique si on retourne le nom de la clé primaire
 * 	ou le nom de la séquence
 *
 * @return string
 * 	- nom de la séquence ($raw = false)
 * 	- nom de la clé primaire ($raw = true)
 */
function spip_pg_sequence(
	string $table,
	bool $raw = false
) {

	include_spip('base/serial');
	if (!isset($GLOBALS['tables_principales'][$table])) {
		return false;
	}
	$desc = $GLOBALS['tables_principales'][$table];
	$prim = @$desc['key']['PRIMARY KEY'];
	if (
		!preg_match('/^\w+$/', $prim) || !str_contains($desc['field'][$prim], 'int')
	) {
		return '';
	} else {
		return $raw ? $prim : $table . '_' . $prim . '_seq';
	}
}

/**
 * Echapper les valeurs
 *
 * Explicite les conversions de Mysql d'une valeur $v de type $t
 * Dans le cas d'un champ date, pas d'apostrophe, c'est une syntaxe ad hoc
 *
 * @param mixed $v
 * 	Valeur
 * @param string $t
 * 	Type
 *
 * @return mixed
 */
function spip_pg_cite($v, string $t) {
	// null php se traduit en NULL SQL
	if ($v === null) {
		return 'NULL';
	}

	if (sql_test_date($t)) {
		if ($v === 'NOW()') {
			return $v;
		}

		if (str_starts_with($v, '0000')) {
			$v = '0001' . substr($v, 4);
		}
		if (strpos($v, '-00-00') === 4) {
			$v = substr($v, 0, 4) . '-01-01' . substr($v, 10);
		}

		return sprintf("timestamp '%s'", pg_escape_string($v));
	}

	if (!sql_test_int($t)) {
		return "'" . pg_escape_string($v) . "'";
	}
	if (is_numeric($v) || str_starts_with($v, 'CAST(')) {
		return $v;
	}
	if ($v[0] == '0' && $v[1] !== 'x' && ctype_xdigit(substr($v, 1))) {
		return substr($v, 1);
	}

	spip_log("Warning: '$v'  n'est pas de type $t", 'pg.' . _LOG_AVERTISSEMENT);

	return intval($v);
}

/**
 * Convertir une chaîne hexadécimale en entier
 *
 * Par exemple : FF ==> 255
 *
 * @param string $v
 *     Chaine hexadecimale
 *
 * @return string
 *     Valeur hexadécimale pour Postgresql
 */
function spip_pg_hex(string $v) {
	return "CAST(x'" . $v . "' as bigint)";
}

/**
 * Échapper une valeur selon son type ou au mieux
 * comme le fait `_q()`
 *
 * @param string|array|number $v
 *     texte, nombre ou tableau à échapper
 * @param string $type
 *     Description du type attendu
 *    (par exemple description SQL de la colonne recevant la donnée)
 *
 * @return string|number
 *    Donnée prête à être utilisée par le gestionnaire SQL
 */
function spip_pg_quote($v, string $type = '') {
	if (!is_array($v)) {
		return spip_pg_cite($v, $type);
	}
	// si c'est un tableau, le parcourir en propageant le type
	foreach ($v as $k => $r) {
		$v[$k] = spip_pg_quote($r, $type);
	}

	return join(',', $v);
}

/**
 * Tester si une date est proche de la valeur d'un champ
 *
 * @param string $champ
 *     Nom du champ a tester
 * @param int $interval
 *     Valeur de l'intervalle : -1, 4, ...
 * @param string $unite
 *     Utité utilisée (DAY, MONTH, YEAR, ...)
 *
 * @return string
 *     Expression SQL
 */
function spip_pg_date_proche(
	string $champ,
	int $interval,
	string $unite
) {
	return '('
	. $champ
	. (($interval <= 0) ? '>' : '<')
	. (($interval <= 0) ? 'DATE_SUB' : 'DATE_ADD')
	. '('
	. sql_quote(date('Y-m-d H:i:s'))
	. ', INTERVAL '
	. (($interval > 0) ? $interval : (0 - $interval))
	. ' '
	. $unite
	. '))';
}

/**
 * Retourner une expression IN pour le gestionnaire de base de données
 *
 * IN (...) est limité à 255 éléments, d'où cette fonction assistante
 *
 * @param string $val
 *     Colonne SQL sur laquelle appliquer le test
 * @param string $valeurs
 *     Liste des valeurs possibles (séparés par des virgules)
 * @param string $not
 *     - '' sélectionne les éléments correspondant aux valeurs
 *     - 'NOT' inverse en sélectionnant les éléments ne correspondant pas aux valeurs
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Inutilisé
 *
 * @return string
 *     Expression de requête SQL
 */
function spip_pg_in(
	string $val,
	string $valeurs,
	string $not = '',
	string $serveur = '',
	bool $requeter = true
) {

	// s'il n'y a pas de valeur, eviter de produire un IN vide: PG rale.
	if (!$valeurs) {
		return $not ? '0=0' : '0=1';
	}
	if (str_contains($valeurs, "CAST(x'")) {
		return "($val=" . join("OR $val=", explode(',', $valeurs)) . ')';
	}
	$n = $i = 0;
	$in_sql = '';
	while ($n = strpos($valeurs, ',', $n + 1)) {
		if ((++$i) >= 255) {
			$in_sql .= "($val $not IN (" .
				substr($valeurs, 0, $n) .
				"))\n" .
				($not ? "AND\t" : "OR\t");
			$valeurs = substr($valeurs, $n + 1);
			$i = $n = 0;
		}
	}
	$in_sql .= "($val $not IN ($valeurs))";

	return "($in_sql)";
}

/**
 * Retourner la dernière erreur generée
 *
 * @note
 *   Bien spécifier le serveur auquel on s'adresse,
 *   mais à l'install la globale n'est pas encore complètement définie.
 *
 * @param string $query
 *     Requête qui était exécutée
 * @param string $serveur
 *     Nom de la connexion
 * @param bool $requeter
 *     Inutilisé
 *
 * @return string
 *     Erreur eventuelle
 */
function spip_pg_error(
	string $query = '',
	string $serveur = '',
	bool $requeter = true
) {
	$link = $GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0]['link'];
	$s = $link ? pg_last_error($link) : pg_last_error();
	if ($s) {
		$s = str_replace('ERROR', 'errcode: 1000 ', $s);
		spip_log("$s - $query", 'pg.' . _LOG_ERREUR);
	}

	return $s;
}

/**
 * Retourner le numero de la dernière erreur SQL
 *
 * @param string $serveur
 *     Nom de la connexion
 * @param bool $requeter
 *     Inutilisé
 *
 * @return int
 *     - 0 : pas d'erreur
 * 	 - Autre : numéro de l'erreur
 */
function spip_pg_errno(
	string $serveur = '',
	bool $requeter = true
) {
	// il faudrait avoir la derniere ressource retournee et utiliser
	// http://fr2.php.net/manual/fr/function.pg-result-error.php
	return 0;
}

/**
 * Supprimer une table SQL
 *
 * @param string $table
 * 	Nom de la table SQL
 * @param string $exist
 * 	True pour ajouter un test d'existence avant de supprimer
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return bool|string
 *     - string texte de la requête si demandé
 *     - true si la requête a réussie, false sinon
 */
function spip_pg_drop_table(
	string $table,
	string $exist = '',
	string $serveur = '',
	bool $requeter = true
) {
	if ($exist) {
		$exist = ' IF EXISTS';
	}
	if (spip_pg_query("DROP TABLE$exist $table", $serveur, $requeter)) {
		return true;
	} else {
		return false;
	}
}

/**
 * Supprimer une vue SQL
 *
 * @param string $view
 * 	Nom de la vue SQL
 * @param string $exist
 * 	True pour ajouter un test d'existence avant de supprimer
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return bool|string
 *     - string texte de la requête si demandé
 *     - true si la requête a réussie, false sinon
 */
function spip_pg_drop_view(
	string $view,
	string $exist = '',
	string $serveur = '',
	bool $requeter = true
) {
	if ($exist) {
		$exist = ' IF EXISTS';
	}

	return spip_pg_query("DROP VIEW$exist $view", $serveur, $requeter);
}

/**
 * Retourner une ressource de la liste des tables de la base de données
 *
 * @param string $match
 *     Filtre sur tables à récupérer
 * @param string $serveur
 *     Connecteur de la base
 * @param bool $requeter
 *     true pour éxecuter la requête
 *     false pour retourner le texte de la requête.
 *
 * @return ressource
 *     Ressource à utiliser avec sql_fetch()
 */
function spip_pg_showbase(
	string $match,
	string $serveur = '',
	bool $requeter = true
) {
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$link = $connexion['link'];
	$connexion['last'] = $q = 'SELECT table_name FROM information_schema.tables WHERE table_name ILIKE ' . _q($match);

	return spip_pg_query_simple($link, $q);
}

/**
 * Obtienir la description d'une table ou vue
 *
 * Récupère la définition d'une table ou d'une vue avec colonnes, indexes, etc.
 * au même format que la définition des tables SPIP, c'est à dire
 * un tableau avec les clés
 *
 * - `field` (tableau colonne => description SQL) et
 * - `key` (tableau type de clé => colonnes)
 *
 * @param string $nom_table
 * 	Nom de la table SQL
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return array|string
 *     - chaîne vide si pas de description obtenue
 *     - string texte de la requête si demandé
 *     - array description de la table sinon
 */
function spip_pg_showtable(
	string $nom_table,
	string $serveur = '',
	bool $requeter = true
) {
	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$link = $connexion['link'];
	$connexion['last'] = $q = 'SELECT column_name, column_default, data_type FROM information_schema.columns WHERE table_name ILIKE ' . _q($nom_table);

	$res = spip_pg_query_simple($link, $q);
	if (!$res) {
		return '';
	}

	// etrangement, $res peut ne rien contenir, mais arriver ici...
	// il faut en tenir compte dans le return
	$fields = [];
	while ($field = pg_fetch_array($res, null, PGSQL_NUM)) {
		$fields[$field[0]] = $field[2] . (!$field[1] ? '' : (' DEFAULT ' . $field[1]));
	}
	$connexion['last'] = $q = 'SELECT indexdef FROM pg_indexes WHERE tablename ILIKE ' . _q($nom_table);
	$res = spip_pg_query_simple($link, $q);
	$keys = [];
	while ($index = pg_fetch_array($res, null, PGSQL_NUM)) {
		if (preg_match('/CREATE\s+(UNIQUE\s+)?INDEX\s([^\s]+).*\((.*)\)$/', $index[0], $r)) {
			$nom = str_replace($nom_table . '_', '', $r[2]);
			$keys[($r[1] ? 'PRIMARY KEY' : ('KEY ' . $nom))] = $r[3];
		}
	}

	return count($fields) ? ['field' => $fields, 'key' => $keys] : '';
}

/**
 * Indiquer si une table existe dans la base de données
 *
 * @param string $table
 *     Table dont on cherche l’existence
 * @param string $serveur
 *     Connecteur de la base
 * @param bool $requeter
 *     true pour éxecuter la requête
 *     false pour retourner le texte de la requête.
 *
 * @return bool|string
 *     - true si la table existe, false sinon
 *     - string : requete sql, si $requeter = true
 */
function spip_pg_table_exists(
	string $table,
	string $serveur = '',
	bool $requeter = true
) {
	$r = spip_pg_query(
		'SELECT * FROM  information_schema.tables' .
	' WHERE table_name ILIKE ' . _q($table),
		$serveur,
		$requeter
	);
	if (!$requeter) {
		return $r;
	}
	$res = spip_pg_fetch($r);
	return (bool) $res;
}

/**
 * Créer une table SQL nommee `$nom` à partir des 2 tableaux `$champs` et `$cles`
 *
 * @note Le nom des caches doit être inferieur à 64 caractères
 *
 * @param string $nom
 * 	Nom de la table SQL
 * @param array $champs
 * 	Couples (champ => description SQL)
 * @param array $cles
 * 	Couples (type de clé => champ(s) de la clé)
 * @param bool $autoinc
 * 	True pour ajouter un auto-incrément sur la Primary Key
 * @param bool $temporary
 * 	True pour créer une table temporaire
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	inutilisé
 *
 * @return array|null|resource|string
 *     - null si champs ou cles n'est pas un tableau
 *     - true si la requête réussie, false sinon.
 */
function spip_pg_create(
	string $nom,
	array $champs,
	array $cles,
	bool $autoinc = false,
	bool $temporary = false,
	string $serveur = '',
	bool $requeter = true
) {

	$connexion = $GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$link = $connexion['link'];
	$nom = prefixer_table_spip($nom, $connexion['prefixe']);

	$query = $prim = $prim_name = $v = $s = $p = '';
	$keys = [];

	// certains plugins declarent les tables  (permet leur inclusion dans le dump)
	// sans les renseigner (laisse le compilo recuperer la description)
	if (!is_array($champs) || !is_array($cles)) {
		return;
	}

	// Le nom des index est prefixe par celui de la table pour eviter les conflits
	foreach ($cles as $k => $v) {
		if (str_starts_with($k, 'KEY ')) {
			$n = str_replace('`', '', $k);
			$v = str_replace('`', '"', $v);
			$i = $nom . preg_replace('/KEY +/', '_', $n);
			if ($k != $n) {
				$i = "\"$i\"";
			}
			$keys[] = "CREATE INDEX $i ON $nom ($v);";
		} elseif (str_starts_with($k, 'UNIQUE ')) {
			$k = preg_replace('/^UNIQUE +/', '', $k);
			$prim .= "$s\n\t\tCONSTRAINT " . str_replace('`', '"', $k) . " UNIQUE ($v)";
		} else {
			$prim .= "$s\n\t\t" . str_replace('`', '"', $k) . " ($v)";
		}
		if ($k == 'PRIMARY KEY') {
			$prim_name = $v;
		}
		$s = ',';
	}
	$s = '';

	$character_set = '';
	if (@$GLOBALS['meta']['charset_sql_base']) {
		$character_set .= ' CHARACTER SET ' . $GLOBALS['meta']['charset_sql_base'];
	}
	if (@$GLOBALS['meta']['charset_collation_sql_base']) {
		$character_set .= ' COLLATE ' . $GLOBALS['meta']['charset_collation_sql_base'];
	}

	foreach ($champs as $k => $v) {
		$k = str_replace('`', '"', $k);
		if (preg_match(',([a-z]*\s*(\(\s*[0-9]*\s*\))?(\s*binary)?),i', $v, $defs)) {
			if (preg_match(',(char|text),i', $defs[1]) && !preg_match(',binary,i', $defs[1])) {
				$v = $defs[1] . $character_set . ' ' . substr($v, strlen($defs[1]));
			}
		}

		$query .= "$s\n\t\t$k "
			. (
				($autoinc && ($prim_name == $k) && preg_match(',\b(big|small|medium|tiny)?int\b,i', $v))
				? ' bigserial'
				: mysql2pg_type($v)
			);
		$s = ',';
	}
	$temporary = $temporary ? 'TEMPORARY' : '';

	// En l'absence de "if not exists" en PG, on neutralise les erreurs

	$q = "CREATE $temporary TABLE $nom ($query" . ($prim ? ",$prim" : '') . ')' .
		($character_set ? " DEFAULT $character_set" : '')
		. "\n";

	if (!$requeter) {
		return $q;
	}
	$connexion['last'] = $q;
	$r = @pg_query($link, $q);

	if (!$r) {
		spip_log("Impossible de creer cette table: $q", 'pg.' . _LOG_ERREUR);
	} else {
		foreach ($keys as $index) {
			pg_query($link, $index);
		}
	}

	return $r;
}

/**
 * Créer une base de données
 *
 * @param string $nom
 * 	Nom de la base
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return bool true si la base est créee.
 */
function spip_pg_create_base(
	string $nom,
	string $serveur = '',
	bool $requeter = true
) {
	return spip_pg_query("CREATE DATABASE $nom", $serveur, $requeter);
}

/**
 * Créer une vue SQL nommée `$nom`
 *
 * @param string $nom
 *    Nom de la vue à creer
 * @param string $query_select
 *     texte de la requête de sélection servant de base à la vue
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $requeter
 *     Effectuer la requete, sinon la retourner
 *
 * @return bool|string
 *     - true si la vue est créée
 *     - false si erreur ou si la vue existe déja
 *     - string texte de la requête si $requeter vaut false
 */
function spip_pg_create_view(
	string $nom,
	string $query_select,
	string $serveur = '',
	bool $requeter = true
) {
	if (!$query_select) {
		return false;
	}
	// vue deja presente
	if (sql_showtable($nom, false, $serveur)) {
		if ($requeter) {
			spip_log("Echec creation d'une vue sql ($nom) car celle-ci existe deja (serveur:$serveur)", 'pg.' . _LOG_ERREUR);
		}

		return false;
	}

	$query = "CREATE VIEW $nom AS " . $query_select;

	return spip_pg_query($query, $serveur, $requeter);
}

/**
 * Définir un charset pour la connexion avec Postgresql
 *
 * @param string $charset
 * 	Charset à appliquer
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	inutilisé
 *
 * @return PgSql\Result|bool
 * 	Jeu de résultats pour fetch()
 */
function spip_pg_set_charset(
	string $charset,
	string $serveur = '',
	bool $requeter = true
) {
	$connexion = $GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	spip_log('changement de charset sql : SET NAMES ' . _q($charset), 'pg.' . _LOG_DEBUG);
	return pg_query($connexion['link'], $connexion['last'] = 'SET NAMES ' . _q($charset));
}

/**
 * Tester si le charset indiqué est disponible sur le serveur SQL
 *
 * @param array $charset
 * 	Nom du charset à tester (élément de la clé 'charset')
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	inutilisé
 *
 * @return array
 * 	Description du charset
 */
function spip_pg_get_charset(
	array $charset = [],
	string $serveur = '',
	bool $requeter = true
) {
	$connexion = $GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$connexion['last'] = $c = 'SELECT * FROM information_schema.character_sets'
		. (!$charset ? '' : (' WHERE  character_set_name ILIKE'
		. _q($charset['charset'])));

	return spip_pg_fetch(pg_query($connexion['link'], $c), null, $serveur);
}

/**
 * Optimiser une table SQL
 *
 * @param string $table
 * 	nom de la table a optimiser
 * @param string $serveur
 * 	nom de la connexion
 * @param bool $requeter
 * 	effectuer la requete ? sinon retourner son code
 *
 * @return bool|string
 *     - true si l'optimisation est sans erreur
 *     - false en cas d'erreur
 *     - string texte de la requête si $requeter vaut false
 */
function spip_pg_optimize(
	string $table,
	string $serveur = '',
	bool $requeter = true
) {
	return spip_pg_query('VACUUM ' . $table, $serveur, $requeter);
}

/**
 * Retourner l'instruction SQL pour obtenir le texte d'un champ contenant
 * une balise `<multi>` dans la langue indiquée
 *
 * Cette sélection est mise dans l'alias `multi` (instruction AS multi).
 *
 * @param string $objet
 * 	Colonne ayant le texte
 * @param string $lang
 * 	Langue à extraire
 *
 * @return string
 * 	texte de sélection pour la requête
 */
function spip_pg_multi(string $objet, string $lang) {
	$r = 'regexp_replace('
		. $objet
		. ",'<multi>.*[[]"
		. $lang
		. "[]]([^[]*).*</multi>', E'\\\\1') AS multi";

	return $r;
}

/**
 * Remplacer les idiosyncrasies MySQL dans les creations de table
 * par leurs équialents en Postgresql
 *
 * A completer par les autres, mais essayer de reduire en amont.
 *
 * @param string $v
 * 	requête de création de table
 *
 * @return string
 * 	la chaîne avec les remplacements effectués
 */
function mysql2pg_type(string $v) {
	$remplace = [
		'/auto_increment/i' => '', // non reconnu
		'/bigint/i' => 'bigint',
		'/mediumint/i' => 'mediumint',
		'/smallint/i' => 'smallint',
		'/tinyint/i' => 'int',
		'/int\s*[(]\s*\d+\s*[)]/i' => 'int',
		'/longtext/i' => 'text',
		'/mediumtext/i' => 'text',
		'/tinytext/i' => 'text',
		'/longblob/i' => 'text',
		'/0000-00-00/' => '0001-01-01',
		'/datetime/i' => 'timestamp',
		'/unsigned/i' => '',
		'/double/i' => 'double precision',
		'/VARCHAR\((\d+)\)\s+BINARY/i' => 'varchar(\1)',
		'/ENUM *[(][^)]*[)]/i' => 'varchar(255)',
		'/(timestamp .* )ON .*$/is' => '\\1',
	];

	return preg_replace(array_keys($remplace), array_values($remplace), $v);
}

/**
 * Tester si on a les fonctions Postgresql (pour l'install)
 *
 * @return bool
 *     True si on a les fonctions, false sinon
 */
function spip_versions_pg() {
	return function_exists('pg_connect');
}

/**
 * Réparer une table SQL
 *
 * Utilise `VACUUM FULL ...` de Postgresql
 *
 * @param string $table
 * 	Nom de la table SQL
 * @param string $serveur
 * 	Nom de la connexion
 * @param bool $requeter
 * 	Exécuter la requête, sinon la retourner
 *
 * @return bool|string|array
 *     - string texte de la requête si demandée,
 *     - true si la requête a réussie, false sinon
 */
function spip_pg_repair(
	string $table,
	string $serveur = '',
	bool $requeter = true
) {
	return spip_pg_query("VACUUM FULL `$table`", $serveur, $requeter);
}
