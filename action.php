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


if (!defined('DOKU_INC'))
	die();
if (!defined('DOKU_PLUGIN'))
	define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . 'action.php';
/**
 * DokuWiki Plugin socialcards (Action Component).
 *
 * @license BSD license
 * @author  Mark C. Prins <mprins@users.sf.net>
 */
class action_plugin_socialcards extends DokuWiki_Action_Plugin {

	/**
	 * Register our callback for the TPL_METAHEADER_OUTPUT event.
	 *
	 * @param $controller Doku_Event_Handler
	 * @see DokuWiki_Action_Plugin::register()
	 */
	public function register(Doku_Event_Handler $controller) {
		$controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this,
				'handle_tpl_metaheader_output');
	}

	/**
	 * Retrieve metadata and add to the head of the page using appropriate meta
	 * tags.
	 *
	 * @global string $ID page id
	 * @global array $conf global wiki configuration
	 * @global array $INFO
	 * @param Doku_Event $event the DokuWiki event. $event->data is a two-dimensional
	 * array of all meta headers. The keys are meta, link and script.
	 * @param unknown_type $param the parameters passed to register_hook when this
	 * handler was registered
	 *
	 * @see http://www.dokuwiki.org/devel:event:tpl_metaheader_output
	 */
	public function handle_tpl_metaheader_output(Doku_Event &$event, $param) {
		global $ID, $conf, $INFO;

		// twitter card, see https://dev.twitter.com/docs/cards
		$event->data['meta'][] = array('name' => 'twitter:card',
				'content' => "summary",);
		$event->data['meta'][] = array('name' => 'twitter:url',
				'content' => wl($ID, '', true),);
		$event->data['meta'][] = array('name' => 'twitter:title',
				'content' => p_get_metadata($ID, 'title', true),);
		$desc = p_get_metadata($ID, 'description', true);
		if (!empty($desc)) {
			$desc = str_replace("\n", " ", $desc['abstract']);
			$event->data['meta'][] = array('name' => 'twitter:description',
					'content' => $desc,);
		}
		$event->data['meta'][] = array('name' => 'twitter:site',
				'content' => $this->getConf('twitterName'),);
		//twitter:site:id
		$event->data['meta'][] = array('name' => 'twitter:creator',
				'content' => $this->getConf('twitterName'),);
		//twitter:creator:id
		$event->data['meta'][] = array('name' => 'twitter:image',
				'content' => $this->getImage(),);

		// opengraph, see http://ogp.me/
		//
		// to make this work properly the template should be modified adding the
		// namespaces for a (x)html 4 template make html tag:
		//
		// <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="nl"
		//		xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#"
		//		xmlns:article="http://ogp.me/ns/article#" xmlns:place="http://ogp.me/ns/place#">
		//
		// and for a (x)html 5 template make head tag:
		//
		// <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article# place: http://ogp.me/ns/place#">

		// og namespace http://ogp.me/ns#
		$event->data['meta'][] = array('property' => 'og:locale',
				'content' => $this->getConf('languageTerritory'),);
		$event->data['meta'][] = array('property' => 'og:site_name',
				'content' => $conf['title'],);
		$event->data['meta'][] = array('property' => 'og:url',
				'content' => wl($ID, '', true),);
		$event->data['meta'][] = array('property' => 'og:title',
				'content' => p_get_metadata($ID, 'title', true),);
		if (!empty($desc)) {
			$event->data['meta'][] = array('property' => 'og:description',
					'content' => $desc,);
		}
		$event->data['meta'][] = array('property' => 'og:type',
				'content' => "article",);
		$event->data['meta'][] = array('property' => 'og:image',
				'content' => $this->getImage(),);

		// article namespace http://ogp.me/ns/article#
		$_dates = p_get_metadata($ID, 'date', true);
		$event->data['meta'][] = array('property' => 'article:published_time',
				'content' => dformat($_dates['created']),);
		$event->data['meta'][] = array('property' => 'article:modified_time',
				'content' => dformat($_dates['modified']),);
		$event->data['meta'][] = array('property' => 'article:author',
				'content' => $INFO['editor'],);
		// $event->data['meta'][] = array('property' => 'article:author','content' => p_get_metadata($ID,'creator',true),);
		// $event->data['meta'][] = array('property' => 'article:author','content' => p_get_metadata($ID,'user',true),);
		$_subject = p_get_metadata($ID, 'subject', true);
		if (!empty($_subject)) {
			if (!is_array($_subject)) {
				$_subject = array($_subject);
			}
			foreach ($_subject as $tag) {
				$event->data['meta'][] = array('property' => 'article:tag',
						'content' => $tag,);
			}
		}

		// place namespace http://ogp.me/ns/place#
		$geotags = p_get_metadata($ID, 'geo', true);
		$lat = $geotags['lat'];
		$lon = $geotags['lon'];
		if (!(empty($lat) && empty($lon))) {
			$event->data['meta'][] = array('property' => 'place:location:latitude',
					'content' => $lat,);
			$event->data['meta'][] = array('property' => 'place:location:longitude',
					'content' => $lon,);
			// place:location:altitude (string) Altitude of location, facebook wants feet...
		}

		/* these are not valid for the GeoPoint type..

		$region=$geotags['region'];
		$country=$geotags['country'];
		$placename=$geotags['placename'];
		if (!empty($region))    {$event->data['meta'][] = array('property' => 'place:location:region',		'content' => $region,);}
		if (!empty($placename)) {$event->data['meta'][] = array('property' => 'place:location:locality',	'content' => $placename,);}
		if (!empty($country))   {$event->data['meta'][] = array('property' => 'place:location:country-name','content' => $country,);}
		*/

		// optional facebook app ID
		$appId = $this->getConf('fbAppId');
		if (!empty($appId)) {
			$event->data['meta'][] = array('property' => 'fb:app_id',
					'content' => $appId,);
		}
	}

	/**
	 * Gets the canonical image path for this page.
	 *
	 * @global string $ID page id
	 * @return string the url to the image to use for this page
	 */
	private function getImage() {
		global $ID;
		$rel = p_get_metadata($ID, 'relation', true);
		$img = $rel['firstimage'];

		if (empty($img)) {
			$img = $this->getConf('fallbackImage');
			if (substr($img, 0, 4 ) === "http") {
				// don't use ml() as this results in a HTTP redirect after hitting the wiki
				return $img;
			}
		}

		return ml($img, array(), true, '&amp;', true);
	}

}
