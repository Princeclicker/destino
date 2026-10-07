<?php

namespace Spip\Archiver;

/**
 * Représenter les fonctions de gestion liées aux fichiers d'archive.
 */
interface ArchiverInterface
{
	/**
	 * Renvoyer le dernier code d'erreur.
	 *
	 * @return int Dernier code d'erreur
	 */
	public function erreur(): int;

	/**
	 * Renvoyer le dernier message d'erreur.
	 *
	 * @return string Dernier message d'erreur
	 */
	public function message(): string;

	/**
	 * Indiquer si l'archive est accessible en ecriture ou pas.
	 *
	 * @return bool true si l'archive est en lecture seule
	 */
	public function getLectureSeule(): bool;

	/**
	 * Indiquer le détail du contenu de l'archive.
	 *
	 * @return array{proprietes:array{racine?:string},fichiers:array<array{filename:string,checksum?:string,size:int,mtime?:int,status?:string,raw?:string}>,commentaire:string}
	 */
	public function informer(): array;

	/**
	 * Extraire tout ou partie des fichiers de l'archive vers une destination.
	 *
	 * @param string    $destination Chemin du répertoire d'extraction
	 * @param string[]	$fichiers    Liste des fichiers à extraire
	 *
	 * @return bool Succès de l'opération
	 */
	public function deballer(string $destination = '', array $fichiers = []): bool;

	/**
	 * Créer ou modifier des fichiers dans le fichier d'archive.
	 *
	 * @param array<int|string,string>	$fichiers Liste des fichiers à ajouter ou modifier
	 * @param string|null               $racine   Répertoire racine des fichiers à retirer du chemin lorsqu'on emballe
	 *
	 * @return bool Succès de l'opération
	 */
	public function emballer(array $fichiers = [], ?string $racine = null): bool;

	/**
	 * Retirer une liste de fichiers dans le fichier d'archive.
	 *
	 * @param array<int|string,string> $fichiers Liste des fichiers à retirer
	 *
	 * @return bool Succès de l'opération
	 */
	public function retirer(array $fichiers = []): bool;

	/**
	 * Associer un commentaire à l'archive.
	 *
	 * @param string $texte Texte du commentaire à associer à l'archive
	 *
	 * @return bool Succès de l'opération
	 */
	public function commenter(string $texte = ''): bool;
}
