<?php
namespace Webenergy\MhHttpbl\Domain\Repository;

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

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class WhitelistRepository
 */
class WhitelistRepository extends Repository
{
    public function initializeObject()
    {
        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        // don't add the pid constraint
        $querySettings->setRespectStoragePage(false);
    }

    /**
     * Returns all objects of this repository.
     *
     * @param  \Webenergy\MhHttpbl\Domain\Model\Demand $demand
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface|array
     */
    public function findDemanded($demand)
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface $query */
        $query = $this->createQuery();
        $ipAddress = $demand->getIpAddress();
        $query->matching(
            $query->equals('ip', $ipAddress)
        );
        return $query->execute();
    }
}
