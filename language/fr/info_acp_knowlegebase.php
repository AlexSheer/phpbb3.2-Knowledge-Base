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
	'ACP_KNOWLEDGE_BASE_CONFIGURE'					=> 'Paramètres',
	'ACP_KNOWLEDGE_EXPLAIN'							=> 'Depuis cette page il est possible de modifier la configuration de l’extension « Knowledge base ».',
	'ACP_LIBRARY_ARTICLES'							=> 'Gestion des articles',
	'ACP_LIBRARY_ATTACHMENTS'						=> 'Fichiers joints',
	'ACP_LIBRARY_ATTACHMENTS_ORPHAN'				=> 'Fichiers joints orphelins',
	'ACP_LIBRARY_MANAGE'							=> 'Gestion des catégories',
	'ACP_LIBRARY_MANAGE_EXPLAIN'					=> 'Depuis cette page il est possible de configurer un nombre illimité de catégories et sous-catégories. Il est possible de créer, modifier des catégories, rechercher leurs emplacements et de déplacer une catégorie dans une autre ou à la racine. Si le nombre d’articles d’une catégories ne correspond pas à la réalisé il est possible de resynchroniser le compte des articles pour chaque catégorie.',
	'ACP_LIBRARY_PERMISSIONS'						=> 'Permissions',
	'ACP_LIBRARY_PERMISSIONS_EXPLAIN'				=> 'Depuis cette page il est possible de modifier pour chaque membre ou groupe les permissions d’accès de chaque catégorie de la base de connaissances, ainsi que d’assigner des modétateurs des catégories. Les permissions administrateur sont disponibles sur la page par défaut de phpBB dans les modèles de permissions, onglet « Base de connaissances ».',
	'ACP_LIBRARY_PERMISSIONS_NO_CATS'				=> 'Pour définir des permissions au moins une catégorie doit être créée.',
	'ACP_LIBRARY_SEARCH'							=> 'Recherche',
	'ACP_LIBRARY_ATTACHMENTS_EXTRA_FILES'			=> 'Fichiers suppplémentaires',
	'ACP_LIBRARY_ATTACHMENTS_LOST_FILES'			=> 'Fichiers perdus',
	'ACP_LIBRARY_ATTACHMENTS_LOST_FILES_EXPLAIN'	=> 'Depuis cette page il est possible de trouver les fichiers perdus sur le serveur. Si des fichiers sont absents du serveur leurs entrées correspondantes dans les articles seront supprimées.',
	'ADD_CATEGORY'									=> 'Créer une catégorie',
	'ADD_CATEGORY_EXPLAIN'							=> 'Permet de créer une nouvelle catégorie.',
	'ALL_CATS'										=> 'Toutes les catégories',
	'ANOUNCE'										=> 'Annoncer les nouveaux d’articles sur le forum',
	'ANOUNCE_EXPLAIN'								=> 'Permet de créer un sujet pour commenter l’article, celui-ci contient une brève description ainsi qu’un lien vers l’article nouvellement créé.<br />Sélectionner un forum dans lequel seront publiés les sujets annonçant les nouveaux articles, depuis la liste de l’option suivante (le menu déroulant s’affiche une fois cette option activée).',
	'ARTICLE_MANAGE'								=> 'Gestion des articles',
	'ARTICLE_MANAGE_EXPLAIN'						=> 'Depuis cette page il est possible de supprimer ou déplacer les articles vers d’autres catégories, ainsi que de voir et modifier les articles (dans une fenêtre séparée).',
	'ARTICLE_MOVE_EXPLAIN'							=> 'Permet de sélectionner la catégorie dans laquelle déplacer l’article.',
	'ATTACHMENTS_EXPLAIN'							=> 'Depuis cette page il est possible de voir et supprimer les fichiers joints dans les articles.',
	'CATEGORYES'									=> 'Catégories',
	'CATEGORY_ADDED'								=> 'Catégorie créée avec succès ! À présent il est possible de définir %sses permissions%s.',
	'CATEGORY_DELETED'								=> 'Catégorie supprimée avec succès !',
	'CATEGORY_EDITED'								=> 'Catégorie modifiée avec succès !',
	'CATEGOTY_LIST'									=> 'Liste des catégories',
	'CAT_DESCR'										=> 'Description de la catégorie',
	'CAT_NAME'										=> 'Nom de la catégorie',
	'CAT_PARENT'									=> 'Catégorie parente',
	'CONFIRM_DEL_CAT'								=> 'Confirmer la suppression de cette catégorie.',
	'COPY_CAT_PERMISSIONS'							=> 'Copier les permissions de categorie',
	'COPY_CAT_PERMISSIONS_EXPLAIN'					=> 'Permet de copier les permissions de la catégorie sélectionnée pour la catégorie en cours de création/modification.',
	'DELETE_ALL_ARTICLES'							=> 'Supprimer les articles',
	'DELETE_SUBCATS'								=> 'Supprimer les sous-catégories et leur contenu',
	'DEL_CATEGORY'									=> 'Suppression de la catégorie',
	'DEL_CATEGORY_EXPLAIN'							=> 'Depuis cette page il est possible de supprimer la catégorie. Il est possible de sélectionner vers quelle catégorie le contenu (articles et sous-catégories) sera déplacé.',
	'EDIT_CATEGORY'									=> 'Modifier la catégorie',
	'EDIT_DATE'										=> 'Modifié',
	'EXTENSION_GROUP_EXPLAIN'						=> 'Depuis cette page il est possible de gérer les extensions des fichiers joints autorisées. À gauche les extensions actives et à droite les extensions disponibles (inactives). Pour désactiver une extension, utiliser le menu déroulant sur la gauche en veillant à ne garder que les extensions actives sélectionnées. Utiliser la combinaison de la touche CTRL et du clic gauche pour sélectionner/désélectionner plus d’une extension. Pour ajouter de nouvelles extensions, il est nécessaire de se rendre sur la page de gestion des extensions dans l’onglet « MESSAGES », page « Gérer les extensions des fichiers joints ».',
	'FILES_DELETED_SUCCESS'							=> 'Les fichiers joints ont été supprimés avec succès !',
	'KB_CONFIG_EXPLAIN'								=> 'Depuis cette page il est possible de modifier les options générales.',
	'KB_CONFIG_UPDATED'								=> 'Paramètres sauvegardés avec succès !',
	'KB_FORUM_EXPLAIN'								=> 'Permet de sélectionner a forum dans lequel seront publiés les annonces des nouveaux articles publiés dans la base de connaissances.',
	'KB_ROOT'										=> 'Racine de la base de connaissances',
	'KNOWLEDGE_BASE'								=> 'Base de connaissances',
	'LIBRARY_EDIT_CAT'								=> 'Modification de la catégorie',
	'LIBRARY_EDIT_CAT_EXPLAIN'						=> 'Depuis cette page il est possible de renommer la catégorie, saisir une brève description et de déplacer la catégorie (et son contenu) vers une autre catégorie ou vers la racine.',
	'MOVE_ARTICLES_TO'								=> 'Déplacer les articles',
	'MOVE_SUBCATS_TO'								=> 'Déplacer les sous-catégories et leur contenu',
	'NO_ARTICLES_IN_KB'								=> 'La base de connaissances n’a aucun article.',
	'NO_CATS_IN_KB'									=> 'La base de connaissances n’a pas de catégories.',
	'NO_CAT_DESCR'									=> 'Aucune description pour la catégorie n’a été saisi.',
	'NO_CAT_NAME'									=> 'Aucun nom pour la catégorie n’a été saisi.',
	'NO_COPY_PERMISSIONS'							=> 'Ne pas copier les permissions',
	'NO_DESTINATION_CATEGORY'						=> 'La catégorie de destination n’a pu être trouvée.',
	'NO_FILES_SELECTED'								=> 'Aucun fichier sélectionné.',
	'NO_PARENT'										=> 'Aucune / À la racine de la base de connaissances',
	'NUM_ARTICLES'									=> 'Articles',
	'ORPHAN_EXPLAIN'								=> 'Depuis cette page il est possible de consulter la liste des fichiers joints orphelins. En général ces fichiers apparaissent car les membres ont envoyés des fichiers joints sans publier d’article. Il est possible de les supprimer ou de les rattacher à des articles existants. Pour cela il suffit de saisir l’ID de l’article pour lequel on souhaite rattacher le fichier joint orphelin.',
	'PER_PAGE'										=> 'Nombre d’articles sur la page',
	'PER_PAGE_EXPLAIN'								=> 'Permet de saisir le nombre d’articles à afficher sur la page de gestion des articles et sur la page des résulats de la recherche.',
	'PRUNE_ATTACHMENTS_EXPLAIN'						=> 'L’existance des fichiers supplémentaires va être vérifiée sur le serveur. Si des fichiers existent ils seront supprimés. Confirmer cette action.',
	'PRUNE_ATTACHMENTS_FINISHED'					=> 'Aucun fichier supplémentaire n’a été trouvé.',
	'PRUNE_ATTACHMENTS_PROGRESS'					=> 'Les fichiers inutiles sont vérifiés. Merci de ne pas interrompre ce processus !<br />Les fichiers suivants ont été supprimés :',
	'PRUNE_ATTACHMENTS_FAIL'						=> '<br />La suppression des fichiers suivants a rencontré un problème, il n’a pas été possible de les supprimer :',
	'POST_ROW_ARTICLE_INFO'							=> ' ayant l’ID %1$d…',
	'RESYNC_ATTACHMENTS_FINISHED'					=> 'Les fichiers joints ont été synchronisés avec succès (les entrées correspondantes dans la base de données ont été vérifiées)',
	'RESYNC_ATTACHMENTS_PROGRESS'					=> 'La vérification des entrées dans la base de données est en cours ! Merci de ne pas interrompre le processus !',
	'SELECT_CAT'									=> 'Sélectionner une catégorie',
	'SELECT_CATEGORY'								=> 'Sélectionner une catégorie',
	'SYNC_OK'										=> 'Catégorie synchronisée avec succès !',
	'THUMBNAIL_EXPLAIN'								=> 'Les dimensions des miniatures sont définies dans la page par défaut de phpBB dans le PCA (&laquo; Paramètres des fichiers joints &raquo;).',
	'UPLOAD_DENIED_ARTICLE'							=> 'L’article ayant l’ID n’existe pas.',
	'UPLOADING_FILE_TO_ARTICLE'						=> 'Envoi du fichier « %1$s » pour l’article',

