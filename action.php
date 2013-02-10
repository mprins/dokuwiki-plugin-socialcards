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
 * DokuWiki Plugin socialcards (Action Component)
 *
 * @license BSD license
 * @author  Mark C. Prins <mprins@users.sf.net>
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_socialcards extends DokuWiki_Action_Plugin {

	public function register(Doku_Event_Handler &$controller) {
		$controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handle_tpl_metaheader_output');
	}

	/**
	 * retrieve metadata and add to the head of the page using appropriate meta tags.
	 * @param Doku_Event $event the DokuWiki event. $event->data is a two-dimensional
	 * 	array of all meta headers. The keys are meta, link and script.
	 * @param unknown_type $param
	 */
	public function handle_tpl_metaheader_output(Doku_Event &$event, $param) {
		/*
		 * see: http://www.dokuwiki.org/devel:event:tpl_metaheader_output
		 * $data is a two-dimensional array of all meta headers. The keys are meta, link and script.
		 */
		global $ID;
		
		// twitter card, see https://dev.twitter.com/docs/cards
		$event->data['meta'][] = array('name' => 'twitter:card',	'content' => "summary",);
		$event->data['meta'][] = array('name' => 'twitter:url',		'content' => wl($ID,'',true),);
		$event->data['meta'][] = array('name' => 'twitter:title',	'content' => p_get_metadata($ID,'title',true),);
		$desc = p_get_metadata($ID,'description',true);
		dbg($desc);
		
		if (!empty($desc)) {
			$event->data['meta'][] = array('name' => 'twitter:description',
																	'content' => str_replace("\n", " ", $desc['abstract']),);
		}
		$event->data['meta'][] = array('name' => 'twitter:site',	'content' => $this->getConf('twitterName'),);
		//twitter:site:id
		$event->data['meta'][] = array('name' => 'twitter:creator',	'content' => $this->getConf('twitterName'),);
		//twitter:creator:id
		//twitter:image
	}
}
