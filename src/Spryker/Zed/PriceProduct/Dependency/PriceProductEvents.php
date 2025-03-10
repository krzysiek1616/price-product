<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Dependency;

interface PriceProductEvents
{
    /**
     * Specification
     * - This events will be used for price_abstract publishing
     *
     * @api
     *
     * @var string
     */
    public const PRICE_ABSTRACT_PUBLISH = 'Price.price_abstract.publish';

    /**
     * Specification
     * - This events will be used for price_abstract un-publishing
     *
     * @api
     *
     * @var string
     */
    public const PRICE_ABSTRACT_UNPUBLISH = 'Price.price_abstract.unpublish';

    /**
     * Specification
     * - This events will be used for price_concrete publishing
     *
     * @api
     *
     * @var string
     */
    public const PRICE_CONCRETE_PUBLISH = 'Price.price_concrete.publish';

    /**
     * Specification
     * - This events will be used for price_concrete un-publishing
     *
     * @api
     *
     * @var string
     */
    public const PRICE_CONCRETE_UNPUBLISH = 'Price.price_concrete.unpublish';

    /**
     * Specification
     * - This events will be used for spy_price_product entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_CREATE = 'Entity.spy_price_product.create';

    /**
     * Specification
     * - This events will be used for spy_price_product entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_UPDATE = 'Entity.spy_price_product.update';

    /**
     * Specification
     * - This events will be used for spy_price_product entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_DELETE = 'Entity.spy_price_product.delete';

    /**
     * Specification
     * - This events will be used for spy_price_type entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_TYPE_CREATE = 'Entity.spy_price_type.create';

    /**
     * Specification
     * - This events will be used for spy_price_type entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_TYPE_UPDATE = 'Entity.spy_price_type.update';

    /**
     * Specification
     * - This events will be used for spy_price_type entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_TYPE_DELETE = 'Entity.spy_price_type.delete';

    /**
     * Specification
     * - This events will be used for spy_price_product_store entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_STORE_CREATE = 'Entity.spy_price_product_store.create';

    /**
     * Specification
     * - This events will be used for spy_price_product_store entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_STORE_UPDATE = 'Entity.spy_price_product_store.update';

    /**
     * Specification
     * - This events will be used for spy_price_product_store entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_STORE_DELETE = 'Entity.spy_price_product_store.delete';

    /**
     * Specification
     * - This events will be used for spy_price_product_default entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_DEFAULT_CREATE = 'Entity.spy_price_product_default.create';

    /**
     * Specification
     * - This events will be used for spy_price_product_default entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_DEFAULT_UPDATE = 'Entity.spy_price_product_default.update';

    /**
     * Specification
     * - This events will be used for spy_price_product_default entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_PRICE_PRODUCT_DEFAULT_DELETE = 'Entity.spy_price_product_default.delete';
}
