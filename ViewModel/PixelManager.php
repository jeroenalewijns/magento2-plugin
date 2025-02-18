<?php
/**
 * Copyright © Squeezely B.V. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Squeezely\Plugin\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Squeezely\Plugin\Api\Config\System\AdvancedOptionsInterface as AdvancedOptionsRepository;
use Squeezely\Plugin\Api\Config\System\FrontendEventsInterface as FrontendEventsRepository;
use Squeezely\Plugin\Api\Service\DataLayerInterface;
use Magento\Framework\UrlInterface;
use Squeezely\Plugin\Api\Log\RepositoryInterface as LogRepository;

/**
 * Class PixelManager
 */
class PixelManager implements ArgumentInterface
{
    /**
     * Url path for ajax call
     */
    const URL_PATH = 'sqzl/events/get';

    /**
     * @var FrontendEventsRepository
     */
    private $configRepository;
    /**
     * @var AdvancedOptionsRepository
     */
    private $advancedOptionsRepository;
    /**
     * @var DataLayerInterface
     */
    private $dataLayer;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var int
     */
    private $storeId = 0;

    /**
     * PixelManager constructor.
     *
     * @param FrontendEventsRepository $configRepository
     * @param AdvancedOptionsRepository $advancedOptionsRepository
     * @param DataLayerInterface $dataLayer
     * @param UrlInterface $urlBuilder
     * @param LogRepository $logRepository
     */
    public function __construct(
        FrontendEventsRepository $configRepository,
        AdvancedOptionsRepository $advancedOptionsRepository,
        DataLayerInterface $dataLayer,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        LogRepository $logRepository
    ) {
        $this->configRepository = $configRepository;
        $this->advancedOptionsRepository = $advancedOptionsRepository;
        $this->dataLayer = $dataLayer;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->logRepository = $logRepository;
    }

    /**
     * Check if the module is enabled
     *
     * @return boolean 0 or 1
     */
    public function isEnabled()
    {
        return $this->configRepository->isEnabled($this->getStoreId());
    }

    /**
     * @return string
     */
    public function getJsLink()
    {
        return sprintf(
            $this->advancedOptionsRepository->getEndpointTrackerUrl(),
            $this->getAccountId()
        );
    }

    /**
     * Get container id
     *
     * @return string
     */
    private function getAccountId()
    {
        return $this->configRepository->getAccountId($this->getStoreId());
    }

    /**
     * @return mixed
     */
    public function fireQueuedEvents()
    {
        return $this->dataLayer->fireQueuedEvents();
    }

    /**
     * Get url for queued events ajax call
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->urlBuilder->getUrl(self::URL_PATH);
    }

    /**
     * Return current store id
     *
     * @return int
     */
    private function getStoreId()
    {
        if (!$this->storeId) {
            try {
                $this->storeId = (int)$this->storeManager->getStore()->getId();
            } catch (NoSuchEntityException $e) {
                $this->logRepository->addDebugLog('pixel manager', $e->getMessage());
            }
        }
        return $this->storeId;
    }
}
