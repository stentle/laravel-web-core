<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\LaravelWebcore\Models;

use Stentle\LaravelWebcore\Facades\ClientHttp;
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
    public $productVariant;
    public $defaultPricesComparison;
    public $sku;
    public $attributeGroups;
    public $photos;
    public $availableSizes;
    public $attributeVariants;
    public $sellingDiscount;
    public $retailPrice;
    public $sellingPrice;
    public $currency;
    public $coverPhotoUrl;


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


    public function getParentAttributeKey()
    {
        return $this->photoAttributes[0];
    }

    public function getInfo($exclude = array())
    {
        $info = parent::getInfo();

        $info['pricesComparison'] = $this->getPricesComparison();

        if (!isset($info['coverPhotoUrl'])) {
            $info['coverPhotoUrl'] = 'http://placehold.it/300x300';
        }


        if (isset($info['photoGallery'])) {
            foreach ($info['photoGallery']['images'] as $photo) {
                if ($photo['type'] == 'cover')
                    $info['coverPhotoUrl'] = $photo['imageURL'];
                else
                    $info['gallery'][] = $photo['imageURL'];
            }
        } else {
            $info['gallery'][] = 'http://placehold.it/600x600';
        }
        return $info;
    }

    public function hasVariantsGroup()
    {
        return $this->productVariant && isset($this->variantsGroup) && count($this->variantsGroup) > 0;
    }

    /**Recupera le informazioni relative ai prezzi dei prodotti venduti da altri merchant.
     * Se il prodotto è una variante, recupera le informazioni sulla variante altrimenti se non sono disponibile le recupera sul prodotto padre.
     * @return mixed
     */
    public function getPricesComparison()
    {
        $pricesComparison = null;
        if ($this->pricesComparison != null) {
            $pricesComparison = $this->pricesComparison;
        } else {
            $pricesComparison = $this->defaultPricesComparison;
        }

        if ($pricesComparison != null) {
            //riordinata per prezzo basso
            usort($pricesComparison['internetPrices'], function ($a, $b) {
                if ($a['priceWithShipping']['value'] == $b['priceWithShipping']['value']) {
                    return 0;
                }
                return ($a['priceWithShipping']['value'] < $b['priceWithShipping']['value']) ? -1 : 1;
            });
        }

        return $pricesComparison;

    }


    /**
     * Ogni prodotto può avere una o più varianti.
     * Ogni variante può essere rappresentata da uno o più attributi che assumono un ruolo particolare.
     * Ad esempio una scarpa nike può avere una variante di colore: giallo, rosso, etc.
     * Ogni colore a sua volta a una variante associata ad esempio taglie disponibili: 41,42, etc.
     * la proprietà variantsGroup rappresenta le dipendenze tra questi attributi tramite un albero (color->size).
     * Si suppone attualmente che l'albero non superi 2 livelli di profondità.
     * @return array|bool
     */
    public function getVariantsGroup()
    {
        if ($this->hasVariantsGroup()) {
            $obj = [];
            $obj['key'] = $this->getParentAttributeKey(); //attributo padre:es.  color
            $obj['keyChildrenVariants'] = $this->getKeyChildrenVariants(); //attributo figlio: es. size
            $obj['values'] = [];
            foreach ($this->variantsGroup as $el) {
                $photos = [];
                //fix photo
                foreach ($el['photos'] as $photo) {
                    preg_match("/products\/(.*)/i", $photo['imageURL'], $matches);
                    if (count($matches) > 1)
                        $photos[] = '/image/loadProduct/' . $matches[1];
                    else
                        $photos[] = $photo['imageURL'];
                } //per ogni attributo padre (es. colore giallo) mappo i figli (es. size 41, 42, etc.)
                $item = ['keyChildrenVariants' => $obj['keyChildrenVariants'], 'key' => $obj['key'], 'value' => $el['key'], 'name' => $el['localeName'], 'photos' => $photos, 'variants' => $this->getChildrenVariants($el['key'])];


                //riordino per valore attributo
                usort($item['variants'], function ($item1, $item2) {

                    $sizes = array(
                        'XXS' => 0,
                        'XS' => 1,
                        'S' => 2,
                        'M' => 3,
                        'L' => 4,
                        'XL' => 5,
                        'XXL' => 6
                    );

                    $keysize1 = $item1['value'];
                    $keysize2 = $item2['value'];
                    if (is_numeric($keysize1) && is_numeric($keysize2)) { //se è una stringa li valuto come delle taglie
                        if ($item1['value'] == $item2['value']) return 0;
                        return $item1['value'] < $item2['value'] ? -1 : 1;
                    } else {
                        $asize = @$sizes[$keysize1];
                        $bsize = @$sizes[$keysize2];
                        if ($asize == $bsize)
                            return 0;
                        return ($asize > $bsize) ? 1 : -1;
                    }

                });
                $obj['values'][] = $item;

            }

            return $obj;
        }
        return false;
    }

    /** Recupera un groupvariant partendo da una idvariante facente parte di quel gruppo
     * @param $id variant_id
     */
    public function findVariantGroupById($id)
    {
        $variantsGroup = $this->getVariantsGroup();
        if ($variantsGroup && $id) {
            foreach ($variantsGroup['values'] as $group) {
                foreach ($group['variants'] as $variant) {
                    if ($variant['id'] == $id) {
                        $group['variant'] = $variant;
                        return $group;
                    }
                }
            }
        } else
            return false;

    }

    /**
     * Recupera la key dell'attributo figlio della variante. Ad esempio se abbiamo un prodotto con attributo padre Colore
     * dal quale dipende l'attributo figlio size. Viene restituito size.
     */
    public function getKeyChildrenVariants()
    {
        if ($this->hasVariantsGroup()) {
            $declareVariants = $this->declareVariants;
            if (($key = array_search($this->getParentAttributeKey(), $declareVariants)) !== false) {
                unset($declareVariants[$key]);
                if (count($declareVariants) == 1) //mi assicuro che ci sia solo un attributo figlio
                    return $declareVariants[0];
            }
        }
        return false;
    }

    /**
     * Fissato il padre (es.colore=bianco), restituisce tutte le varianti associate su un determinato attributo figlio (es. size)
     * @param $valueParent es. bianco
     * @return bool
     */
    public function getChildrenVariants($valueParent)
    {
        $keyChildren = $this->getKeyChildrenVariants();
        if ($this->hasVariantsGroup()) {
            foreach ($this->variantsGroup as $group) {
                if ($group['key'] == $valueParent) {
                    $children = [];
                    foreach ($group['variants'] as $variant) {
                        $children[] = [
                            'id' => $variant['id'],
                            'prices' => $variant['prices'],
                            'availabilityTotal' => $variant['availabilityTotal'],
                            'key' => $keyChildren,
                            'pricesComparison' => $variant['pricesComparison'],
                            'value' => $variant['attributeLocales'][$keyChildren]['value'],
                            'name' => $variant['attributeLocales'][$keyChildren]['locale']];

                    }
                    //riordino per valore attributo
                    usort($children, function ($item1, $item2) {

                        $sizes = array(
                            'XXS' => 0,
                            'XS' => 1,
                            'S' => 2,
                            'M' => 3,
                            'L' => 4,
                            'XL' => 5,
                            'XXL' => 6
                        );

                        if (is_string($item1['value']) && is_string($item2['value'])) {
                            $asize = @$sizes[$item1];
                            $bsize = @$sizes[$item2];
                            if ($asize == $bsize)
                                return 0;
                            return ($asize > $bsize) ? 1 : -1;
                        } else {
                            if ($item1['value'] == $item2['value']) return 0;
                            return $item1['value'] < $item2['value'] ? -1 : 1;
                        }

                    });


                    return $children;
                }
            }

        }


        return false;
    }


    public function all()
    {
        return $this->search();
    }


    /**
     * Search for products in the catalog with the ability to filter and imagine
     * array['basic'] array with the basic filters
     * array['advanced'] array with the advanced filters
     * array['range']  array that represent the range
     * array['pageNumber'] integer the requested page number
     * array['limit'] integer the maximum elements to fetch
     * @param array $filter (see above) or use createFilter
     * @return mixed
     * @throws \Exception
     */

    public static function search($filter = array())
    {
        $options = [];

        if (empty($filter)) {
            $filter = self::createFilter([], [], [], 1, 100);
        }
        $options['headers']['Accept'] = 'application/stentle.api-v0.2+json';
        $options['json'] = $filter; //filters

        $response = ClientHttp::post('catalog', $options);

        if ($response->getStatusCode() >= 400)
            throw new \Exception("catalog search request failed with code: " . $response->getStatusCode());
        else
            $json = json_decode($response->getBody()->getContents(), true);

        $products = [];
        $items = $json['data']['result']['items'];

        foreach ($json['data']['result']['items'] as $item) {

            $p = (new ProductFeed());
            $p->setInfo($item);
            $products[] = $p;
        }

        $json['data']['result']['items'] = $products;
        return $json;
    }

    /**
     * Returns an array that represent a filter
     * @param array $basic array with the basic filters
     * @param array $advanced array with the advanced filters
     * @param array $range array that represent the range
     * @param integer $pageNumber the requested page number
     * @param integer $limit the maximum elements to fetch
     * @return array
     */
    public static function createFilter($basic, $advanced, $range, $pageNumber, $limit)
    {
        return ['filterAttributes' => [
            'basic' => $basic,
            'advanced' => $advanced,
            'range' => $range,
        ], 'pageNumber' => $pageNumber,
            'limit' => $limit];
    }

}