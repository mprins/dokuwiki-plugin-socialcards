<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/*
 * Copyright (c) 2013-2016 Mark C. Prins <mprins@users.sf.net>
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
 * DokuWiki Plugin socialcards (Action Component).
 *
 * @license BSD license
 * @author  Mark C. Prins <mprins@users.sf.net>
 */
class action_plugin_socialcards extends ActionPlugin
{
    /**
     * Register our callback for the TPL_METAHEADER_OUTPUT event.
     *
     * @param $controller Doku_Event_Handler
     * @see DokuWiki_Action_Plugin::register()
     */
    public function register(EventHandler $controller): void
    {
        $controller->register_hook(
            'TPL_METAHEADER_OUTPUT',
            'BEFORE',
            $this,
            'handleTplMetaheaderOutput'
        );
    }

    /**
     * Retrieve metadata and add to the head of the page using appropriate meta
     * tags unless the page does not exist.
     *
     * @param Event $event the DokuWiki event. $event->data is a two-dimensional
     *                          array of all meta headers. The keys are meta, link and script.
     * @param mixed      $param the parameters passed to register_hook when this
     *                          handler was registered (not used)
     *
     * @global array     $INFO
     * @global string    $ID    page id
     * @global array     $conf  global wiki configuration
     * @see http://www.dokuwiki.org/devel:event:tpl_metaheader_output
     */
    public function handleTplMetaheaderOutput(Event $event, $param): void
    {
        global $ID, $conf, $INFO;

        if (!page_exists($ID)) {
            return;
        }
        if (auth_quickaclcheck($ID) < AUTH_READ) {
            return;
        }

        // twitter card, see https://dev.twitter.com/cards/markup
        // creat a summary card, see https://dev.twitter.com/cards/types/summary
        $event->data['meta'][] = ['name'    => 'twitter:card', 'content' => "summary"];

        $event->data['meta'][] = ['name'    => 'twitter:site', 'content' => $this->getConf('twitterName')];

        $event->data['meta'][] = ['name'    => 'twitter:title', 'content' => p_get_metadata($ID, 'title', METADATA_RENDER_USING_SIMPLE_CACHE)];

        $desc = p_get_metadata($ID, 'description', METADATA_RENDER_USING_SIMPLE_CACHE);
        if (!empty($desc)) {
            $desc                  = str_replace("\n", " ", $desc['abstract']);
            $event->data['meta'][] = ['name'    => 'twitter:description', 'content' => $desc];
        }

        if ($this->getConf('twitterUserName') !== '') {
            $event->data['meta'][] = ['name'    => 'twitter:creator', 'content' => $this->getConf('twitterUserName')];
        }

        $event->data['meta'][] = ['name'    => 'twitter:image', 'content' => $this->getImage()];
        $event->data['meta'][] = ['name'    => 'twitter:image:alt', 'content' => $this->getImageAlt()];

        // opengraph, see http://ogp.me/
        //
        // to make this work properly the template should be modified adding the
        // namespaces for a (x)html 4 template make html tag:
        //
        // <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="nl"
        //       xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#"
        //       xmlns:article="http://ogp.me/ns/article#" xmlns:place="http://ogp.me/ns/place#">
        //
        // and for a (x)html 5 template make head tag:
        //
        // <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#
        //    article: http://ogp.me/ns/article# place: http://ogp.me/ns/place#">

        // og namespace http://ogp.me/ns#
        $event->data['meta'][] = ['property' => 'og:locale', 'content'  => $this->getConf('languageTerritory')];
        $event->data['meta'][] = ['property' => 'og:site_name', 'content'  => $conf['title']];
        $event->data['meta'][] = ['property' => 'og:url', 'content'  => wl($ID, '', true)];
        $event->data['meta'][] = ['property' => 'og:title', 'content'  => p_get_metadata($ID, 'title', METADATA_RENDER_USING_SIMPLE_CACHE)];
        if (!empty($desc)) {
            $event->data['meta'][] = ['property' => 'og:description', 'content'  => $desc];
        }
        $event->data['meta'][] = ['property' => 'og:type', 'content'  => "article"];
        $ogImage               = $this->getImage();
        $secure                = strpos($ogImage, 'https') === 0 ? ':secure_url' : '';
        $event->data['meta'][] = ['property' => 'og:image' . $secure, 'content'  => $ogImage];

        // article namespace http://ogp.me/ns/article#
        $_dates                = p_get_metadata($ID, 'date', METADATA_RENDER_USING_SIMPLE_CACHE);
        $event->data['meta'][] = ['property' => 'article:published_time', 'content'  => dformat($_dates['created'])];
        $event->data['meta'][] = ['property' => 'article:modified_time', 'content'  => dformat($_dates['modified'])];
        $event->data['meta'][] = ['property' => 'article:author', 'content'  => $INFO['editor']];
//        $event->data['meta'][] = array(
//            'property' => 'article:author',
//            'content'  => p_get_metadata($ID, 'creator', METADATA_RENDER_USING_SIMPLE_CACHE),
//        );
//        $event->data['meta'][] = array(
//            'property' => 'article:author',
//            'content'  => p_get_metadata($ID, 'user', METADATA_RENDER_USING_SIMPLE_CACHE),
//        );
        $_subject = p_get_metadata($ID, 'subject', METADATA_RENDER_USING_SIMPLE_CACHE);
        if (!empty($_subject)) {
            if (!is_array($_subject)) {
                $_subject = [$_subject];
            }
            foreach ($_subject as $tag) {
                $event->data['meta'][] = ['property' => 'article:tag', 'content'  => $tag];
            }
        }

        // place namespace http://ogp.me/ns/place#
        $geotags = p_get_metadata($ID, 'geo', METADATA_RENDER_USING_SIMPLE_CACHE);
        if (is_array($geotags)) {
            $lat = $geotags['lat'] ?? 0;
            $lon = $geotags['lon'] ?? 0;
            if (!(empty($lat) && empty($lon))) {
                $event->data['meta'][] = ['property' => 'place:location:latitude', 'content'  => $lat];
                $event->data['meta'][] = ['property' => 'place:location:longitude', 'content'  => $lon];
            }
            // see https://developers.facebook.com/docs/opengraph/property-types/#geopoint
            $alt = $geotags['alt'] ?? 0;
            if (!empty($alt)) {
                // facebook expects feet...
                $alt                   *= 3.2808;
                $event->data['meta'][] = ['property' => 'place:location:altitude', 'content'  => $alt];
            }
            /* these are not valid for the GeoPoint type..
            $region    = $geotags['region'];
            $country   = $geotags['country'];
            $placename = $geotags['placename'];
            if(!empty($region)) {
                $event->data['meta'][] = array('property' => 'place:location:region', 'content' => $region,);
            }
            if(!empty($placename)) {
                $event->data['meta'][] = array('property' => 'place:location:locality', 'content' => $placename,);
            }
            if(!empty($country)) {
                $event->data['meta'][] = array('property' => 'place:location:country-name', 'content' => $country,);
            }
            */
        }

        // optional facebook app ID
        $appId = $this->getConf('fbAppId');
        if (!empty($appId)) {
            $event->data['meta'][] = ['property' => 'fb:app_id', 'content'  => $appId];
        }
    }

