<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ADD_ARTICLE'					=> 'Добавить статью',
	'APPROVE'						=> 'Одобрить',
	'ARTICLE'						=> 'Статья',
	'ARTICLE_APPROVED_SUCESS'		=> 'Статья была одобрена.',
	'ARTICLE_AUTHOR'				=> 'Автор',
	'ARTICLE_BODY'					=> 'Текст статьи',
	'ARTICLE_BODY_EXPLAIN'			=> 'Введите здесь текст статьи',
	'ARTICLE_DATE'					=> 'Дата',
	'ARTICLE_DELETED'				=> 'Статья успешно удалена.',
	'ARTICLE_DESCRIPTION'			=> 'Описание',
	'ARTICLE_DISAPPROVED_SUCESS'	=> 'Статья была отклонена.',
	'ARTICLE_EDITED'				=> 'Статья успешно отредактирована.',
	'ARTICLE_MANAGE'				=> 'Управление статьями',
	'ARTICLE_MOVED'					=> 'Статья успешно перенесена.',
	'ARTICLE_NEED_APPROVE'			=> 'Статья успешно добавлена, но требует предварительного одобрения.',
	'ARTICLE_NO_EXISTS'				=> 'Такой статьи не существует',
	'ARTICLE_SUBMITTED'				=> 'Статья успешно добавлена.',
	'ARTICLE_TITLE'					=> 'Название статьи',
	'ARTICLES'						=> 'Статьи',

	'CAT_NO_EXISTS'					=> 'Такой категории не существует',
	'CATEGORIES'					=> 'Категории',
	'CATEGORIES_LIST'				=> 'Список категорий',
	'CATEGORY'						=> 'Категория',
	'COMMENTS'						=> 'Комментарии',
	'CONFIRM_DELETE_ARTICLE'		=> 'Вы уверены, что хотите удалить эту статью?',
	'COULDNT_GET_CAT_DATA'			=> 'Невозможно получить данные',
	'COULDNT_UPDATE_ORDER'			=> 'Невозможно изменить порядок категорий',

	'DELETE_ARTICLE'				=> 'Удалить статью',
	'DELETE_ARTICLE_WARN'			=> 'Удаленную статью восстановить невозможно',
	'DESCR'							=> 'Описание статьи',
	'DISAPPROVE'					=> 'Отклонить',

	'EDIT'							=> 'Редактировать',
	'EDIT_ARTICLE'					=> 'Редактировать статью',

	'EMPTY_QUERY'					=> 'Вы не ввели никакого поискового запроса',
	'FOUND_KB_SEARCH_MATCH'			=> 'Найдено %s совпадение',
	'FOUND_KB_SEARCH_MATCHES'		=> 'Найдено  совпадений %s',

	'KB_PERMISSIONS'				=> 'Права доступа',

	'LEAVE_COMMENTS'				=> 'Оставить комментарий',
	'LIBRARY'						=> 'Библиотека',
	'LOGIN_EXPLAIN_APPROVE'			=> 'Для проведения этого действия вы должны войти на конференцию.',

	'MAX_NUM_ATTACHMENTS'			=> 'Достигнуто максимально допустимое количество вложений: %d ',
	'MISSING_INLINE_ATTACHMENT'		=> 'Вложение <strong>%s</strong> больше недоступно',

	'NEED_APPROOVE'					=> 'Статья требует одобрения',
	'NO_ARTICLES'					=> 'В этой категории нет статей',
	'NO_CAT_YET'					=> 'В библиотеке еще нет ни одной категории.',
	'NO_DESCR'						=> 'Вы не ввели описание статьи',
	'NO_ID_SPECIFIED'				=> 'Не указан номер статьи',
	'NO_NEED_APPROVE' 				=> 'Эта статья не требует одобрения.',
	'NO_TEXT' 						=> 'Вы не ввели текст статьи',
	'NO_TITLE'						=> 'Вы не указали название статьи',
	'NOTIFICATION_ARTICLE_APPROVE'			=> '<b>Модератор</b> %1$s одобрил вашу статью:',
	'NOTIFICATION_ARTICLE_DISAPPROVE'		=>'<b>Модератор</b> %1$s отклонил вашу статью:',
	'NOTIFICATION_NEED_APPROVAL'			=> '<b>Ожидает одобрения</b> статья от пользователя %1$s:',
	'NOTIFICATION_TYPE_ARTICLE_APPROVE'		=> 'Статья была одобрена',
	'NOTIFICATION_TYPE_ARTICLE_DISAPPROVE'	=> 'Статья была отклонена',
	'NOTIFICATION_TYPE_NEED_APPROVAL'		=> 'Статья ожидает одобрения',

	'PRINT'								=> 'Версия для печати',

	'READ_FULL'							=> 'Прочитать статью полностью',
	'RETURN_ARTICLE'					=> '%sПерейти к статье%s',
	'RETURN_CAT'						=> '%sВернуться в категорию%s',
	'RETURN_LIBRARY'					=> '%sВернутся в Библиотеку%s',
	'RETURN_NEW_CAT'					=> '%sПерейти в новую категорию%s',
	'RETURN_TO_KB_SEARCH_ADV'			=> 'Вернуться к расширенному поиску',
	'RULES_KB_ADD_CAN'					=> 'Вы <b>можете</b> добавлять статьи',
	'RULES_KB_ADD_CANNOT'				=> 'Вы <b>не можете</b> добавлять статьи',
	'RULES_KB_ADD_NOAPPROVE'			=> 'Вы <b>можете</b> добавлять статьи без предварительного одобрения',
	'RULES_KB_ADD_NOAPPROVE_CANNOT'		=> 'Вы <b>не можете</b> добавлять статьи без предварительного одобрения',
	'RULES_KB_APPROVE_MOD_CAN'			=> 'Вы <b>можете</b> одобрять статьи',
	'RULES_KB_APPROVE_MOD_CANNOT'		=> 'Вы <b>не можете</b> одобрять статьи',
	'RULES_KB_DELETE_CAN'				=> 'Вы <b>можете</b> удалять свои статьи',
	'RULES_KB_DELETE_CANNOT'			=> 'Вы <b>не можете</b> удалять свои статьи',
	'RULES_KB_DELETE_MOD_CAN'			=> 'Вы <b>можете</b> удалять статьи',
	'RULES_KB_EDIT_CAN'					=> 'Вы <b>можете</b> редактировать свои статьи',
	'RULES_KB_EDIT_CANNOT'				=> 'Вы <b>не можете</b> редактировать свои статьи',
	'RULES_KB_EDIT_MOD_CAN'				=> 'Вы <b>можете</b> редактировать статьи',
	'RULES_KB_MOD_DELETE_CANNOT'		=> 'Вы <b>не можете</b> удалять статьи',
	'RULES_KB_MOD_EDIT_CANNOT'			=> 'Вы <b>не можете</b> редактировать статьи',

	'SEARCH_ARTICLES_ONLY'				=> 'Только в тексте статей',
	'SEARCH_ARTICLES_TITLE_ONLY'		=> 'Только в заголовках статей',
	'SEARCH_CAT'						=> 'Искать в категориях',
	'SEARCH_CAT_EXPLAIN'				=> 'Выберите категорию или категории, в которых будет произведён поиск. Если не выбрано ничего, поиск будет осуществлен во всех категориях.',
	'SEARCH_DISABLED'					=> 'Поиск в Библиотеке отключен администратором',
	'SEARCH_IN_CAT'						=> 'Поиск в категории…',
	'SEARCH_KB'							=> 'Поиск',
	'SORT_ARTICLE_TITLE'				=> 'Заголовок статьи',

	'TOTAL_ITEMS'						=> 'Статей: <strong>%d</strong>',

	'WARNING_DEFAULT_CONFIG'			=> 'Конфигурационные настройки библиотеки установлены по умолчанию, это может привести к некорректной работе модуля.<br />Пожалуйста, перейдите в <b>Конфигурация</b> и задайте необходимые значения.',
));
