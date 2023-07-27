<?php

namespace ReesMcIvor\ProjectorDistance\Block\Widget;

class Calculator extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'widget/calculator.phtml';

    protected $_categoryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_categoryFactory = $categoryFactory;
    }


    public function getCategoryId()
    {
        return $this->getData('category_id');
    }

    public function getCategoryProducts()
    {
        $categoryId = $this->getCategoryId();
        $category = $this->_categoryFactory->create()->load($categoryId);
        return $category->getProductCollection()->addAttributeToSelect('*');
    }
}
