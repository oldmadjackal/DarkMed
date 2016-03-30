-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- Хост: localhost
-- Время создания: Мар 30 2016 г., 23:13
-- Версия сервера: 5.0.51
-- Версия PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- База данных: `darkmed`
-- 

-- --------------------------------------------------------

-- 
-- Структура таблицы `access_list`
-- 

CREATE TABLE `access_list` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL,
  `login` varchar(32) collate utf8_unicode_ci NOT NULL,
  `page` int(5) unsigned NOT NULL,
  `crypto` varchar(128) collate utf8_unicode_ci NOT NULL,
  `ext_key` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Ключ шифрования файлов',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`,`page`,`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Реестр управления доступом и ключами' AUTO_INCREMENT=95 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `callback_msg`
-- 

CREATE TABLE `callback_msg` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `session` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Сессия',
  `user` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Пользователь',
  `category` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Категория',
  `form` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Имя диалоговой формы',
  `message` varchar(1024) collate utf8_unicode_ci default NULL COMMENT 'Текст сообщения',
  `status` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Статус обработки',
  `remark` varchar(1024) collate utf8_unicode_ci default NULL COMMENT 'Результат обработки',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Сообщения обратной связи от пользователей' AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `client_pages`
-- 

CREATE TABLE `client_pages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `page` int(5) unsigned NOT NULL COMMENT 'Номер страницы',
  `type` varchar(16) collate utf8_unicode_ci NOT NULL default 'client' COMMENT 'Тип страницы',
  `creator` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Создатель страницы',
  `check` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Контрольная строка шифрования',
  `title` varchar(512) collate utf8_unicode_ci NOT NULL COMMENT 'Заголовок',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Примечание',
  `published` varchar(1) collate utf8_unicode_ci default NULL COMMENT 'Метка опубликования',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`,`page`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Дополнительные страницы данных клиента' AUTO_INCREMENT=48 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `client_pages_ext`
-- 

