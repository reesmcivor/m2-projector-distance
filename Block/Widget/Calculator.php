<?php

namespace ReesMcIvor\ProjectorDistance\Block\Widget;

class Calculator extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'ReesMcIvor_ProjectorDistance::widget/calculator.phtml';

    protected $_categoryFactory;

    protected $_productCollectionFactory;

    protected $_configurableProductType;

    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_configurableProductType = $configurableProductType;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getProjectorsCategoryId()
    {
        return str_replace("category/", "", $this->getData('projectors_category_id'));
    }

    public function getCurrentProductSku()
    {
        $product = $this->_registry->registry('current_product');
        if ($product) {
            return $product->getSku();
        }
        return null;
    }

    public function getCategoryProducts($categoryId)
{
        $category = $this->_categoryFactory->create()->load($categoryId);
        $categoryIds = [$categoryId];

        $childCategories = $category->getChildrenCategories();
        if ($childCategories->count() > 0) {
            foreach ($childCategories as $childCategory) {
                $categoryIds[] = $childCategory->getId();
            }
        }

        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*')->addCategoriesFilter(['in' => $categoryIds]);

        $products = [];

        foreach ($productCollection as $product) {
            if ($product->getTypeId() == "configurable") {
                $childProducts = $this->getChildProducts($product);
                foreach ($childProducts as $childProduct) {
                    $products[] = $this->getProductData($childProduct);
                }
            } else {
                $products[] = $this->getProductData($product);
            }
        }

        return $products;
    }

    public function getProjectorProducts()
    {
        $categoryId = $this->getProjectorsCategoryId();
        $category = $this->_categoryFactory->create()->load($categoryId);
        $categoryIds = [$categoryId];

        foreach ($category->getChildrenCategories() as $childCategory) {
            $categoryIds[] = $childCategory->getId();
        }

        return $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addCategoriesFilter(['in' => $categoryIds]);
    }

    public function filterProjectorProjectorsWithValidThrows( $products )
    {
        foreach($products as $key => $product)
        {
            if (
                empty($product['projector_throw_min']) || empty($product['projector_throw_max']) ||
                !is_numeric($product['projector_throw_min']) || !is_numeric($product['projector_throw_max'])

            ) {
                unset($products[$key]);
            }
        }
        return $products;
    }

    public function getScreenProducts()
    {
        return $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('sku', ['in' => $this->getData('screens')])
            ->addAttributeToFilter('screen_viewable_width', ['gt' => 0]);
    }

    public function getJson( $productCollection )
    {
        $productData = [];
        foreach ($productCollection as $product) {
            $productData[] = $this->getProductData($product);
        }
        return json_encode($productData);
    }

    protected function getProductData( $product )
    {
        return array_filter([
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'manufacturer' => $product->getAttributeText('manufacturer') ?? '',
            'projector_throw_min' => $product->getData('projector_throw_min'),
            'projector_throw_max' => $product->getData('projector_throw_max'),
            'screen_viewable_width' => $product->getData('screen_viewable_width'),
        ]);
    }

    /**
     * @param mixed $product
     * @return void
     */
    protected function getChildProducts( mixed $product )   
    {
        $simpleProducts = $this->_configurableProductType->getUsedProducts($product);
        $simpleProductIds = [];
        foreach ($simpleProducts as $simpleProduct) {
            $simpleProductIds[] = $simpleProduct->getId();
        }
        return $this->_productCollectionFactory->create()
            ->addIdFilter($simpleProductIds)
            ->addAttributeToSelect('*');
    }
}
