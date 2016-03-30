-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- Хост: localhost
-- Время создания: Мар 30 2016 г., 23:14
-- Версия сервера: 5.0.51
-- Версия PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- База данных: `darkmed`
-- 

-- --------------------------------------------------------

-- 
-- Структура таблицы `ref_cert_issuers`
-- 

CREATE TABLE `ref_cert_issuers` (
  `code` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Код',
  `language` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Язык',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Описание',
  UNIQUE KEY `code` (`code`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Справочник выдавателей квалификационных документов';

-- 
-- Дамп данных таблицы `ref_cert_issuers`
-- 

INSERT INTO `ref_cert_issuers` (`code`, `language`, `name`) VALUES ('HE_1MGMU', 'RU', 'Первый Московский государственный медицинский университет имени И.М. Сеченова');
INSERT INTO `ref_cert_issuers` (`code`, `language`, `name`) VALUES ('HE_MGMSU', 'RU', 'Московский государственный медико-стоматологический университет');
INSERT INTO `ref_cert_issuers` (`code`, `language`, `name`) VALUES ('HE_RNIMU', 'RU', 'Российский национальный исследовательский медицинский университет имени Н.И. Пирогова');

-- --------------------------------------------------------

-- 
-- Структура таблицы `ref_cert_kinds`
-- 

CREATE TABLE `ref_cert_kinds` (
  `code` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Код',
  `language` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Язык',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Описание',
  UNIQUE KEY `code` (`code`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Справочник видов квалификационных документов';

-- 
-- Дамп данных таблицы `ref_cert_kinds`
-- 

INSERT INTO `ref_cert_kinds` (`code`, `language`, `name`) VALUES ('HE_DIPLOM', 'RU', 'Диплом о высшем образовании');

-- --------------------------------------------------------

-- 
-- Структура таблицы `ref_doctor_specialities`
-- 

CREATE TABLE `ref_doctor_specialities` (
  `code` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Код',
  `language` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Язык',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Описание',
  UNIQUE KEY `code` (`code`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Справочник врачебных специальностей';

-- 
-- Дамп данных таблицы `ref_doctor_specialities`
-- 

INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Cosmetologist', 'RU', 'Косметолог');
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Nurse', 'RU', 'Медсестра');
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Physiatrist', 'RU', 'Физиотерапевт');
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Therapeutist', 'RU', 'Терапевт');
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Traumatologist', 'RU', 'Травматолог');
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Рehabilitionist', 'RU', 'Реабилитолог');

-- --------------------------------------------------------

-- 
-- Структура таблицы `ref_messages_types`
-- 

CREATE TABLE `ref_messages_types` (
  `code` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Код типа сообщения',
  `language` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Язык описания',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Название типа сообщения',
  UNIQUE KEY `code` (`code`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Справочник типов сообщений';

-- 
-- Дамп данных таблицы `ref_messages_types`
-- 

INSERT INTO `ref_messages_types` (`code`, `language`, `name`) VALUES ('CHAT_MESSAGE', 'RU', 'Сообщение');
INSERT INTO `ref_messages_types` (`code`, `language`, `name`) VALUES ('CLIENT_ACCESS_INVITE', 'RU', 'Приглашение от пациента');
INSERT INTO `ref_messages_types` (`code`, `language`, `name`) VALUES ('CLIENT_PRESCRIPTIONS_ALERT', 'RU', 'Сделано назначение');

-- --------------------------------------------------------

-- 
-- Структура таблицы `ref_prescriptions_types`
-- 

CREATE TABLE `ref_prescriptions_types` (
  `code` varchar(32) collate utf8_unicode_ci NOT NULL,
  `language` varchar(16) collate utf8_unicode_ci NOT NULL,
  `name` varchar(128) collate utf8_unicode_ci NOT NULL,
  UNIQUE KEY `code` (`code`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список типов назначений';

-- 
-- Дамп данных таблицы `ref_prescriptions_types`
-- 

INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('exercise', 'RU', 'Упражнение');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('exploration', 'RU', 'Исследование');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('measurement', 'RU', 'Контрольное измерение');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('operation', 'RU', 'Операция');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('others', 'RU', 'Прочее');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('pharmacotherapy', 'RU', 'Лекарственная терапия');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('test', 'RU', 'Анализ');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('treatment', 'RU', 'Процедура');
INSERT INTO `ref_prescriptions_types` (`code`, `language`, `name`) VALUES ('unregistered', 'RU', 'Вне Регистра');
