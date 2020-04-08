<?php
/**
 * Default Russian Lexicon Entries for Tickets
 */

include_once('setting.inc.php');

$_lang['mailinglist'] = 'Рассылка';

$_lang['mailinglist_create_here'] = 'Рассылку';



/************************ Resource ***************/

$_lang['mailinglist_new'] = 'Новая рассылка';
$_lang['mailinglist_settings'] = 'Настройки рассылки';
$_lang['mailinglist_settings_title'] = 'Поля для отправки';
$_lang['mailinglist_subscribers'] = 'Подписчики';
$_lang['mailinglist_mailsender'] = 'Отправка писем';
$_lang['mailinglist_content_title'] = 'Содержание письма';
$_lang['mailinglist_files_title'] = 'Прикрепляемые файлы';
$_lang['mailinglist_emailsubject'] = 'Тема письма';
$_lang['mailinglist_emailsubject_desc'] = 'Тема письма рассылки (по умолчанию site_name)';
$_lang['mailinglist_emailfrom'] = 'От (почта)';
$_lang['mailinglist_emailfrom_desc'] = 'Почта с которой будут отправлены пиьсма (по умолчанию emailsender)';
$_lang['mailinglist_emailfromname'] = 'От кого (имя)';
$_lang['mailinglist_emailfromname_desc'] = 'Имя отображаемое в поле от кого (по умолчанию site_name)';
$_lang['mailinglist_emailreplyto'] = 'Ответить (почта)';
$_lang['mailinglist_emailreplyto_desc'] = 'Почта подставлемая в качестве адреса при нажатии кнопки "Ответить" (по умолчанию mailinglist_emailreplyto)';
$_lang['mailinglist_emailreplytoname'] = 'Ответить кому (имя)';
$_lang['mailinglist_emailreplytoname_desc'] = 'Имя отображаемое в поле кому при нажатии кнопки "Ответить" (по умолчанию mailinglist_emailreplytoname)';
$_lang['mailinglist_anonym_title'] = 'Анонимные подписчики';
$_lang['mailinglist_users_title'] = 'Пользователи подписчики';
$_lang['mailinglist_instances_title'] = 'Текущие очереди писем';
$_lang['mailinglist_instances_empty'] = 'В данный момент нет активных очередей писем';
$_lang['mailinglist_сrontab_unavail'] = 'Внимание! Сервис работы с задачами недоступен. Чтобы зыпустить рассылку вам необходимо самостоятельно добавить в crontab задачу. Параметры добавления будут показаны при создвнии очереди писем';
$_lang['mailinglist_instance_create'] = 'Создать очередь писем';
$_lang['mailinglist_instance_loading'] = 'Подождите...';
$_lang['mailinglist_instance_status_created'] = 'Создана';
$_lang['mailinglist_instance_status_prepared'] = 'Готова к рассылке';
$_lang['mailinglist_instance_status_process'] = 'Идёт рассылка!';
$_lang['mailinglist_instance_status_pause'] = 'Приостановлено';
$_lang['mailinglist_instance_status_stoped'] = 'Остановлена';
$_lang['mailinglist_instance_status_completed'] = 'Завершена';