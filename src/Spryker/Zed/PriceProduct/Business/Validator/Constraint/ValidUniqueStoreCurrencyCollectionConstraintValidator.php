<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Validator\Constraint;

use Generated\Shared\Transfer\PriceProductTransfer;
use Spryker\Zed\Kernel\Communication\Validator\AbstractConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Traversable;

class ValidUniqueStoreCurrencyCollectionConstraintValidator extends AbstractConstraintValidator
{
    /**
     * @param \Traversable<int, \Generated\Shared\Transfer\PriceProductTransfer> $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     *
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Traversable) {
            throw new UnexpectedTypeException($value, Traversable::class);
        }

        if (!$constraint instanceof ValidUniqueStoreCurrencyCollectionConstraint) {
            throw new UnexpectedTypeException($constraint, ValidUniqueStoreCurrencyCollectionConstraint::class);
        }

        $existingKeys = [];

        foreach ($value as $position => $priceProductTransfer) {
            if (!$priceProductTransfer instanceof PriceProductTransfer) {
                throw new UnexpectedTypeException($priceProductTransfer, PriceProductTransfer::class);
            }

            $moneyValueTransfer = $priceProductTransfer->getMoneyValueOrFail();

            if (!$moneyValueTransfer->getFkCurrency() || !$moneyValueTransfer->getFkStore()) {
                continue;
            }

            $key = sprintf(
                '%s-%s-%s',
                $moneyValueTransfer->getFkCurrencyOrFail(),
                $moneyValueTransfer->getFkStoreOrFail(),
                $priceProductTransfer->getPriceTypeOrFail()->getIdPriceTypeOrFail(),
            );
            if (in_array($key, $existingKeys, true)) {
                $this->context->buildViolation($constraint->getMessage())
                    ->atPath("[$position]")
                    ->addViolation();
            }

            $existingKeys[] = $key;
        }
    }
}
