<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\LaravelWebcore\Models;

use Stentle\LaravelWebcore\Models\Product;


/**
 * Class ProductFeed
 * @package Stentle\LaravelWebcore\Models
 */
class ProductFeed extends Product
{

    public $resource = 'products_catalog';
    public $variantsGroup;
    public $pricesComparison;
    public $descriptions;

    public function getSize()
    {

        if (isset($this->attributeGroups['attributeGroupList']) && count($this->attributeGroups['attributeGroupList']) > 0) {
            foreach ($this->attributeGroups['attributeGroupList'][0]['attributeList'] as $attr) {
                if ($attr['attributeCode'] == 'size') {
                    return $attr['localeName'];
                }
            }
            return null;
        }
    }

    public function getColor()
    {
        if (isset($this->attributeGroups['attributeGroupList']) && count($this->attributeGroups['attributeGroupList']) > 0) {
            foreach ($this->attributeGroups['attributeGroupList'][0]['attributeList'] as $attr) {
                if ($attr['attributeCode'] == 'color') {
                    return $attr['localeName'];
                }
            }
            return null;
        }
    }

}