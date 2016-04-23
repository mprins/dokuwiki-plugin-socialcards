<?php
/**
 * Action tests for the socialcards plugin
 *
 * @group plugin_socialcards
 * @group plugins
 */
class action_plugin_socialcards_test extends DokuWikiTest {

   protected $pluginsEnabled = array('socialcards');

   function setUp(){
        global $conf;

        parent::setUp();

        $conf ['plugin']['socialcards']['twitterName'] = '@twitterName';
        $conf ['plugin']['socialcards']['twitterUserName'] = '@twitterUserName';
        // $conf ['plugin']['socialcards']['fallbackImage'] = 'wiki:dokuwiki-128.png';
        // $conf ['plugin']['socialcards']['languageTerritory'] = 'en_US';
        // $conf ['plugin']['socialcards']['fbAppId'] = '';
    }

    public function testHeaders() {
        // make a request
        $request = new TestRequest();
        $response = $request->execute();

        $this->assertTrue(
            strpos($response->getContent(), 'DokuWiki') !== false,
            'DokuWiki was not a word in the output'
        );

        print_r($response);

        // check meta headers
        $this->assertEquals('',
                        $response->queryHTML('meta[name="twitter:title"]')->attr('content'));
        $this->assertEquals('@twitterName',
                        $response->queryHTML('meta[name="twitter:site"]')->attr('content'));
        $this->assertEquals('summary',
                        $response->queryHTML('meta[name="twitter:card"]')->attr('content'));
        $this->assertEquals('@twitterUserName',
                        $response->queryHTML('meta[name="twitter:creator"]')->attr('content'));
        $this->assertEquals('en_US',
                        $response->queryHTML('meta[property="og:locale"]')->attr('content'));
    }
}
