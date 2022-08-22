CREATE TABLE IF NOT EXISTS `PREFIX_ambjolisearch_synonyms` (
	  `id_synonym` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `synonym` varchar(15) NOT NULL,
	  `id_word` int(10) unsigned NOT NULL,
	  PRIMARY KEY (`id_synonym`),
	  UNIQUE KEY `unq_word_synonym` (`id_word`,`synonym`) USING BTREE,
	  KEY `id_word` (`id_word`),
	  KEY `idx_synonym` (`synonym`),
	  KEY `id_word_synonym` (`id_word`,`synonym`) USING BTREE
	);
CREATE TABLE IF NOT EXISTS `PREFIX_amb_search_index` (
      `id_word` int(10) NOT NULL AUTO_INCREMENT,
      `id_shop` int(11) NOT NULL DEFAULT "1",
      `id_lang` int(10) NOT NULL,
      `word` varchar(15) NOT NULL,
      `weight` varchar(45) DEFAULT NULL,
      `id_product` int(10) DEFAULT NULL,
      PRIMARY KEY (`id_word`)
    );