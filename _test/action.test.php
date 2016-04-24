<?php
/*
 * Copyright (c) 2016 Mark C. Prins <mprins@users.sf.net>
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
 * Action tests for the socialcards plugin.
 *
 * @group plugin_socialcards
 * @group plugins
 */
class action_plugin_socialcards_test extends DokuWikiTest {

    protected $pluginsEnabled = array('socialcards');

    public function setUp() {
        global $conf;

        parent::setUp();

        $conf ['plugin']['socialcards']['twitterName'] = '@twitterName';
        $conf ['plugin']['socialcards']['twitterUserName'] = '@twitterUserName';
    }

    public function testHeaders() {
        $request = new TestRequest();
        $response = $request->get(array('id'=>'wiki:dokuwiki'), '/doku.php');

        print_r($response);

        $this->assertTrue(
            strpos($response->getContent(), 'DokuWiki') !== false,
            'DokuWiki was not a word in the output'
        );

        // check twitter meta headers
        $this->assertEquals('DokuWiki',
                        $response->queryHTML('meta[name="twitter:title"]')->attr('content'));
        $this->assertEquals('@twitterName',
                        $response->queryHTML('meta[name="twitter:site"]')->attr('content'));
        $this->assertEquals('summary',
                        $response->queryHTML('meta[name="twitter:card"]')->attr('content'));
        $this->assertEquals('@twitterUserName',
                        $response->queryHTML('meta[name="twitter:creator"]')->attr('content'));
        $this->assertEquals('http://wiki.example.com/./lib/exe/fetch.php?media=wiki:dokuwiki-128.png',
                        $response->queryHTML('meta[name="twitter:image"]')->attr('content'));
        $this->assertEquals('',
                        $response->queryHTML('meta[name="twitter:image:alt"]')->attr('content'));

        // check og meta headers
        $this->assertEquals('My Test Wiki',
                        $response->queryHTML('meta[property="og:site_name"]')->attr('content'));
        $this->assertEquals('en_US',
                        $response->queryHTML('meta[property="og:locale"]')->attr('content'));
        $this->assertEquals('http://wiki.example.com/./lib/exe/fetch.php?media=wiki:dokuwiki-128.png',
                        $response->queryHTML('meta[property="og:image"]')->attr('content'));
        $this->assertEquals('DokuWiki',
                        $response->queryHTML('meta[property="og:title"]')->attr('content'));
        $this->assertEquals('article',
                        $response->queryHTML('meta[property="og:type"]')->attr('content'));
    }
}
