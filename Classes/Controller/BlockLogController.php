<?php
namespace Webenergy\MhHttpbl\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class BlockLogController
 *
 * @author Julian Hofmann <julian.hofmann@webenergy.de>
 */
class BlockLogController extends BackendController
{
    /**
     * blockLogRepository
     *
     * @var \Webenergy\MhHttpbl\Domain\Repository\BlockLogRepository
     */
    protected $blockLogRepository;

    /**
     * whitelistRepository
     *
     * @var \Webenergy\MhHttpbl\Domain\Repository\WhitelistRepository
     */
    protected $whitelistRepository;

    /**
     * list action
     *
     * @param \Webenergy\MhHttpbl\Domain\Model\Demand|null $demand
     * @param string $sortField
     * @param int $sortRev
     */
    public function listAction(\Webenergy\MhHttpbl\Domain\Model\Demand $demand = null, $sortField = '', $sortRev = 0)
    {
        if ($sortField) {
            $this->blockLogRepository->setDefaultOrderings([
                $sortField => ($sortRev == 1 ? QueryInterface::ORDER_ASCENDING : QueryInterface::ORDER_DESCENDING)
            ]);
        }
        if ($demand === null) {
            $entries = $this->blockLogRepository->findAll();
        } else {
            $entries = $this->blockLogRepository->findDemanded($demand);
        }

        $this->view->assignMultiple([
            'sortField' => $sortField,
            'sortRev' => $sortRev,
            'demand' => $demand,
            'entries' => $entries
        ]);
    }

    /**
     * action delete
     *
     * @param \Webenergy\MhHttpbl\Domain\Model\BlockLog $blockLog
     */
    public function deleteAction(\Webenergy\MhHttpbl\Domain\Model\BlockLog $blockLog)
    {
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            'The IP adress ' . $blockLog->getIp() . ' has been removed from the list of blocked IPs.',
            $blockLog->getIp() . ' has been deleted',
            FlashMessage::OK
        );
        $this->blockLogRepository->remove($blockLog);
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $flashMessageService = $this->objectManager->get(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);

        $this->forward('list');
    }

    /**
     * action move
     *
     * @param \Webenergy\MhHttpbl\Domain\Model\BlockLog $blockLog
     */
    public function moveAction(\Webenergy\MhHttpbl\Domain\Model\BlockLog $blockLog)
    {
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            'The IP adress ' . $blockLog->getIp() . ' has been moved to the whitelist.',
            $blockLog->getIp() . ' has been moved',
            FlashMessage::OK
        );

        $tstamp = new \DateTime();
        $tstamp->setTimestamp($GLOBALS['SIM_ACCESS_TIME']);

        $whitelist = new \Webenergy\MhHttpbl\Domain\Model\Whitelist();
        $whitelist->setIp($blockLog->getIp());
        $whitelist->setCrdate($tstamp);
        $whitelist->setTstamp($tstamp);

        $this->whitelistRepository->add($whitelist);
        $this->blockLogRepository->remove($blockLog);
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();

        $flashMessageService = $this->objectManager->get(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);

        $this->forward('list');
    }

    /**
     * @param \Webenergy\MhHttpbl\Domain\Repository\BlockLogRepository $blockLogRepository
     */
    public function injectBlockLogRepository(\Webenergy\MhHttpbl\Domain\Repository\BlockLogRepository $blockLogRepository)
    {
        $this->blockLogRepository = $blockLogRepository;
    }

    /**
     * @param \Webenergy\MhHttpbl\Domain\Repository\WhitelistRepository $whitelistRepository
     */
    public function injectWhitelistRepository(\Webenergy\MhHttpbl\Domain\Repository\WhitelistRepository $whitelistRepository)
    {
        $this->whitelistRepository = $whitelistRepository;
    }
}
