<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\ViewModel;

use MadameAiguille\Contact\Model\FormDataValidator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Contact\Helper\Data as ContactHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class ContactForm implements ArgumentInterface
{
    private bool $productLoaded = false;
    private ?Product $product = null;

    public function __construct(
        private readonly ContactHelper $contactHelper,
        private readonly RequestInterface $request,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly Image $imageHelper
    ) {
    }

    public function getValue(string $field): string
    {
        return trim((string) $this->contactHelper->getPostValue($field));
    }

    public function getName(): string
    {
        return $this->getValue('name') ?: (string) $this->contactHelper->getUserName();
    }

    public function getEmail(): string
    {
        return $this->getValue('email') ?: (string) $this->contactHelper->getUserEmail();
    }

    /** @return array<string, string> */
    public function getSubjects(): array
    {
        return FormDataValidator::SUBJECTS;
    }

    public function getProductSku(): string
    {
        $persisted = $this->getValue('product');
        if ($persisted !== '') {
            return mb_substr($persisted, 0, 64);
        }

        return mb_substr(trim((string) $this->request->getParam('product', '')), 0, 64);
    }

    public function getProduct(): ?Product
    {
        if ($this->productLoaded) {
            return $this->product;
        }
        $this->productLoaded = true;
        $sku = $this->getProductSku();
        if ($sku === '') {
            return null;
        }

        try {
            $product = $this->productRepository->get(
                $sku,
                false,
                (int) $this->storeManager->getStore()->getId()
            );
            if ((int) $product->getStatus() !== Status::STATUS_ENABLED
                || (int) $product->getVisibility() === Visibility::VISIBILITY_NOT_VISIBLE
            ) {
                return null;
            }
            $this->product = $product;
        } catch (\Throwable) {
            $this->product = null;
        }

        return $this->product;
    }

    public function getProductImageUrl(Product $product): string
    {
        return $this->imageHelper->init($product, 'category_page_grid')->resize(160, 160)->getUrl();
    }
}
