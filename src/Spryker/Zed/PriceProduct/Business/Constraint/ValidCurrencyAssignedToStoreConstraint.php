<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Constraint;

use Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToStoreFacadeInterface;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

class ValidCurrencyAssignedToStoreConstraint extends SymfonyConstraint
{
    protected const MESSAGE = 'Currency "{{ currency }}" is not assigned to the store "{{ store }}"';

    /**
     * @var \Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @phpstan-param array<string, mixed> $options
     *
     * @param \Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToStoreFacadeInterface $storeFacade
     * @param array|null $options
     */
    public function __construct(PriceProductToStoreFacadeInterface $storeFacade, $options = null)
    {
        $this->storeFacade = $storeFacade;

        parent::__construct($options);
    }

    /**
     * @return \Spryker\Zed\PriceProduct\Dependency\Facade\PriceProductToStoreFacadeInterface
     */
    public function getStoreFacade(): PriceProductToStoreFacadeInterface
    {
        return $this->storeFacade;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return static::MESSAGE;
    }

    /**
     * @return string
     */
    public function getTargets(): string
    {
        return static::CLASS_CONSTRAINT;
    }
}
