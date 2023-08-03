<?php

namespace ReesMcIvor\ProjectorDistance\Block\Widget;

class Calculator extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'ReesMcIvor_ProjectorDistance::widget/calculator.phtml';

    protected $_categoryFactory;

    protected $_productCollectionFactory;

    protected $_configurableProductType;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_configurableProductType = $configurableProductType;
        parent::__construct($context, $data);
    }


    public function getProjectorsCategoryId()
    {
        return str_replace("category/", "", $this->getData('projectors_category_id'));
    }

    public function getScreensCategoryId()
    {
        return str_replace("category/", "", $this->getData('screen_category_id'));
    }

    public function getCategoryProducts( $categoryId )
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        $categoryIds = [$categoryId];  // Initialize with the selected category ID

        $childCategories = $category->getChildrenCategories();
        if ($childCategories->count() > 0) {
            foreach ($childCategories as $childCategory) {
                $categoryIds[] = $childCategory->getId();
            }
        }

        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*')->addCategoriesFilter(['in' => $categoryIds]);
        return $productCollection;
    }

    public function getProjectorProducts()
    {
        return $this->getCategoryProducts( $this->getProjectorsCategoryId());
    }

    public function getScreenProducts()
    {
        return $this->getCategoryProducts( $this->getScreensCategoryId());
    }

    public function getJson( $productCollection)
    {
        $productData = [];
        foreach ($productCollection as $product) {
            $productData[] = $this->getProductData( $product );
        }
        return json_encode($productData);
    }

    protected function getProductData( $product )
    {
        return array_filter([
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'projector_throw_min' => $product->getData('projector_throw_min'),
            'projector_throw_max' => $product->getData('projector_throw_max')
        ]);
    }
}