// User Permissions
	'kb_u_add'				=> 'Peut créer de nouveaux articles.',
	'kb_u_edit'				=> 'Peut modifier ses articles.',
	'kb_u_delete'			=> 'Peut supprimer ses articles.',
	'kb_u_add_noapprove'	=> 'Peut créer de nouveaux articles sans approbation.',
// Moderator Permissions
	'kb_m_edit'				=> 'Peut modifier les articles.',
	'kb_m_delete'			=> 'Peut supprimer les articles.',
	'kb_m_approve'			=> 'Peut approuver les articles.',

	'LOG_KB_CONFIG_SEARCH'					=> '<strong>Les paramètres de la recherche de la base de connaissances ont été sauvegardés avec succès !</strong>',
	'ACP_SEARCH_INDEX_EXPLAIN'				=> 'Depuis cette page il est possible de gérer les méthodes d’indexation de la recherche de la base de connaissances. Étant donné qu’une seule méthode d’indexation ne peut être utilisée en même temps, il est recommandé de supprimer les autres. Après toute modification des paramètres de la recherche (comme le nombre minimum/maximum de caractères) il est recommandé de recréer l’index de la recherche afin de prendre en compte ces modifications.',
	'ACP_SEARCH_SETTINGS_EXPLAIN'			=> 'Depuis cette page il est possible de définir quelles méthodes d’indexation sera utilisée pour indexer les contenus des articles et améliorer la recherche. Il est possible de définir diverses options pouvant influencer le processus d’indexation. Certains options sont communes à toutes les méthodes d’indexation.',

	'CONFIRM_SEARCH_BACKEND'				=> 'Confirmer la modification vers une méthode d’indexation différente pour la recherche. Après toute modification de méthode d’indexation il est nécessaire de recréer l’index. Après cette opération il est recommandé de supprimer tout autre index inutilisé libérant ainsi de la mémoire dans la base de données.',
	'CONTINUE_DELETING_INDEX'				=> 'Poursuivre la suppression du précédent index',
	'CONTINUE_DELETING_INDEX_EXPLAIN'		=> 'La suppression d’un index a été initialisée. Avant d’accéder à la page de l’index de la recherche il est nécessaire d’accomplir le processus initialisé ou de l’annuler.',
	'CONTINUE_INDEXING'						=> 'Poursuivre le processus d’indexation',
	'CONTINUE_INDEXING_EXPLAIN'				=> 'Un processus d’indexation a été initialisé. Avant d’accéder à la page de l’index de la recherche il est nécessaire d’accomplir le processus initialisé ou de l’annuler.',
	'CREATE_INDEX'							=> 'Créer l’index',

	'DELETE_INDEX'							=> 'Supprimer l’index',
	'DELETING_INDEX_IN_PROGRESS'			=> 'Suppression d’index en cours…',
	'DELETING_INDEX_IN_PROGRESS_EXPLAIN'	=> 'La méthode d’indexation de la recherche est actuellement en cours de nettyage de l’index. Cela peut durer plusieurs minutes.',

	'FULLTEXT_MYSQL_INCOMPATIBLE_DATABASE'	=> 'La méthode d’indexation « MySQL Fulltext » peut être utilisée avec les versions de MySQL 4 et supérieures.',
	'FULLTEXT_MYSQL_NOT_SUPPORTED'			=> 'La méthode d’indexation « MySQL Fulltext » peut être utilisée avec les tables MyISAM ou InnoDB. MySQL 5.6.4 ou plus récent est nécessaire pour indexer en mode « fulltext » avec les tables InnoDB.',
	'FULLTEXT_MYSQL_TOTAL_POSTS'			=> 'Nombre total de messages indexés',

	'FULLTEXT_POSTGRES_INCOMPATIBLE_DATABASE'	=> 'La méthode d’indexation « PostgreSQL Fulltext » peut être utilisée avec PostgreSQL.',
	'FULLTEXT_POSTGRES_TOTAL_POSTS'				=> 'Nombre total de messages indexés',
	'FULLTEXT_POSTGRES_VERSION_CHECK'			=> 'Version de PostgreSQL',
	'FULLTEXT_POSTGRES_TS_NAME'					=> 'Profil de configuration de la recherche de texte :',
	'FULLTEXT_POSTGRES_VERSION_CHECK_EXPLAIN'	=> 'La version 8.3 ou supérieure de PostgreSQL est requise pour cette méthode d’indexation.',

	'GENERAL_SEARCH_SETTINGS'				=> 'Paramètres généraux de la recherche',

	'INDEX_STATS'							=> 'Statistiques d’indexation',
	'INDEXING_IN_PROGRESS'					=> 'Indexation en cours de progession…',
	'INDEXING_IN_PROGRESS_EXPLAIN'			=> 'La méthode d’indexation est actuellement en cours d’indexation de tous les articles. Ce processus peut durer plusieurs minutes à plusieurs heures selon le volume de données à traiter.',

	'LIMIT_SEARCH_LOAD'						=> 'Search page system load limit',
	'LIMIT_SEARCH_LOAD_EXPLAIN'				=> 'Si la charge système d’une minute dépasse cette valeur la recherche passera en mode hors ligne, 1.0 éqivaux à environ 100% d’utilisation du processeur. Cette fonctionnlité n’est valable que les serveurs utilisant un système d’exploitation basé sur UNIX.',
	'PER_PAGE_SEARCH'						=> 'Résultats de la recherche',
	'PER_PAGE_SEARCH_EXPLAIN'				=> 'Permet de saisir le nombre d’éléments à afficher sur la page des résultats de la recherche.',

	'PROGRESS_BAR'							=> 'Barre de progression',

	'SEARCH_INDEX_CREATE_REDIRECT'			=> array(
		2	=> 'Tous les messages jusqu’au message ayant l’ID %2$d ont été indexés, un lot de %1$d messages a été traité.<br />',
	),
	'SEARCH_INDEX_CREATE_REDIRECT_RATE'		=> array(
		2	=> 'Le taux actuel d’indexation est de %1$.1f messages par seconde.<br />Indexation en cours…',
	),
	'SEARCH_INDEX_DELETE_REDIRECT'			=> array(
		2	=> 'Tous les messages jusqu’au message ayant l’ID %2$d ont été effacés de l’index de recherche.<br />Effacement en cours…',
	),
	'SEARCH_INDEX_CREATED'					=> 'Tous les messages du forum ont été indexés.',
	'SEARCH_INDEX_REMOVED'					=> 'L’index de recherche a été supprimé.',
	'SEARCH_TYPE'							=> 'Méthode d’indexation',
	'SEARCH_TYPE_EXPLAIN'					=> 'phpBB vous permet de choisir la méthode d’indexation utilisée pour la recherche de texte dans le contenu des messages. Par défaut, la recherche utilisera la recherche FULLTEXT de phpBB.',
	'SWITCHED_SEARCH_BACKEND'				=> 'Vous avez modifié la méthode d’indexation de la recherche. Afin d’utiliser la nouvelle méthode d’indexation, vous devrez vous assurer qu’il existe bien un index de recherche pour celle-ci.',

	'TOTAL_WORDS'							=> 'Nombre total de mots indexés',
	'TOTAL_MATCHES'							=> 'Nombre total de mots indexés en relation avec les messages',

	'YES_SEARCH'							=> 'Activer la fonction de recherche',
	'YES_SEARCH_EXPLAIN'					=> 'Active la fonctionnalité de recherche, ce qui inclut la recherche des membres.',
	'YES_SEARCH_UPDATE'						=> 'Activer la mise à jour de FULLTEXT',

	'ACP_LIBRARY_LOGS'					=> 'Journal des actions',
	'ACP_LIBRARY_LOGS_EXPLAIN'			=> 'Depuis cette page il est possible de consulter l’ensemble des actions effectuées concernant la base de connaissances. Il est possible de trier selon le nom d’utilisateur, la date, l’adresse IP et l’action. Enfin, il est possible de supprimer des entrées du journal individuellement ou dans son ensemble.',
	'LOG_CLEAR_KB'						=> '<strong>Journal des actions néttoyé</strong>',
	'LOG_CATS_MOVE_DOWN'				=> '<strong>Catégorie déplacée</strong> %1$s <strong>en dessous de</strong> %2$s',
	'LOG_CATS_MOVE_UP'					=> '<strong>Catégorie déplacée</strong> %1$s <strong>au-dessus de</strong> %2$s',
	'LOG_CATS_ADD'						=> '<strong>Catégorie créée</strong><br /> %s',
	'LOG_CATS_DEL_ARTICLES'				=> '<strong>Articles de catégorie supprimés</strong><br /> %s',
	'LOG_CATS_DEL_MOVE_POSTS_MOVE_CATS'	=> '<strong>Catégorie supprimée</strong> %3$s, <strong>Articles déplacés vers</strong> %1$s <strong>et sous-catégories vers</strong> % 2$s',
	'LOG_CATS_DEL_MOVE_POSTS'			=> '<strong>Catégorie supprimée</strong> %2$s<br /><strong>et articles déplacés dans</strong> % 1$s',
	'LOG_CATS_DEL_CAT'					=> '<strong>Catégorie supprimée</strong><br /> %s',
	'LOG_CATS_DEL_MOVE_POSTS_CATS'		=> '<strong>Catégorie supprimée</strong> %2$s<br /><strong>et sous-catégorie et articlés déplacés vers</strong> %1$s',
	'LOG_CATS_DEL_POSTS_MOVE_CATS'		=> '<strong>Catégorie supprimée</strong> %2$s <strong>avec articles, sous-catégories déplacées vers</strong> %1$s',
	'LOG_CATS_DEL_POSTS_CATS'			=> '<strong>Catégorie supprimée avec articles et sous-catégories</strong><br /> %s',
	'LOG_CATS_DEL_CATS'					=> '<strong>Catégorie supprimée</strong> %2$s <strong>et sous-catégories déplacés vers</strong> %1$s',
	'LOG_CATS_EDIT'						=> '<strong>Catégorie modifiée</strong><br /> %1$s',
	'LOG_CATS_CAT_MOVED_TO'				=> '<strong>Catégorie</strong> %1$s <strong>déplacée vers</strong> %2$s',
	'LOG_CATS_SYNC'						=> '<strong>Catégorie resynchronisée</strong><br /> %1s',
	'LOG_KB_CONFIG_SEARCH'				=> '<strong>Méthode d’indexation modifiée</strong>',
	'LOG_KB_SEARCH_INDEX_REMOVED'		=> '<strong>Index de recherche supprimé</strong>',
	'LOG_KB_SEARCH_INDEX_CREATED'		=> '<strong>Index de recherche créé</strong>',
	'LOG_LIBRARY_ADD_ARTICLE'			=> 'Article créé &laquo;<strong>%1s</strong>&raquo; dans la catégorie<br /> <strong>%2s</strong>',
	'LOG_LIBRARY_DEL_ARTICLE'			=> 'Article supprimé &laquo;<strong>%1s</strong>&raquo; de la catégorie<br /> <strong>%2s</strong>',
	'LOG_LIBRARY_EDIT_ARTICLE'			=> 'Article modifié &laquo;<strong>%1s</strong>&raquo; dans la catégorie<br /> <strong>%2s</strong>',
	'LOG_LIBRARY_MOVED_ARTICLE'			=> 'Article déplacé <strong>%1s</strong> de la catégorie <strong>%2s</strong><br />vers la catégorie <strong>%3s</strong>',
	'LOG_LIBRARY_APPROVED_ARTICLE'		=> 'Article approuvé <strong>%1s</strong> dans la catégorie <strong>%2s</strong><br />créé par le membre <strong>%3s</strong>',
	'LOG_LIBRARY_REJECTED_ARTICLE'		=> 'Article refusé <strong>%1s</strong> dans la catégorie <strong>%2s</strong><br />créé par le membre <strong>%3s</strong>',
	'LOG_LIBRARY_PERMISSION_DELETED'	=> 'Accès à la catégorie retiré pour le groupe/membre <strong>%1s</strong><br /> %2s',
	'LOG_LIBRARY_PERMISSION_ADD'		=> 'Accès à la catégorie ajouté/modifié pour le groupe/membre <strong>%1s</strong><br /> %2s',
	'LOG_LIBRARY_CONFIG'				=> '<strong>Base de connaissances reconfigurée</strong>',
));
