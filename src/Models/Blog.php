<?php
/**
 * Created by PhpStorm.
 * User: giuseppetoto
 * Date: 10/07/15
 * Time: 10:56
 */

namespace Stentle\LaravelWebcore\Models;

use Stentle\LaravelWebcore\Http\RestModel;

/**
 * Class User
 * @package Stentle\LaravelWebcore\Models
 * @property string|array $country
 */

class Blog extends RestModel
{

    protected $resource='cms/frontend/blogposts';

    protected  $rootProperty='data.items';
    protected  $rootPropertyForMethodFind='data';
    protected $fillable  = array('published','text','title','creationDate','publishingDate','coverImage','additionalImages','nextPost','previousPost');

    public $published;
    public $text;
    public $title;
    public $creationDate;
    public $publishingDate;
    public $coverImage;
    public $additionalImages;
    public $nextPost;
    public $previousPost;

}