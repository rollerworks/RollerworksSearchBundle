<?php

/**
 * This file is part of the RollerworksSearchBundle package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\SearchBundle\SearchFilterStorage;

use Rollerworks\Bundle\SearchBundle\SearchFilterStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * SessionStorage stores search filter-codes using the Symfony Session
 * system.
 */
class SessionStorage implements SearchFilterStorageInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function containsFilter($filterName)
    {
        return $this->session->has('rollerworks_search.filter_code'.$filterName);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterCode($filterName)
    {
        return $this->session->get('rollerworks_search.filter_code'.$filterName, '');
    }

    /**
     * {@inheritdoc}
     */
    public function setFilterCode($filterName, $filterCode)
    {
        $this->session->set('rollerworks_search.filter_code'.$filterName, $filterCode);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFilter($filterName)
    {
        $this->session->remove('rollerworks_search.filter_code'.$filterName);
    }
}
