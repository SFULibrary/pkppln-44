<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Journal.
 *
 * @ORM\Table(name="journal", indexes={
 * @ORM\Index(columns={"uuid", "title", "issn", "url", "email", "publisher_name", "publisher_url"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\JournalRepository")
 */
class Journal extends AbstractEntity {
    /**
     * List of states where a deposit has been sent to LOCKSSOMatic.
     *
     * @todo shouldn't this live in Deposit?
     */
    public const SENTSTATES = [
        'deposited',
        'complete',
        'status-error',
    ];

    /**
     * The URL suffix for the ping gateway.
     *
     * This suffix is appened to the Journal's URL for to build the ping URL.
     */
    public const GATEWAY_URL_SUFFIX = '/gateway/plugin/PLNGatewayPlugin';

    /**
     * Journal UUID, as generated by the PLN plugin.
     *
     * @var string
     * @ORM\Column(type="string", length=36, nullable=false)
     */
    private $uuid;

    /**
     * When the journal last contacted the staging server.
     *
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $contacted;

    /**
     * OJS version powering the journal.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true, length=12)
     */
    private $ojsVersion;

    /**
     * When the journal manager was notified.
     *
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $notified;

    /**
     * The title of the journal.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * Journal's ISSN.
     *
     * @var string
     * @ORM\Column(type="string", length=9, nullable=true)
     */
    private $issn;

    /**
     * The journal's URL.
     *
     * @var string
     *
     * @Assert\Url
     * @ORM\Column(type="string", nullable=false)
     */
    private $url;

    /**
     * The status of the journal's health.
     *
     * One of new, healthy, unhealthy, triggered, or abandoned.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $status;

    /**
     * True if a ping reports that the journal manager has accepts the terms of use.
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $termsAccepted;

    /**
     * Email address to contact the journal manager.
     *
     * @var string
     * @Assert\Email
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * Name of the publisher.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $publisherName;

    /**
     * Publisher's website.
     *
     * @var string
     * @Assert\Url
     * @ORM\Column(type="string", nullable=true)
     */
    private $publisherUrl;

    /**
     * The journal's deposits.
     *
     * @var Collection|Deposit[]
     * @ORM\OneToMany(targetEntity="Deposit", mappedBy="journal", fetch="EXTRA_LAZY")
     */
    private $deposits;

    /**
     * Construct a journal.
     */
    public function __construct() {
        parent::__construct();
        $this->status = 'healthy';
        $this->contacted = new DateTime();
        $this->termsAccepted = false;
        $this->deposits = new ArrayCollection();
    }

    /**
     * Return the journal's title or UUID if the title is unknown.
     */
    public function __toString() : string {
        if ($this->title) {
            return $this->title;
        }

        return $this->uuid;
    }

    /**
     * Set uuid.
     *
     * @param string $uuid
     *
     * @return Journal
     */
    public function setUuid($uuid) {
        $this->uuid = strtoupper($uuid);

        return $this;
    }

    /**
     * Get uuid.
     *
     * @return string
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * Set contacted.
     *
     * @return Journal
     */
    public function setContacted(DateTime $contacted) {
        $this->contacted = $contacted;

        return $this;
    }

    /**
     * Get contacted.
     *
     * @return DateTime
     */
    public function getContacted() {
        return $this->contacted;
    }

    /**
     * Set ojsVersion.
     *
     * @param string $ojsVersion
     *
     * @return Journal
     */
    public function setOjsVersion($ojsVersion) {
        $this->ojsVersion = $ojsVersion;

        return $this;
    }

    /**
     * Get ojsVersion.
     *
     * @return string
     */
    public function getOjsVersion() {
        return $this->ojsVersion;
    }

    /**
     * Set notified.
     *
     * @return Journal
     */
    public function setNotified(DateTime $notified) {
        $this->notified = $notified;

        return $this;
    }

    /**
     * Get notified.
     *
     * @return DateTime
     */
    public function getNotified() {
        return $this->notified;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Journal
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set issn.
     *
     * @param string $issn
     *
     * @return Journal
     */
    public function setIssn($issn) {
        $this->issn = $issn;

        return $this;
    }

    /**
     * Get issn.
     *
     * @return string
     */
    public function getIssn() {
        return $this->issn;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Journal
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Return the ping gateway url.
     *
     * @return string
     */
    public function getGatewayUrl() {
        return $this->url . self::GATEWAY_URL_SUFFIX;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Journal
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set termsAccepted.
     *
     * @param bool $termsAccepted
     *
     * @return Journal
     */
    public function setTermsAccepted($termsAccepted) {
        $this->termsAccepted = $termsAccepted;

        return $this;
    }

    /**
     * Get termsAccepted.
     *
     * @return bool
     */
    public function getTermsAccepted() {
        return $this->termsAccepted;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Journal
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set publisherName.
     *
     * @param string $publisherName
     *
     * @return Journal
     */
    public function setPublisherName($publisherName) {
        $this->publisherName = $publisherName;

        return $this;
    }

    /**
     * Get publisherName.
     *
     * @return string
     */
    public function getPublisherName() {
        return $this->publisherName;
    }

    /**
     * Set publisherUrl.
     *
     * @param string $publisherUrl
     *
     * @return Journal
     */
    public function setPublisherUrl($publisherUrl) {
        $this->publisherUrl = $publisherUrl;

        return $this;
    }

    /**
     * Get publisherUrl.
     *
     * @return string
     */
    public function getPublisherUrl() {
        return $this->publisherUrl;
    }

    /**
     * Add deposit.
     *
     * @return Journal
     */
    public function addDeposit(Deposit $deposit) {
        $this->deposits[] = $deposit;

        return $this;
    }

    /**
     * Remove deposit.
     */
    public function removeDeposit(Deposit $deposit) : void {
        $this->deposits->removeElement($deposit);
    }

    /**
     * Get deposits.
     *
     * @return Collection
     */
    public function getDeposits() {
        return $this->deposits;
    }

    /**
     * Get the deposits which have been set to LOCKSSOMatic, but which may not have
     * achieved agreement yet.
     *
     * Deposits returned will be in state deposited, complete, or status-error. Those
     * have all been sent to lockss.
     *
     * @return ArrayCollection|Deposit[]
     */
    public function getSentDeposits() {
        $criteria = Criteria::create()->where(Criteria::expr()->in('state', self::SENTSTATES));

        return $this->getDeposits()->matching($criteria);
    }
}