CREATE TABLE `client_pages_ext` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `page_id` int(11) unsigned NOT NULL COMMENT 'Идентификатор страницы',
  `order_num` int(5) unsigned NOT NULL COMMENT 'Порядковый номер',
  `type` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Тип блока',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Текст',
  `file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к файлу',
  `short_file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к короткому файлу',
  `www_link` varchar(512) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `prescription_id` (`page_id`,`order_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `client_page_main`
-- 

CREATE TABLE `client_page_main` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `check` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Контрольная строка шифрации',
  `name_f` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Фамилия',
  `name_i` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Имя',
  `name_o` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Отчество',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Главные страницы пользователей' AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `deseases_ext`
-- 

CREATE TABLE `deseases_ext` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `desease_id` int(11) unsigned NOT NULL COMMENT 'Идентификатор заболевания',
  `order_num` int(5) unsigned NOT NULL COMMENT 'Порядковый номер',
  `user` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Создавший пользователь',
  `type` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Тип блока',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Текст',
  `file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к файлу',
  `short_file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к короткому файлу',
  `www_link` varchar(512) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `prescription_id` (`desease_id`,`order_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `deseases_registry`
-- 

CREATE TABLE `deseases_registry` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Добавивший пользователь',
  `type` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Категория',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Название',
  `reference` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Официальный регистрационный код',
  `description` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Основное описание',
  `www_link` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Основная ссылка на внешнее описание',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Общий реестр заболеваний' AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `doctor_certificates`
-- 

CREATE TABLE `doctor_certificates` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `confirmed` varchar(1) collate utf8_unicode_ci NOT NULL default 'N' COMMENT 'Флаг подтверждения',
  `order_num` int(5) unsigned NOT NULL,
  `kind` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Вид документа',
  `issuer` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Выдавшая организация',
  `requicites` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Серия, номер',
  `issue_date` varchar(16) collate utf8_unicode_ci default NULL COMMENT 'Дата выдачи',
  `exp_date` varchar(16) collate utf8_unicode_ci default NULL COMMENT 'Срок действия',
  `desc` varchar(1024) collate utf8_unicode_ci default NULL COMMENT 'Описание',
  `www_link` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Ссылка для электронных сертификатов',
  `image_1` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Файл картинки 1',
  `image_1_s` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Файл картинки 1, сокращенный вариант',
  `image_2` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Файл картинки 2',
  `image_2_s` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Файл картинки 2, сокращенный вариант',
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`,`order_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Квалификационные документы врачей' AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `doctor_notes`
-- 

CREATE TABLE `doctor_notes` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `client` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Пациент',
  `check` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Контрольная строка шифрования',
  `category` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Категория',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Заметки',
  `deseases` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Перечень кодов заболеваний',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`,`client`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Заметки врача по пациенту' AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `doctor_page_ext`
-- 

CREATE TABLE `doctor_page_ext` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `page_id` int(11) unsigned NOT NULL COMMENT 'Идентификатор страницы',
  `order_num` int(5) unsigned NOT NULL COMMENT 'Порядковый номер',
  `type` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Тип блока',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Текст',
  `file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к файлу',
  `short_file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к короткому файлу',
  `www_link` varchar(512) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`,`order_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Дополнительные блоки формуляра доктора' AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `doctor_page_main`
-- 

CREATE TABLE `doctor_page_main` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `confirmed` varchar(1) collate utf8_unicode_ci NOT NULL default 'N' COMMENT 'Флаг подтверждения',
  `name_f` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Фамилия',
  `name_i` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Имя',
  `name_o` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Отчество',
  `speciality` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Специальность',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Примечание',
  `portrait` varchar(64) collate utf8_unicode_ci default NULL COMMENT 'Имя файла портрета',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `measurements`
-- 

CREATE TABLE `measurements` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `page_id` int(11) NOT NULL COMMENT 'Идентификатор страницы назначения',
  `measurement_id` int(11) unsigned NOT NULL COMMENT 'Идентификатор назначения',
  `checked` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Время измерения',
  `value` varchar(64) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `measurement_id` (`measurement_id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список активных сессий' AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `messages`
-- 

CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `receiver` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Получатель',
  `sender` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Отправитель',
  `sent` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Дата/время создания',
  `type` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Тип сообщения',
  `text` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Видимый текст',
  `copy` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Копия текста сообщения',
  `details` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Прилагаемая техническая спецификация',
  `read` varchar(1) collate utf8_unicode_ci default NULL COMMENT 'Флаг прочтения',
  PRIMARY KEY  (`id`),
  KEY `receiver` (`receiver`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Сообщения участникам' AUTO_INCREMENT=127 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `prescriptions_ext`
-- 

CREATE TABLE `prescriptions_ext` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `prescription_id` int(11) unsigned NOT NULL COMMENT 'Идентификатор назначения',
  `order_num` int(5) unsigned NOT NULL COMMENT 'Порядковый номер',
  `user` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Создавший пользователь',
  `type` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Тип блока',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Текст',
  `file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к файлу',
  `short_file` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Путь к короткому файлу',
  `www_link` varchar(512) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `prescription_id` (`prescription_id`,`order_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `prescriptions_pages`
-- 

CREATE TABLE `prescriptions_pages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `page` int(5) unsigned NOT NULL COMMENT 'Номер страницы',
  `order_num` int(5) unsigned NOT NULL COMMENT 'Порядковый номер',
  `prescription_id` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Идентификатор назначения',
  `name` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Название назначения',
  `remark` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Примечание',
  `type` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Тип назначения',
  `reference` int(11) unsigned NOT NULL default '0' COMMENT 'Ссылочный идентификатор',
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`,`page`),
  KEY `reference` (`reference`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Состав назначений пациентам' AUTO_INCREMENT=120 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `prescriptions_registry`
-- 

CREATE TABLE `prescriptions_registry` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Добавивший пользователь',
  `type` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Тип назначения',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Название',
  `reference` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Официальный регистрационный код',
  `description` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Основное описание',
  `www_link` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Основная ссылка на внешнее описание',
  `deseases` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Перечень кодов заболеваний',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Общий реестр назначений' AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `releases`
-- 

CREATE TABLE `releases` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `types` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Список типов пользователей',
  `title` varchar(256) collate utf8_unicode_ci NOT NULL COMMENT 'Название',
  `notes` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Название файла описания релиза',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список релизов' AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `releases_read`
-- 

CREATE TABLE `releases_read` (
  `release_id` int(11) NOT NULL,
  `user` varchar(32) collate utf8_unicode_ci NOT NULL,
  `time_mark` timestamp NOT NULL default CURRENT_TIMESTAMP,
  KEY `release_id` (`release_id`,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список прочитанных релизов';

-- --------------------------------------------------------

-- 
-- Структура таблицы `sessions`
-- 

CREATE TABLE `sessions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `login` varchar(32) collate utf8_unicode_ci NOT NULL,
  `session` varchar(32) collate utf8_unicode_ci NOT NULL,
  `started` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `session` (`session`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список активных сессий' AUTO_INCREMENT=426 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `sets_elements`
-- 

CREATE TABLE `sets_elements` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `set_id` int(11) unsigned NOT NULL COMMENT 'Идентификатор комплекса',
  `order_num` int(5) unsigned NOT NULL COMMENT 'Порядковый номер',
  `prescription_id` int(11) unsigned NOT NULL COMMENT 'Идентификатор назначения',
  `remark` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Примечание',
  PRIMARY KEY  (`id`),
  KEY `set_id` (`set_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список элементов в составе комплексов назначений' AUTO_INCREMENT=64 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `sets_registry`
-- 

CREATE TABLE `sets_registry` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Пользователь-владелец',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Название',
  `description` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Описание',
  `deseases` varchar(512) collate utf8_unicode_ci default NULL COMMENT 'Перечень кодов заболеваний',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Реестр комплексов назначений' AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

-- 
-- Структура таблицы `users`
-- 

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `login` varchar(32) collate utf8_unicode_ci NOT NULL,
  `password` varchar(32) collate utf8_unicode_ci NOT NULL,
  `email` varchar(128) collate utf8_unicode_ci default NULL,
  `sign_p_key` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Публичный ключ подписи',
  `sign_s_key` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Секретный ключ подписи',
  `msg_key` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Ключ шифрования копий исходящих сообщений',
  `options` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Специальные права',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список логинов пользователей' AUTO_INCREMENT=29 ;
