<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Journal;
use AppBundle\Entity\Whitelist;
use AppBundle\Utilities\PingResult;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;

/**
 * Ping service.
 */
class Ping {

    /**
     * Http client configuration.
     */
    const CONF = array(
        'allow_redirects' => true,
        'headers' => array(
            'User-Agent' => 'PkpPlnBot 1.0; http://pkp.sfu.ca',
            'Accept' => 'application/xml,text/xml,*/*;q=0.1',
        ),
    );

    /**
     * Minimum expected OJS version.
     *
     * @var string
     */
    private $minOjsVersion;

    /**
     * Doctrine instance.
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Black and white service.
     *
     * @var BlackWhiteList
     */
    private $list;

    /**
     * Guzzle http client.
     *
     * @var Client
     */
    private $client;

    /**
     * Construct the ping service.
     *
     * @param type $minOjsVersion
     * @param EntityManagerInterface $em
     * @param BlackWhiteList $list
     */
    public function __construct($minOjsVersion, EntityManagerInterface $em, BlackWhiteList $list) {
        $this->minOjsVersion = $minOjsVersion;
        $this->em = $em;
        $this->list = $list;
        $this->client = new Client();
    }

    /**
     * Set the HTTP client.
     *
     * @param Client $client
     */
    public function setClient(Client $client) {
        $this->client = $client;
    }

    /**
     * Process a ping response.
     *
     * @param Journal $journal
     * @param PingResult $result
     */
    public function process(Journal $journal, PingResult $result) {
        if (!$result->getOjsRelease()) {
            $journal->setStatus('ping-error');
            $result->addError("Journal version information missing in ping result.");
            return;
        }
        $journal->setContacted(new DateTime());
        $journal->setTitle($result->getJournalTitle());
        $journal->setOjsVersion($result->getOjsRelease());
        $journal->setTermsAccepted($result->areTermsAccepted() === 'yes');
        $journal->setStatus('healthy');
        if (version_compare($result->getOjsRelease(), $this->minOjsVersion, '<')) {
            return;
        }
        if ($this->list->isListed($journal->getUuid())) {
            return;
        }
        $whitelist = new Whitelist();
        $whitelist->setUuid($journal->getUuid());
        $whitelist->setComment("{$journal->getUrl()} added by ping.");
        $this->em->persist($whitelist);
    }

    /**
     * Ping $journal and return the result.
     *
     * @param Journal $journal
     * @return PingResult
     */
    // @codingStandardsIgnoreStart
    public function ping(Journal $journal) {
    // @codingStandardsIgnoreEnd
        try {
            $response = $this->client->get($journal->getGatewayUrl(), self::CONF);
            $result = new PingResult($response);
            $this->process($journal, $result);
            return $result;
        } catch (Exception $e) {
            $journal->setStatus('ping-error');
            $message = strip_tags($e->getMessage());
            return new PingResult(null, $message);
        }
    }

}
