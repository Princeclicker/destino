<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// ---------------------------------------------------------------------------
// Contact form, CVT API.
//
// Called from body-62.html with [(#FORMULAIRE{contact})], which renders
// formulaires/contact.html.
//
// The template shipped with GoWilds was <form class="contact-form"> with no
// action and no method, so the browser did a GET on the current URL and
// nothing was ever transmitted. This is the CVT replacement: SPIP posts back
// to the current page with formulaire_action/formulaire_args, runs
// formulaires_contact_verifier(), then formulaires_contact_traiter().
//
// Storage only. This install cannot send mail: php.ini has sendmail_path
// blank with SMTP = localhost:25 and no local mail server, and SPIP's
// email_envoi meta is empty, so mail() returns false. Submissions are
// therefore persisted and no email is attempted.
// ---------------------------------------------------------------------------

/**
 * Default values for the form.
 *
 * The keys are the field names; SPIP repopulates them from the POST when
 * verification fails, which is what lets contact.html echo the values back
 * into the inputs after an error.
 *
 * @return array<string,string>
 */
function formulaires_contact_charger(...$args) {
	return [
		'nom' => '',
		'email' => '',
		'telephone' => '',
		'message' => '',
	];
}

/**
 * Validate the submission.
 *
 * A non-empty return makes SPIP skip formulaires_contact_traiter() and re-render the form
 * with these strings exposed to the template as #ENV{erreurs/<field>}.
 *
 * @return array<string,string> field name => message
 */
function formulaires_contact_verifier(...$args) {
	$erreurs = [];

	if (trim((string) _request('nom')) === '') {
		$erreurs['nom'] = 'Please enter your name.';
	}

	$email = trim((string) _request('email'));
	if ($email === '') {
		$erreurs['email'] = 'Please enter your email address.';
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$erreurs['email'] = 'This email address is not valid.';
	}

	if (trim((string) _request('telephone')) === '') {
		$erreurs['telephone'] = 'Please enter your phone number.';
	}

	if (trim((string) _request('message')) === '') {
		$erreurs['message'] = 'Please enter a message.';
	}

	return $erreurs;
}

/**
 * Persist an accepted submission.
 *
 * editable=false hides the form once the row is written, leaving only the
 * confirmation message.
 *
 * @return array<string,mixed>
 */
function formulaires_contact_traiter(...$args) {
	// table_objet_sql() only knows the object types SPIP has registered, so it
// returns an empty string for a custom table. Build the name from the
// configured prefix instead (config/connect.php sets it to 'spip', with no
// trailing underscore, so the separator has to be added).
	$table = ($GLOBALS['table_prefix'] ?: 'spip') . '_destino_messages';

	// sql_insertq() takes column => value pairs and quotes them for us.
	$id = sql_insertq(
		$table,
		[
			'nom' => substr(trim((string) _request('nom')), 0, 100),
			'email' => substr(trim((string) _request('email')), 0, 190),
			'telephone' => substr(trim((string) _request('telephone')), 0, 50),
			'message' => trim((string) _request('message')),
		]
	);

	if (!$id) {
		return ['message_erreur' => 'Your message could not be saved. Please try again.'];
	}

	return [
		'message_ok' => 'Thank you. Your message has been received.',
		'editable' => false,
	];
}
