<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProduct\Business\Model\Product;

use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\PriceProduct\Business\Model\PriceData\PriceDataChecksumGeneratorInterface;
use Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriter\PriceProductStoreWriterPluginExecutorInterface;
use Spryker\Zed\PriceProduct\Dependency\Service\PriceProductToUtilEncodingServiceInterface;
use Spryker\Zed\PriceProduct\Persistence\PriceProductEntityManagerInterface;
use Spryker\Zed\PriceProduct\Persistence\PriceProductQueryContainerInterface;
use Spryker\Zed\PriceProduct\Persistence\PriceProductRepositoryInterface;
use Spryker\Zed\PriceProduct\PriceProductConfig;

class PriceProductStoreWriter implements PriceProductStoreWriterInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\PriceProduct\Persistence\PriceProductQueryContainerInterface
     */
    protected $priceProductQueryContainer;

    /**
     * @var \Spryker\Zed\PriceProduct\Persistence\PriceProductEntityManagerInterface
     */
    protected $priceProductEntityManager;

    /**
     * @var \Spryker\Zed\PriceProduct\Persistence\PriceProductRepositoryInterface
     */
    protected $priceProductRepository;

    /**
     * @var \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriter\PriceProductStoreWriterPluginExecutorInterface
     */
    protected $priceProductStoreWriterPluginExecutor;

    /**
     * @var \Spryker\Zed\PriceProduct\PriceProductConfig
     */
    protected $priceProductConfig;

    /**
     * @var \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductDefaultWriterInterface
     */
    protected $priceProductDefaultWriter;

    /**
     * @var \Spryker\Zed\PriceProduct\Business\Model\PriceData\PriceDataChecksumGeneratorInterface
     */
    protected $priceDataChecksumGenerator;

    /**
     * @var \Spryker\Zed\PriceProduct\Dependency\Service\PriceProductToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \Spryker\Zed\PriceProduct\Persistence\PriceProductQueryContainerInterface $priceProductQueryContainer
     * @param \Spryker\Zed\PriceProduct\Persistence\PriceProductEntityManagerInterface $priceProductEntityManager
     * @param \Spryker\Zed\PriceProduct\Persistence\PriceProductRepositoryInterface $priceProductRepository
     * @param \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductStoreWriter\PriceProductStoreWriterPluginExecutorInterface $priceProductStoreWriterPluginExecutor
     * @param \Spryker\Zed\PriceProduct\PriceProductConfig $priceProductConfig
     * @param \Spryker\Zed\PriceProduct\Business\Model\Product\PriceProductDefaultWriterInterface $priceProductDefaultWriter
     * @param \Spryker\Zed\PriceProduct\Business\Model\PriceData\PriceDataChecksumGeneratorInterface $priceDataChecksumGenerator
     * @param \Spryker\Zed\PriceProduct\Dependency\Service\PriceProductToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        PriceProductQueryContainerInterface $priceProductQueryContainer,
        PriceProductEntityManagerInterface $priceProductEntityManager,
        PriceProductRepositoryInterface $priceProductRepository,
        PriceProductStoreWriterPluginExecutorInterface $priceProductStoreWriterPluginExecutor,
        PriceProductConfig $priceProductConfig,
        PriceProductDefaultWriterInterface $priceProductDefaultWriter,
        PriceDataChecksumGeneratorInterface $priceDataChecksumGenerator,
        PriceProductToUtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->priceProductQueryContainer = $priceProductQueryContainer;
        $this->priceProductEntityManager = $priceProductEntityManager;
        $this->priceProductRepository = $priceProductRepository;
        $this->priceProductStoreWriterPluginExecutor = $priceProductStoreWriterPluginExecutor;
        $this->priceProductConfig = $priceProductConfig;
        $this->priceProductDefaultWriter = $priceProductDefaultWriter;
        $this->priceDataChecksumGenerator = $priceDataChecksumGenerator;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return void
     */
    public function deleteOrphanPriceProductStoreEntities(PriceProductTransfer $priceProductTransfer): void
    {
        $priceProductCriteriaTransfer = (new PriceProductCriteriaTransfer())
            ->setIdProductAbstract($priceProductTransfer->getIdProductAbstract())
            ->setIdProductConcrete($priceProductTransfer->getIdProduct());

        $orphanPriceProductStoreEntities = $this->priceProductRepository->findOrphanPriceProductStoreEntities($priceProductCriteriaTransfer);

        if (count($orphanPriceProductStoreEntities) === 0) {
            return;
        }

        $this->getTransactionHandler()->handleTransaction(function () use ($orphanPriceProductStoreEntities) {
            $this->doDeleteOrphanPriceProductStoreEntities($orphanPriceProductStoreEntities);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    public function persistPriceProductStore(PriceProductTransfer $priceProductTransfer): PriceProductTransfer
    {
        /** @var \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer */
        $moneyValueTransfer = $priceProductTransfer->requireMoneyValue()->getMoneyValue();

        $moneyValueTransfer
            ->requireFkCurrency()
            ->requireFkStore();

        if (!$priceProductTransfer->getIdPriceProduct()) {
            $priceProductTransfer = $this->savePriceProductEntity($priceProductTransfer);
        }

        $priceProductStoreEntity = $this->findPriceProductStoreEntity(
            $priceProductTransfer,
            $moneyValueTransfer,
        );

        $priceProductStoreEntity->fromArray($moneyValueTransfer->toArray());

        /** @var int $idPriceProduct */
        $idPriceProduct = $priceProductTransfer->getIdPriceProduct();
        $priceProductStoreEntity
            ->setGrossPrice($moneyValueTransfer->getGrossAmount())
            ->setNetPrice($moneyValueTransfer->getNetAmount())
            ->setFkPriceProduct($idPriceProduct);

        $priceProductStoreEntity = $this->setPriceDataChecksum($moneyValueTransfer, $priceProductStoreEntity);

        $priceProductStoreEntity->save();

        /** @var int $idPriceProductStore */
        $idPriceProductStore = $priceProductStoreEntity->getIdPriceProductStore();
        $moneyValueTransfer->setIdEntity($idPriceProductStore);

        $priceProductTransfer = $this->persistPriceProductDimension($priceProductTransfer);

        if ($this->priceProductConfig->getIsDeleteOrphanStorePricesOnSaveEnabled()) {
            $this->deleteOrphanPriceProductStoreEntities($priceProductTransfer);
        }

        return $priceProductTransfer;
    }

    /**
     * @return void
     */
    public function deleteAllOrphanPriceProductStoreEntities(): void
    {
        $orphanPriceProductStoreEntities = $this->priceProductRepository
            ->findOrphanPriceProductStoreEntities(new PriceProductCriteriaTransfer());

        if (count($orphanPriceProductStoreEntities) === 0) {
            return;
        }

        $this->getTransactionHandler()->handleTransaction(function () use ($orphanPriceProductStoreEntities) {
            $this->doDeleteOrphanPriceProductStoreEntities($orphanPriceProductStoreEntities);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function savePriceProductEntity(PriceProductTransfer $priceProductTransfer): PriceProductTransfer
    {
        $priceProductTransfer
            ->requireFkPriceType();

        $this->requireFieldsBaseOnProductType($priceProductTransfer);

        if ($priceProductTransfer->getIdProduct() !== null) {
            return $this->preparePriceProductForProductConcrete($priceProductTransfer);
        }

        return $this->preparePriceProductForProductAbstract($priceProductTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function preparePriceProductForProductConcrete(PriceProductTransfer $priceProductTransfer): PriceProductTransfer
    {
        $idPriceProduct = $this->priceProductRepository
            ->findIdPriceProductForProductConcrete($priceProductTransfer);

        if ($idPriceProduct === null) {
            $idPriceProduct = $this->priceProductEntityManager
                ->savePriceProductForProductConcrete($priceProductTransfer);
        }

        return $priceProductTransfer
            ->setIdPriceProduct($idPriceProduct);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function preparePriceProductForProductAbstract(PriceProductTransfer $priceProductTransfer): PriceProductTransfer
    {
        $idPriceProduct = $this->priceProductRepository
            ->findIdPriceProductForProductAbstract($priceProductTransfer);

        if ($idPriceProduct === null) {
            $idPriceProduct = $this->priceProductEntityManager
                ->savePriceProductForProductAbstract($priceProductTransfer);
        }

        return $priceProductTransfer
            ->setIdPriceProduct($idPriceProduct);
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return void
     */
    protected function requireFieldsBaseOnProductType(PriceProductTransfer $priceProductTransfer): void
    {
        if ($priceProductTransfer->getIdProduct() === null) {
            $priceProductTransfer->requireIdProductAbstract();
        }

        if ($priceProductTransfer->getIdProductAbstract() === null) {
            $priceProductTransfer->requireIdProduct();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer
     * @param \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore $priceProductStoreEntity
     *
     * @return \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore
     */
    protected function setPriceDataChecksum(MoneyValueTransfer $moneyValueTransfer, SpyPriceProductStore $priceProductStoreEntity): SpyPriceProductStore
    {
        if (!empty($moneyValueTransfer->getPriceData())) {
            $priceProductStoreEntity->setPriceDataChecksum($this->generatePriceDataChecksumByPriceData($moneyValueTransfer->getPriceData()));
        }

        return $priceProductStoreEntity;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function persistPriceProductDimension(PriceProductTransfer $priceProductTransfer): PriceProductTransfer
    {
        /** @var \Generated\Shared\Transfer\PriceProductDimensionTransfer $priceDimensionTransfer */
        $priceDimensionTransfer = $priceProductTransfer->requirePriceDimension()->getPriceDimension();
        if ($priceDimensionTransfer->getType() === $this->priceProductConfig->getPriceDimensionDefault()) {
            return $this->persistPriceProductDefaultDimensionType($priceProductTransfer);
        }

        if ($priceProductTransfer->getIdProduct()) {
            return $this->priceProductStoreWriterPluginExecutor->executePriceDimensionConcreteSaverPlugins($priceProductTransfer);
        }

        if ($priceProductTransfer->getIdProductAbstract()) {
            return $this->priceProductStoreWriterPluginExecutor->executePriceDimensionAbstractSaverPlugins($priceProductTransfer);
        }

        return $priceProductTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function persistPriceProductDefaultDimensionType(
        PriceProductTransfer $priceProductTransfer
    ): PriceProductTransfer {
        $priceProductDefaultEntityTransfer = $this->priceProductDefaultWriter->persistPriceProductDefault($priceProductTransfer);
        /** @var int|null $idPriceProductDefault */
        $idPriceProductDefault = $priceProductDefaultEntityTransfer->getIdPriceProductDefault();
        /** @var \Generated\Shared\Transfer\PriceProductDimensionTransfer $priceDimensionTransfer */
        $priceDimensionTransfer = $priceProductTransfer->requirePriceDimension()->getPriceDimension();
        $priceDimensionTransfer->setIdPriceProductDefault($idPriceProductDefault);

        return $priceProductTransfer->setPriceDimension($priceDimensionTransfer);
    }

    /**
     * @param array<\Generated\Shared\Transfer\SpyPriceProductStoreEntityTransfer> $priceProductStoreEntityTransfers
     *
     * @return void
     */
    protected function doDeleteOrphanPriceProductStoreEntities(array $priceProductStoreEntityTransfers): void
    {
        foreach ($priceProductStoreEntityTransfers as $priceProductStoreEntityTransfer) {
            $idPriceProductStore = (int)$priceProductStoreEntityTransfer->getIdPriceProductStore();

            $this->priceProductStoreWriterPluginExecutor->executePriceProductStorePreDeletePlugins($idPriceProductStore);
            $this->priceProductEntityManager->deletePriceProductStore($idPriceProductStore);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param \Generated\Shared\Transfer\MoneyValueTransfer $moneyValueTransfer
     *
     * @return \Orm\Zed\PriceProduct\Persistence\SpyPriceProductStore
     */
    protected function findPriceProductStoreEntity(
        PriceProductTransfer $priceProductTransfer,
        MoneyValueTransfer $moneyValueTransfer
    ): SpyPriceProductStore {
        /** @var int $idPriceProduct */
        $idPriceProduct = $priceProductTransfer->requireIdPriceProduct()->getIdPriceProduct();
        /** @var int $idCurrency */
        $idCurrency = $moneyValueTransfer->requireFkCurrency()->getFkCurrency();
        /** @var int $idStore */
        $idStore = $moneyValueTransfer->requireFkStore()->getFkStore();

        return $this->priceProductQueryContainer
            ->queryPriceProductStoreByProductCurrencyStore(
                $idPriceProduct,
                $idCurrency,
                $idStore,
            )
            ->filterByNetPrice($moneyValueTransfer->getNetAmount())
            ->filterByGrossPrice($moneyValueTransfer->getGrossAmount())
            ->filterByPriceDataChecksum($moneyValueTransfer->getPriceDataChecksum())
            ->findOneOrCreate();
    }

    /**
     * @param string $priceData
     *
     * @return string|null
     */
    protected function generatePriceDataChecksumByPriceData(string $priceData): ?string
    {
        $priceDataArray = $this->utilEncodingService->decodeJson($priceData, true);

        if (empty($priceDataArray)) {
            return null;
        }

        return $this->priceDataChecksumGenerator->generatePriceDataChecksum($priceDataArray);
    }
}
