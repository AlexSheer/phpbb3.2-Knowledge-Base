<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 * French translation by Galixte (http://www.galixte.com)
 *
 * @copyright (c) 2017 Sheer <https://phpbbguru.net>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	'ADD_ARTICLE'							=> 'Nouvel article',
	'APPROVE'								=> 'Approuver',
	'ARTICLE'								=> 'Article',
	'ARTICLES'								=> 'Articles',
	'ARTICLE_APPROVED_SUCESS'				=> 'L’article a été approuvé.',
	'ARTICLE_AUTHOR'						=> 'Auteur',
	'ARTICLE_BODY'							=> 'Texte de l’article',
	'ARTICLE_BODY_EXPLAIN'					=> 'Saisir le texte de l’article',
	'ARTICLE_DATE'							=> 'Date',
	'ARTICLE_DELETED'						=> 'L’article a été supprimé avec succès !',
	'ARTICLE_DESCRIPTION'					=> 'Description',
	'ARTICLE_DISAPPROVED_SUCESS'			=> 'L’article a été refusé.',
	'ARTICLE_EDITED'						=> 'L’article a été modifié avec succès !',
	'ARTICLE_MANAGE'						=> 'Gérer les articles',
	'ARTICLE_MOVED'							=> 'L’article a été déplacé avec succès !',
	'ARTICLE_NEED_APPROVE'					=> 'L’article a été créé avec succès, mais requiert l’approbation d’un modérateur.',
	'ARTICLE_NO_EXISTS'						=> 'Cet article n’existe pas',
	'ARTICLE_SUBMITTED'						=> 'L’article a été créé avec succès !',
	'ARTICLE_TITLE'							=> 'Nom de l’article',
	'CATEGORIES'							=> 'Catégories',
	'CATEGORIES_LIST'						=> 'Liste des catégories',
	'CATEGORY'								=> 'Categorie',
	'CAT_NO_EXISTS'							=> 'Cette catégorie n’existe pas',
	'COMMENTS'								=> 'Commentaires',
	'CONFIRM_DELETE_ARTICLE'				=> 'Confirmer la suppression de cet article.',
	'COULDNT_GET_CAT_DATA'					=> 'Impossible d’obtenir les données',
	'COULDNT_UPDATE_ORDER'					=> 'Impossible de modifier l’ordre des catégories',
	'DELETE_ARTICLE'						=> 'Supprimer l’article',
	'DELETE_ARTICLE_WARN'					=> 'L’article supprimé ne peut pas être restauré',
	'DESCR'									=> 'Description de l’article',
	'DISAPPROVE'							=> 'Refuser',
	'EDIT'									=> 'Modifier',
	'EDIT_ARTICLE'							=> 'Modifier l’article',
	'EMPTY_QUERY'							=> 'Aucun mot n’a été saisi dans le champs de la recherche',
	'FOUND_KB_SEARCH_MATCH'					=> '%d résultat trouvé',
	'FOUND_KB_SEARCH_MATCHES'				=> '%d résultats trouvés',
	'KB_PERMISSIONS'						=> 'Permissions',
	'LEAVE_COMMENTS'						=> 'Laisser un commentaire',
	'LIBRARY'								=> 'Base de connaissances',
	'LINK_TO_ARTICLE'						=> 'Lien vers l’article',
	'LOGIN_EXPLAIN_APPROVE'					=> 'Pour effectuer cette action il est nécessaire d’avoir enregistré un compte sur le forum et d’être connecté à celui-ci.',
	'MAX_NUM_ATTACHMENTS'					=> 'La limite du nombre de fichiers joints autorisés a été atteinte : %d .',
	'MISSING_INLINE_ATTACHMENT'				=> 'Le fichier joint : « <strong>%s</strong> » n’existe pas.',
	'MOVE_DRAGNDROP'						=> 'Click here left mouse button and move article to desired place, up or down',
	'NEED_APPROOVE'							=> 'L’article requiert l’approbation d’un modérateur.',
	'NOTIFICATION_ARTICLE_APPROVE'			=> 'Le <b>modérateur</b> %1$s a approuvé votre article :',
	'NOTIFICATION_ARTICLE_DISAPPROVE'		=> 'Le <b>modérateur</b> %1$s a refusé votre article :',
	'NOTIFICATION_NEED_APPROVAL'			=> '<b>Article en attente d’approbation</b> créé par %1$s :',
	'NOTIFICATION_TYPE_ARTICLE_APPROVE'		=> 'L’article a été approuvé',
	'NOTIFICATION_TYPE_ARTICLE_DISAPPROVE'	=> 'L’article a été refusé',
	'NOTIFICATION_TYPE_NEED_APPROVAL'		=> 'Article en attente d’approbation.',
	'NO_ARTICLES'							=> 'Il n’y a aucun article dans cette catégorie.',
	'NO_CAT_YET'							=> 'La base de connaissances n’a aucune catégorie.',
	'NO_DESCR'								=> 'Aucune description n’a été saisie pour cet article.',
	'NO_ID_SPECIFIED'						=> 'Aucun numéro/ID n’a été configuré pour cet article.',
	'NO_NEED_APPROVE'						=> 'Cet article ne requiert pas d’approbation.',
	'NO_TEXT'								=> 'Aucun texte n’a été saisi pour cet article.',
	'NO_TITLE'								=> 'Aucun titre n’a été saisi pour cet article.',
	'PRINT'									=> 'Imprimer',
	'READ_FULL'								=> 'Lire l’article complet',
	'RETURN_ARTICLE'						=> '%sRetourner à l’article%s',
	'RETURN_CAT'							=> '%sRetourner à la catégorie%s',
	'RETURN_LIBRARY'						=> '%sRetourner à la base de connaissance%s',
	'RETURN_NEW_CAT'						=> '%sSe rendre dans la nouvelle catégorie%s',
	'RETURN_TO_KB_SEARCH_ADV'				=> 'Retourner dans la recherche avancée',
	'RULES_KB_ADD_CAN'						=> 'Vous <b>pouvez</b> créer des articles',
	'RULES_KB_ADD_CANNOT'					=> 'Vous <b>ne pouvez pas</b> créer des articles',
	'RULES_KB_ADD_NOAPPROVE'				=> 'Vous <b>pouvez</b> créer des articles sans approbation',
	'RULES_KB_ADD_NOAPPROVE_CANNOT'			=> 'Vous <b>ne pouvez pas</b> créer des articles sans approbation',
	'RULES_KB_APPROVE_MOD_CAN'				=> 'Vous <b>pouvez</b> approuver des articles',
	'RULES_KB_APPROVE_MOD_CANNOT'			=> 'Vous <b>can not </b> approuver des articles',
	'RULES_KB_DELETE_CAN'					=> 'Vous <b>pouvez</b> supprimer vos articles',
	'RULES_KB_DELETE_CANNOT'				=> 'Vous <b>ne pouvez pas</b> supprimer vos articles',
	'RULES_KB_DELETE_MOD_CAN'				=> 'Vous <b>pouvez</b> supprimer des articles',
	'RULES_KB_EDIT_CAN'						=> 'Vous <b>pouvez</b> modifier vos articles',
	'RULES_KB_EDIT_CANNOT'					=> 'Vous <b>ne pouvez pas</b> modifier vos articles',
	'RULES_KB_EDIT_MOD_CAN'					=> 'Vous <b>pouvez</b> modifier des articles',
	'RULES_KB_MOD_DELETE_CANNOT'			=> 'Vous <b>ne pouvez pas</b> supprimer des articles',
	'RULES_KB_MOD_EDIT_CANNOT'				=> 'Vous <b>ne pouvez pas</b> modifier des articles',
	'SEARCH_ARTICLES_ONLY'					=> 'Uniquement dans le texte des articles',
	'SEARCH_ARTICLES_TITLE_ONLY'			=> 'Uniquement dans le titre des articles',
	'SEARCH_CAT'							=> 'Rechercher dans les catégories',
	'SEARCH_CAT_EXPLAIN'					=> 'Permet de sélectionner la ou les catégorie(s) dans laquelle/lesquelles le recherche s’effectuera. Si aucune catégorie n’est sélectionnée, la recherche s’effectuera dans toutes les catégories.',
	'SEARCH_DISABLED'						=> 'La recherche dans la base de connaissances a été désactivée par un administrateur.',
	'SEARCH_IN_CAT'							=> 'Recherche…',
	'SEARCH_KB'								=> 'Recherche',
	'SORT_ARTICLE_TITLE'					=> 'Titlre de l’article',
	'TOTAL_ITEMS'							=> 'Articles : <strong>%d</strong>',
	'WARNING_DEFAULT_CONFIG'				=> 'The configuration settings Knowledge base are installed by default, it can lead to incorrect operation of the module. <br />Please go to <b>Configuration</b> and specify the required values.',
));
