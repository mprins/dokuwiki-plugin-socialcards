<?php
/*
 * Copyright (c) 2013 Mark C. Prins <mprins@users.sf.net>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

/**
 * Options for the socialcards plugin
 *
 * @license BSD license
 * @author  Mark C. Prins <mprins@users.sf.net>
 */
$meta['twitterName'] = array ('string');
$meta['fallbackImage'] = array ('string');
$meta['languageTerritory'] = array ('multichoice','_choices' => array('be_BY','bn_IN','cs_CZ','da_DK','de_CH','de_DE','el_GR','en_AU','en_CA','en_GB','en_HK','en_IN','en_NZ','en_PH','en_SG','en_US','en_ZA','es_AR','es_BO','es_CL','es_CO','es_CR','es_DO','es_EC','es_ES','es_GT','es_HN','es_MX','es_NI','es_PA','es_PE','es_PR','es_PY','es_SV','es_US','es_UY','es_VE','fi_FI','fr_BE','fr_CA','fr_CH','fr_FR','gu_IN','he_IL','hi_IN','hr_HR','hu_HU','id_ID','it_CH','it_IT','iw_IL','ja_JP','kk_KZ','ko_KR','mr_IN','ms_MY','nl_NL','no_NO','pa_IN','pl_PL','pt_BR','pt_PT','ro_RO','ru_RU','sk_SK','sl_SI','sv_SE','ta_IN','te_IN','th_TH','tr_TR','zh_CN','zh_HKS','zh_HKT','zh_SGS','zh_TW'));
$meta['fbAppId'] = array ('string');
