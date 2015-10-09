-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- Хост: localhost
-- Время создания: Авг 22 2015 г., 12:38
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

DROP TABLE IF EXISTS `access_list`;
CREATE TABLE `access_list` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL,
  `login` varchar(32) collate utf8_unicode_ci NOT NULL,
  `page` int(5) unsigned NOT NULL,
  `crypto` varchar(128) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`,`page`,`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Реестр управления доступом и ключами' AUTO_INCREMENT=43 ;

-- 
-- Дамп данных таблицы `access_list`
-- 

INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (3, 'mad', 'mad', 0, 'U2FsdGVkX194rXY3awfq7HYAy4rCEaYJhW4cEXeAHPQ=');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (14, 'user1', 'user1', 0, 'U2FsdGVkX18Bdt5O+G52xyAdIN9hYOlOy0RqmQWP1RCAfK5PvcIPAF6d12PAclHYy6Hax09gIPGgr78J3dNsNXWPqI4D2mLeMjRbdMhhpNQZcsjqZITSew==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (15, 'user1', 'user1', 1, 'U2FsdGVkX19E273aoCfDXaqGqQAKaN1VE8fhRixFSxs05d6rddS13iWSu1Oll13HyTzFt8vK0wW41VjgV2oY7Q==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (22, 'user1', 'doc', 1, 'U2FsdGVkX1+9trVobsBjEc0t8ogJscvUHQC7ZILhJ6TBtIqXPPphZjofJ2W3zEJ5I4o33jijpevpUfEUDNju0w==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (23, 'mad', 'doc', 0, 'U2FsdGVkX1/rbJqW4BH19B1hZIcHP0RK0PgZcJZGJWI=');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (24, 'crash', 'crash', 0, 'U2FsdGVkX1+DZHo75R1mXmK4ujKwOYqDU2KTr6EbTD/nAa5TXJEXanQv0AM2SknkXEDDhwDhl2aIVYAA2YXz7++RavSXnwsJNd7NOC/NOl4IGpT8sIhuRA==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (25, 'crash', 'doc', 0, 'U2FsdGVkX185rrvbdY3GEpu5g5uZTidZf8kjUKlBfAGLWDgahu/kRbMjXj1lo0IIBnp3G/z7d9n60R415dycdCqaP7ZJC/yTJUmCAv/AKG8QwDq+QYc+kg==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (26, 'doc', 'doc', 0, 'U2FsdGVkX19lD1eAoaUJJtDRUbGYUpWZlHAT3xuNN7MFTB5kpEusb2K5id2T62imBBy2KVIj2u7y8QvIDwuH89h5IJN6QhiWmuknF0mmT82ygzMJtaeprg==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (27, 'fis', 'fis', 0, 'U2FsdGVkX18l+IUmLPxITS71R3vtNo7R+pmQ/SwPBn9sNdG1capU+XAmMRLGMV1QYLe5pMUI7FYm0HGwFWRySof0Es/Jll4m1rPcqjBA5bEmNJDHslTZKg==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (33, 'ivanov', 'ivanov', 0, 'U2FsdGVkX1/y2WmP2dURAmWV/SZWrf3LVA/Qvw67JBRXTr2TAbMQwSLUe57TbxY7Z9InuNNEUfWdO9rvmBmM88zNloe+MF+E/DF8s7oGOaT19nKZiJhusQ==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (34, 'sidorov', 'sidorov', 0, 'U2FsdGVkX19SWMkHpYdlN4qSyEbE7Aj+3X2ylMXEEXR8IvJtx5dycwF7Ltlf9YLhOeM0bXV2JEYAez5gWC1WJsuKFGESJ8qZ+2QiF9YmrZYLyF6qaEw8Pw==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (35, 'ivanov', 'ivanov', 1, 'U2FsdGVkX1/y9uoKwxvj3nt+g4kF5da8V+7JqOjV0g9xbJh4hMiT11QmEHxBRKjBJB8OwgSLHZyoSxxLdZmo/w==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (36, 'ivanov', 'ivanov', 2, 'U2FsdGVkX1+K2SlyO25Vp0rE1XZtYE3FnTsfkasW/lciBpoqLvBS1HxJaG5mRq20DDUprbcrBS+6xqzlSChE4w==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (37, 'ivanov', 'ivanov', 3, 'U2FsdGVkX19dNXtayXltSQS75Gnxve7vQtDK8p4lm4ZOlYRbhyWqGT2PxTS/hMtQcfElmJO04nxDaOvNXV5/kA==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (39, 'petrov', 'petrov', 0, 'U2FsdGVkX1/D/cUUlON7HlQo6Mkz0AmWrOedB5PBXxKxGDunllw4YUtxciZp8bmg5MueZRCB4HatSHmqxM2B8uI9sJJOqQAWw3snShExsPNnGbToswUMwA==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (40, 'ivanov', 'doc', 0, 'U2FsdGVkX19qgbOQExreZu8n05sXHZwj1QCUrmM+E8soKlUTtF2ZlE8RszQuplAr4wo4k+zfD5Wbtm/zWCXjgUTtp0stb+v2ov3eV/skJIxJocd9ecqHLw==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (41, 'ivanov', 'doc', 1, 'U2FsdGVkX19uef0gkz9PlCuQqttA88pqBFPrcPU4eBRbhBr5LWAAN4t5wdaNEMe1lvcS95nWNnenbHOSXGjYGw==');
INSERT INTO `access_list` (`id`, `owner`, `login`, `page`, `crypto`) VALUES (42, 'ivanov', 'doc', 3, 'U2FsdGVkX1+ddnJ+FTYCkgG54qJph3sL56/lnYs25QebvJyZeEVZt0YX0G4Kw4fASA15IcmoruKgBsMu1aTq6Q==');

-- --------------------------------------------------------

-- 
-- Структура таблицы `client_pages`
-- 

DROP TABLE IF EXISTS `client_pages`;
CREATE TABLE `client_pages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `page` int(5) unsigned NOT NULL COMMENT 'Номер страницы',
  `temp_page` tinyint(1) default '0' COMMENT 'Признак временной страницы',
  `login` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Пользователь временной страницы',
  `exp_date` datetime default NULL COMMENT 'Срок действия временной страницы',
  `check` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Контрольная строка шифрования',
  `title` varchar(512) collate utf8_unicode_ci NOT NULL COMMENT 'Заголовок',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Примечание',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`,`page`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Дополнительные страницы данных клиента' AUTO_INCREMENT=21 ;

-- 
-- Дамп данных таблицы `client_pages`
-- 

INSERT INTO `client_pages` (`id`, `owner`, `page`, `temp_page`, `login`, `exp_date`, `check`, `title`, `remark`) VALUES (10, 'user1', 1, 0, NULL, NULL, 'U2FsdGVkX1+Ns9w9APkTOatZZPli89aYOrFp7P8cdEFoPE2R0udX6jYCaB5fTG8/RaHyikdrDtg=', 'U2FsdGVkX1+n+m6eibBJpjrgTx686okYQZ0hq7mTnZOBNyxrRzFCFwx9KMhLT+gF', 'U2FsdGVkX1931tN8J9WnZ/MaaFQ4sbsz00HuNo2UqcRf4qwB1EP+dxK5Vku81P8hmar4dDpT9dDkcXjm3QfMSFp2nDWhST/Xzn1Orbd6kztO0xR4Lc+9Cg==');
INSERT INTO `client_pages` (`id`, `owner`, `page`, `temp_page`, `login`, `exp_date`, `check`, `title`, `remark`) VALUES (17, 'ivanov', 1, 0, NULL, NULL, 'U2FsdGVkX196cdoTuDuiVbJ7nGXs95Im7iU1x+hMur9/6PkRqT8Dz6X1dFWzG+WeN3Zz0o7W1+w=', 'U2FsdGVkX1+56CfGkUHDXcnGOaM5uvw1HC+st/U9EMwQ7oEOZNPjny4Sur/kq0OqyMvkzLgntXjChaZ8D5auf307r9wY1Z0r6YRbCU61x+8Dw7MMrO92Q2Rqh1nrCiHHipaOZ2AcWcE=', 'U2FsdGVkX19jE4eU7CBJ6BGGX1nwLsmY9P4PVLKnretS/oEtFd4fUadnGPLLpRMvmd+LYW7/AGgP+Sf0uywonBy9wRI8ztR8iAN/4/1U7wAD0zfCgOEle1OBVR70GLtyLsZcQdSSA27j0LRidu/2J9ewdHhXk8Aw7eNXVGBjA/FDn3yGo0HEqOosugjhJgMrOQjvpFZYKg6v0dwS5jaxAg==');
INSERT INTO `client_pages` (`id`, `owner`, `page`, `temp_page`, `login`, `exp_date`, `check`, `title`, `remark`) VALUES (18, 'ivanov', 2, 0, NULL, NULL, 'U2FsdGVkX1/Ddyjgl3v0MXbkDg6AqQmYnW99p+Xe2AmFmDSRGUazaRbUg9UfPmL1tIZqzbGmluE=', 'U2FsdGVkX1/uoM8ncmxwvuirZR2Ygo5snuBPKDMbD4qrhxzhr1PHyvExigImSr1Z', '');
INSERT INTO `client_pages` (`id`, `owner`, `page`, `temp_page`, `login`, `exp_date`, `check`, `title`, `remark`) VALUES (19, 'ivanov', 3, 0, NULL, NULL, 'U2FsdGVkX1/BoiiGtwk8nDSQSeHcA1VIYE1duhbpfF6ssdeOJF5fMAUs1jicWh2z+JTp1iqZiwc=', 'U2FsdGVkX19RI7LYoWX8yAYOnxgSjygOkq+DsNIt6iRtclkUKQdGMf2plbxzri3yZB1XgDK/IvFt3u74TCfT2nSUFH+RrdFE1UoP56HcWAI=', '');

-- --------------------------------------------------------

-- 
-- Структура таблицы `client_page_main`
-- 

DROP TABLE IF EXISTS `client_page_main`;
CREATE TABLE `client_page_main` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `login` varchar(32) collate utf8_unicode_ci default NULL COMMENT 'Пользователь временной копии',
  `check` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Контрольная строка шифрации',
  `name_f` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Фамилия',
  `name_i` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Имя',
  `name_o` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Отчество',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Главные страницы пользователей' AUTO_INCREMENT=8 ;

-- 
-- Дамп данных таблицы `client_page_main`
-- 

INSERT INTO `client_page_main` (`id`, `owner`, `login`, `check`, `name_f`, `name_i`, `name_o`, `remark`) VALUES (2, 'mad', NULL, 'U2FsdGVkX18INV17IjgL+BPjwqX5pzfovbLV8cIilIk+rOv1KdCejzGwtUsaHxzy5uFhvSQQNsY=', 'U2FsdGVkX18LnGcGzjfnFFLjGriumr30t99/8BBa3b8=', 'U2FsdGVkX1+o00FRdWXckxy/lCZz+JoYQJhjf4VrlKc=', 'U2FsdGVkX1892E0jwxzPyIaTKZ18hkmPcuTiQUJA3o9zzvHfUgsaCw==', 'U2FsdGVkX1+PC+SLrmdiOF1NK7bZ0PngoHWlQk3J1W1jIgrI2pXq3lX25vnHu9c2SVEbPf3dEjynhv9aIxDQgiEjZxjrAlR9');
INSERT INTO `client_page_main` (`id`, `owner`, `login`, `check`, `name_f`, `name_i`, `name_o`, `remark`) VALUES (4, 'user1', NULL, 'U2FsdGVkX1/TqiUGs2TSd4hALo+QEjOcf1jkD6CmiAiSTOQQf6PyIk/bUodDEFvah7Zd37T5E9o=', 'U2FsdGVkX1/b4Ex77R6y5GIIbhSXg/a8VeZLa5FH8pgOd0vZPNMwIQ==', 'U2FsdGVkX19TYcqomuoD0Zi24SRln4Og52qDrCR11mw=', 'U2FsdGVkX1+jfJMLUeaBqilm6LlYei8gol4xX6RtyO0=', 'U2FsdGVkX19BziGssQzYsTmGBtZNKIoG');
INSERT INTO `client_page_main` (`id`, `owner`, `login`, `check`, `name_f`, `name_i`, `name_o`, `remark`) VALUES (5, 'crash', NULL, 'U2FsdGVkX1+EmyGBm6KHqKYEJdrIQw7j0MuL1rS2KFeN8IcY5BJ87EpKiajPssGqkvyRRSg0u/k=', 'U2FsdGVkX19QoCxOf+cSEPZflv8Yl2CJCZmYk1MwnSIUjCKIEQdChA==', 'U2FsdGVkX181cIGOuVANbjPHIv/fknN2VUYOGFLGO9Y=', 'U2FsdGVkX1+PE6sz3yXvfhlmFumCxSJep+fXRH1gFujW/m7Dh6x8/g==', 'U2FsdGVkX18nQntAJ8AUWQdyt67+Ap2cRPVqGgGUISru+zdIEKHDM1Ocgr0SyVq/SP8ouV7ioA+EC+6+UZgRqHjK4+nf3lk/V/FkGaLSabnL2AM1XMimPmyHL38Ntfc8avE+gHcRsNfuEJippKZb7lDLRm7L+BLtw2+/4vJYYF/ayZm8fXirB4hbT0asId5Z');
INSERT INTO `client_page_main` (`id`, `owner`, `login`, `check`, `name_f`, `name_i`, `name_o`, `remark`) VALUES (6, 'ivanov', NULL, 'U2FsdGVkX18aVVVeynTtJj30u6wjjIfBZ8hJx6UjpN20c3nmSPHzfO08Am/k9KttYkygPqf36gI=', 'U2FsdGVkX19/OhpazPu3PHyHHGLA4QXFXmFWOPIQE58=', 'U2FsdGVkX1/Y2vZ5AhCO/comcQTxrc9iqe3T6/1HEmw=', 'U2FsdGVkX19+iYDO1W8FJ1o/Q+FhujnSRIcDoxx96iJV5ldx8xCB/Q==', 'U2FsdGVkX18ygVWoNCak0KuIdxdaYVa0S782M3edw2sLLp2a0panbP/8pCzbG+yztFX4hk6ow19rdKPpRs4g35B8et+his8FVQgN8yk8OOYpFLKqKtvu6BcE4txajimrqxcFI4AV1eCJtMLL/ogIkfijs5yBogUj4N0p5CVLXPyJSgsPDVqgTpzWVGInN8w16XzHfz3rVm0=');
INSERT INTO `client_page_main` (`id`, `owner`, `login`, `check`, `name_f`, `name_i`, `name_o`, `remark`) VALUES (7, 'sidorov', NULL, 'U2FsdGVkX18gqeT1bCjxlX8usNpPJRMp25VXwVCF0X1FkgPZPjFDf86vsR+7xo8g8FNsKSsaHLY=', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

-- 
-- Структура таблицы `doctor_notes`
-- 

DROP TABLE IF EXISTS `doctor_notes`;
CREATE TABLE `doctor_notes` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `client` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Пациент',
  `check` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Контрольная строка шифрования',
  `category` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Категория',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Заметки',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`,`client`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Заметки врача по пациенту' AUTO_INCREMENT=5 ;

-- 
-- Дамп данных таблицы `doctor_notes`
-- 

INSERT INTO `doctor_notes` (`id`, `owner`, `client`, `check`, `category`, `remark`) VALUES (1, 'doc', 'mad', '', 'U2FsdGVkX1+FpFinYOthyuWhMuFw9/XYeptPYwgqO/P2HP9GHmiE+QjlTOlnmn8CZMrQvOnh+INU7jSsXymGgd91IjBiKnrynsiM/5idauOjQVnJCwoC+w==', '');
INSERT INTO `doctor_notes` (`id`, `owner`, `client`, `check`, `category`, `remark`) VALUES (2, 'doc', 'user1', '', 'U2FsdGVkX1/0PI4qkGkBhhHMERE1Qqzets3UJEosgtQec1h+lXLkIG9vzusUMOi+AS4LBjKwOmc/oY3pufI+6KkWgV3MR0aItl/2v0+/k4wN5qzYC8pW7fdqNWNat5mB', 'U2FsdGVkX18GT1Aod61hRCd8/+j2Bx7P0XfDSK5AQJiSVUb9ZIVmhbCD6U42XVys4YgbKdyblsI=');
INSERT INTO `doctor_notes` (`id`, `owner`, `client`, `check`, `category`, `remark`) VALUES (3, 'doc', 'crash', '', NULL, NULL);
INSERT INTO `doctor_notes` (`id`, `owner`, `client`, `check`, `category`, `remark`) VALUES (4, 'doc', 'ivanov', '', 'U2FsdGVkX1+Rb+Ep7g+mX9kz7MH3fx8RocCMpR3LrXHnO5wpm7GhYZeMt6ETY+KL/bK8MhfVtT6AKHT6eXZ9MBdyIBEgQ83c', 'U2FsdGVkX1+MdCv6U1YHZBnzwFMpyVkdRcKBVE6nGfyy4Zob0yknOr/noGkJ/7ffWqIC38T3hik0NlmP9w2ZgTYMNh9abL0TwEThz0vegV2kQLnCY57jiuAQ9u3ADscF5PFCQ4GIZ0OGMNFs9qxRIqJXVWINxIVTHvyU1NkEGMpcEnO01laglA==');

-- --------------------------------------------------------

-- 
-- Структура таблицы `doctor_page_main`
-- 

DROP TABLE IF EXISTS `doctor_page_main`;
CREATE TABLE `doctor_page_main` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `owner` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Владелец',
  `name_f` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Фамилия',
  `name_i` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Имя',
  `name_o` varchar(128) collate utf8_unicode_ci default NULL COMMENT 'Отчество',
  `speciality` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Специальность',
  `remark` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Примечание',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- 
-- Дамп данных таблицы `doctor_page_main`
-- 

INSERT INTO `doctor_page_main` (`id`, `owner`, `name_f`, `name_i`, `name_o`, `speciality`, `remark`) VALUES (1, 'doc', 'Иванов', 'Сергей', 'Петрович', 'Physiatrist,Therapeutist,', 'Что-то о себе');
INSERT INTO `doctor_page_main` (`id`, `owner`, `name_f`, `name_i`, `name_o`, `speciality`, `remark`) VALUES (2, 'fis', 'Пилюлькин', 'Теодор', 'Феоктистович', 'Therapeutist,Traumatologist,', 'Имею огромный опыт лечения ударных травм костно-мышечного аппарата');
INSERT INTO `doctor_page_main` (`id`, `owner`, `name_f`, `name_i`, `name_o`, `speciality`, `remark`) VALUES (3, 'petrov', 'Петров ', 'Петр', 'Петрович', 'Physiatrist,Traumatologist,', 'Имею большой опыт восстановления после сложных переломов и сочетанных травм опорно-двигательного аппарата.@@Кандидат медицинских наук.@@Заведующий отделением физиотерапии Московской областной клинической больницы N3.');

-- --------------------------------------------------------

-- 
-- Структура таблицы `messages`
-- 

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `receiver` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Получатель',
  `sender` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Отправитель',
  `sent` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Дата/время создания',
  `type` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Тип сообщения',
  `text` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Видимый текст',
  `details` varchar(2048) collate utf8_unicode_ci default NULL COMMENT 'Прилагаемая техническая спецификация',
  `deleted` datetime default NULL COMMENT 'Тайм-штамп удаления',
  PRIMARY KEY  (`id`),
  KEY `receiver` (`receiver`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Сообщения участникам' AUTO_INCREMENT=5 ;

-- 
-- Дамп данных таблицы `messages`
-- 

INSERT INTO `messages` (`id`, `receiver`, `sender`, `sent`, `type`, `text`, `details`, `deleted`) VALUES (1, 'doc', 'mad', '2015-07-12 13:49:40', 'CLIENT_ACCESS_INVITE', 'U2FsdGVkX1+biWBhTLqHrvYyPQi2RMAC+MTr8PnsH82LlmwAifHhNm2LItp9LoLfQA7LOETk5CPR4q8Ro/II+A==', 'U2FsdGVkX19hzrsBd9eIFBdl4+3fXUJwSyk0sxo219ILm508T7YaBQYSpBaPyMKCeJwHIthgEOSTsqOAi4lnyQNJY73AHuRFR2UfJoE+H4f92Tkd5AAvqbiMXmoWzbbhlB/vtNGn3uP+NeVm/vqSC4irpo389gcpGcNHUNIDPZs=', NULL);
INSERT INTO `messages` (`id`, `receiver`, `sender`, `sent`, `type`, `text`, `details`, `deleted`) VALUES (2, 'doc', 'user1', '2015-07-12 13:54:57', 'CLIENT_ACCESS_INVITE', 'U2FsdGVkX18VhCNeh5Th93hZq/ohCS0vtLSoelGHkNgdhGcJv482fnsGcKmSYhm9MxffvxHfeIrnv8e+wYj/t1QYcbVfQdmT9HZj6TPm3wkXvY4AAVIeRawTeaICi210EhpFMCI14J8MZtx3h5HuZQ==', 'U2FsdGVkX1+F4vIRby/9/9otKZgFb09pOeVU86vCr0I1x93rBW9VtgTjLgpWuNCpiTnBsSmXc9gbuPw8GJK5zZS1pOx32r/tNisdrJeznTl4L6+uvb78YcEr2ANdZediG7+0n132R09Krj+EpWjO4A==', NULL);
INSERT INTO `messages` (`id`, `receiver`, `sender`, `sent`, `type`, `text`, `details`, `deleted`) VALUES (3, 'doc', 'crash', '2015-08-01 13:53:37', 'CLIENT_ACCESS_INVITE', 'U2FsdGVkX1+Prr6qkbjy1FS6CokNG3E3tyOJxkpvioY9srr3TT5n4VzrXs0ugXc2hwa9n7CgzYZZs3pojwb42o878rO757/jrVDJjFvTnmJe1cM06ypsnfp6XBYycbSP', 'U2FsdGVkX18PusvZ4Vzq4IkgZ9ryb/kj/J6bd35iDYUUnOkBQGY7xO+HiywloqsTURUTldFNADxpr/Fq1fE16eTqg/BIlc+5l45uNu529ytpXTtydMFPCk6pI725xVCTkWNeHUwTJ+xcfRcvp+c25nP+OigcHeUGMEUNeJRLrMati3OfCGsBUW0Qo2+RL/4u', NULL);
INSERT INTO `messages` (`id`, `receiver`, `sender`, `sent`, `type`, `text`, `details`, `deleted`) VALUES (4, 'doc', 'ivanov', '2015-08-16 12:58:49', 'CLIENT_ACCESS_INVITE', 'U2FsdGVkX1/HN1rg3Gd7lJ+oSXHfuT0rzQGIlJ/bDHl8LFeo5YplwzPOBod9FM2xN88CjQM6pBx/hxIwqn8lOZC9NqO/mrx+UIsLvzvnV4Q+RqxwbAYk54mQu+v3jKZDhtzrJIzaUKcE8dj7OYZJgVDKfzgA77wh1iJCa3pml5XUSY+WTngM2GQyWkh/jCAN5NdsfkXVDQuQIQ4BTLPy6NmceVydsF9nP5Sfkvwf31GYFBuWmG2NYHeUe5705jhUyUYX+bqjD8Zi3Q0FkLeBuyBOWxAMtvgzGRB+3GsrEWTxSLR1tph0qlDnimdQpMzou8t76xbKWrsmP8aoJ8x24Re3TBdLYlR/0USc62VmO8V7nZA9VBlGkW0M1nACfgSFhk2Bdg+yj6KF+HOAUkNLLj0xe7dBWk39S+bn4OCSGiA=', 'U2FsdGVkX18GI+tkTvFM2U13iQkbfIPzZZ3j86Mpgk2VSdMN9Un6o3nJGqNMddocvRS0FTHBBVZdS/0i06TXcdpGg2zQTI31oCoocGurKfRM07DewXJzyLEiQs+bhUMdcBxj48MVhZ/dU3wvmtRm7HK94cTMximIcc/TYk4GnEfIWgqrafBTv/WXTTzC1N07UkkIwPH1yYeLJxolXz8XITGxIl4CbKXpqurz4mEQczcf3gejvfX5pr3hl08WW0Onvn4obBEfM3RPk0AgTIgsPquWKH19nYUe5f5XSnmaj6IQ4qnshfS5wbiC6qwLqVscvKGOu1jrLX4b0OQtWnUySg==', NULL);

-- --------------------------------------------------------

-- 
-- Структура таблицы `ref_doctor_specialities`
-- 

DROP TABLE IF EXISTS `ref_doctor_specialities`;
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
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Physiatrist', 'RU', 'Физиотерапевт');
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Therapeutist', 'RU', 'Терапевт');
INSERT INTO `ref_doctor_specialities` (`code`, `language`, `name`) VALUES ('Traumatologist', 'RU', 'Травматолог');

-- --------------------------------------------------------

-- 
-- Структура таблицы `ref_messages_types`
-- 

DROP TABLE IF EXISTS `ref_messages_types`;
CREATE TABLE `ref_messages_types` (
  `code` varchar(32) collate utf8_unicode_ci NOT NULL COMMENT 'Код типа сообщения',
  `language` varchar(16) collate utf8_unicode_ci NOT NULL COMMENT 'Язык описания',
  `name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'Название типа сообщения',
  UNIQUE KEY `code` (`code`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Справочник типов сообщений';

-- 
-- Дамп данных таблицы `ref_messages_types`
-- 

INSERT INTO `ref_messages_types` (`code`, `language`, `name`) VALUES ('CLIENT_ACCESS_INVITE', 'RU', 'Приглашение от пациента');

-- --------------------------------------------------------

-- 
-- Структура таблицы `sessions`
-- 

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `login` varchar(32) collate utf8_unicode_ci NOT NULL,
  `session` varchar(32) collate utf8_unicode_ci NOT NULL,
  `started` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `session` (`session`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список активных сессий' AUTO_INCREMENT=78 ;

-- 
-- Дамп данных таблицы `sessions`
-- 


-- --------------------------------------------------------

-- 
-- Структура таблицы `users`
-- 

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `login` varchar(32) collate utf8_unicode_ci NOT NULL,
  `password` varchar(32) collate utf8_unicode_ci NOT NULL,
  `email` varchar(128) collate utf8_unicode_ci default NULL,
  `sign_p_key` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Публичный ключ подписи',
  `sign_s_key` varchar(256) collate utf8_unicode_ci default NULL COMMENT 'Секретный ключ подписи',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Список логинов пользователей' AUTO_INCREMENT=18 ;

-- 
-- Дамп данных таблицы `users`
-- 

INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (5, 'mad', '538e', 'oldmadjackal@gmail.com', 'RSAKlBZUIHDDuqxwyL9G5clzD9CetVqU9Vf', 'U2FsdGVkX18HGirMLw9i2nCm8YD03Pv55SnZdm2uiw8gDSk4MwrbAvbzopE+/EpxIaISwuYVuq8=');
INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (8, 'doc', 'a09b', 'oldmadjackal@gmail.com', 'RSAWksqOiuRm4BvB6h0akAwawvyrGsIStFQ', 'U2FsdGVkX1/IWAwDVbXe55Zz/2tEcugkkvl/7Ia9jbkznYOjw0+rtjrWf1g7fC/sbIdxRoADM8E=');
INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (10, 'user1', '4c9e', 'oldmadjackal@gmail.com', 'RSAO8TLL5UpotvrxwruUdvrpld6KpEhSPlW', 'U2FsdGVkX1+a5uMbrBdskRNP4m2fhXzm5YN5eFe0dMvJioL3FDP4YEzXFJd/o4GEafy7ch5CqOI=');
INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (11, 'fis', '7ab8', 'oldmadjackal@gmail.com', 'RSAEXnBKBtuSTxgOYRYkMCragZM6fSjoCK8', 'U2FsdGVkX186uzt6oqU0pdg4Osblf2fcERZi5ANojHJrtm90lDBVlfwSG6XlhvykwRa5+7+b/jA=');
INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (12, 'crash', 'caa9', 'oldmadjackal@gmail.com', 'RSAt1S2EmJTtN6Ft5LXdpxlfcNlUPYZitqo', 'U2FsdGVkX18b8pTXPlIRc1xNUl7BVUfn0UmamF6Xz9+lQneyyMc1yBySZcwVIgemjjzvVzuZZs8=');
INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (15, 'ivanov', 'dfe6', 'ivanov89@gmail.com', 'RSATDFDfPfsIILeYo7YbvzqGheHuq9aAyYe', 'U2FsdGVkX180NWNe8gHgll6nDCiZ3PiOwHH+fS4BIgc/ZLXktkg1/6snsDkcjq57BpTMKRGbVE8=');
INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (16, 'sidorov', 'cd3a', 'oldmadjackal@gmail.com', 'RSABm7QhIM3QyELxSuhg0OdRBO6hA6LQup6', 'U2FsdGVkX1/wjlbEc7TtFH9sgBSsJ0d4SPYI+SxYbZyuodMk6LlATB63b2o4B0XDjpgvYu4LB90=');
INSERT INTO `users` (`id`, `login`, `password`, `email`, `sign_p_key`, `sign_s_key`) VALUES (17, 'petrov', '396c', 'petrov@gmail.com', 'RSAfPSueoPVaSsMsjthoCxwx7PdaDNRPUnO', 'U2FsdGVkX18EcQmb/II6EbB+EWp1QJWWlDo9Qgy25JGUcCfWFrQw6+Svco/ZAGDVhqSOsTTqW24=');
