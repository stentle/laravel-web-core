<?php

namespace Stentle\LaravelWebcore\Business;

use DrewM\MailChimp\MailChimp;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Stentle\LaravelWebcore\Models\Filters;

class FiltersManager
{


    private static $instance = null;
    private $filter;

    private function __construct()
    {


        $this->filter = (new Filters())->find(0);

        if ($this->filter instanceof Filters) {
            $this->filter = $this->filter->getInfo();
        }

    }

    /***
     * @return FiltersManager
     */
    public static function getInstance()
    {

        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c();

        }


        return self::$instance;
    }

    /** restituisce l'elenco dei brands
     * @return array
     */
    public function brands()
    {
        if (isset($this->filter['advanced'])) {
            foreach ($this->filter['advanced'] as $row) {
                if ($row['key'] == 'brand') {
                    usort($row['values'], function ($a, $b) {
                        return strcmp($a['locale'], $b['locale']);
                    });
                    return $row['values'];
                }
            }
        }
        return [];
    }

    /** restituisce l'elenco delle macrocategorie di un certo dipartimento
     * @param $department_key
     * @return array
     */
    public function macrocategories($department_key)
    {
        if (isset($this->filter['basic'])) {
            foreach ($this->filter['basic'] as $dep) {
                if ($dep['value'] == $department_key) {
                    return $dep['children'];
                }
            }
        }
        return [];
    }

    /** restituisce l'elenco delle microcategorie di un certo dipartimento
     * @param $department_key
     * @return array
     */
    public function microcategories($department_key)
    {

        $list = [];
        $macrocategories = $this->macrocategories($department_key);

        foreach ($macrocategories as $macro) {
            foreach ($macro['children'] as $micro) {
                $list[] = $micro;
            }
        }
        return $list;
    }
}