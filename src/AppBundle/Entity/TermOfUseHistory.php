<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * TermOfUseHistory.
 *
 * @ORM\Table(name="term_of_use_history")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TermOfUseHistoryRepository")
 */
class TermOfUseHistory extends AbstractEntity {
    /**
     * A term ID, similar to the OJS translation keys.
     *
     * @var int
     * @ORM\Column(type="integer")
     */
    private $termId;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $action;

    /**
     * The change set, as computed by Doctrine.
     *
     * @var string
     *
     * @ORM\Column(type="array")
     */
    private $changeSet;

    /**
     * The user who added/edited/deleted the term of use.
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $user;

    /**
     *
     */
    public function __toString() {
    }

    /**
     * Set termId.
     *
     * @param int $termId
     *
     * @return TermOfUseHistory
     */
    public function setTermId($termId) {
        $this->termId = $termId;

        return $this;
    }

    /**
     * Get termId.
     *
     * @return int
     */
    public function getTermId() {
        return $this->termId;
    }

    /**
     * Set action.
     *
     * @param string $action
     *
     * @return TermOfUseHistory
     */
    public function setAction($action) {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set changeSet.
     *
     * @param array $changeSet
     *
     * @return TermOfUseHistory
     */
    public function setChangeSet($changeSet) {
        $this->changeSet = $changeSet;

        return $this;
    }

    /**
     * Get changeSet.
     *
     * @return array
     */
    public function getChangeSet() {
        return $this->changeSet;
    }

    /**
     * Set user.
     *
     * @param string $user
     *
     * @return TermOfUseHistory
     */
    public function setUser($user) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

}