    /**
     * Gets the canonical image path for this page.
     *
     * @return string the url to the image to use for this page
     * @global string $ID page id
     */
    private function getImage(): string
    {
        global $ID;
        $rel = p_get_metadata($ID, 'relation', METADATA_RENDER_USING_SIMPLE_CACHE);
        $img = $rel['firstimage'];

        if (empty($img)) {
            $img = $this->getConf('fallbackImage');
            if (strpos($img, "http") === 0) {
                // don't use ml() as this results in a HTTP redirect after
                //   hitting the wiki making the card image fail.
                return $img;
            }
        }

        return ml($img, [], true, '&amp;', true);
    }

    /**
     * Gets the alt text for this page image.
     *
     * @return string alt text
     * @global string $ID page id
     */
    private function getImageAlt(): string
    {
        global $ID;
        $rel   = p_get_metadata($ID, 'relation', METADATA_RENDER_USING_SIMPLE_CACHE);
        $imgID = $rel['firstimage'];
        $alt   = "";

        if (!empty($imgID)) {
            require_once(DOKU_INC . 'inc/JpegMeta.php');
            $jpegmeta = new JpegMeta(mediaFN($imgID));
            $tags     = ['IPTC.Caption', 'EXIF.UserComment', 'EXIF.TIFFImageDescription', 'EXIF.TIFFUserComment', 'IPTC.Headline', 'Xmp.dc:title'];
            $alt      = media_getTag($tags, $jpegmeta, "");
        }
        return htmlspecialchars($alt);
    }
}
