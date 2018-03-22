<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\BagValidator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Validate a bag metadata and checksums.
 */
class ValidateBagCommand extends AbstractProcessingCmd {
    
    /**
     * Bag validator service.
     *
     * @var BagValidator
     */
    private $bagValidator;
    
    /**
     * Build the command.
     *
     * @param EntityManagerInterface $em
     *   Dependency injected entity manager.
     * @param BagValidator $bagValidator
     *   Dependency injected bag validator.
     */
    public function __construct(EntityManagerInterface $em, BagValidator $bagValidator) {
        parent::__construct($em);
        $this->bagValidator = $bagValidator;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:validate:bag');
        $this->setDescription('Validate PLN deposit packages.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        return $this->bagValidator->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'bag-validated';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'payload-validated';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'Bag checksum validation failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'Bag checksum validation succeeded.';
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'bag-error';
    }

}
