<?php


namespace Stentle\LaravelWebcore\Abstracts;


class Entity
{

    /***
     * @var array rappresenta l'insieme dei campi che non devono essere mostrati quando si recuperano i campi dell'entità
     */
    protected $hidden = array();

    /**
     * @var array rappresenta l'insieme dei campi che non possono essere settati. Utile per proteggere da un eventuale set massivo dei dati.
     *Utilizzare fillable o guarded. Non entrambi
     */
    protected $guarded = array();

    /**
     * @var array rappresenta l'insieme dei campi che possono essere settati. Utile per progettegere da un eventuale set massivo dei dati.
     * Utilizzare fillable o guarded. Non entrambi
     */
    protected $fillable;

    private $ignore_fields = array('hidden', 'guarded', 'fillable', 'ignore_fields');

    /**
     *  restituisce le informazioni del bean in formato array
     * @param array $exclude ulteriori campi da ignorare nell'array che viene restituito
     * @return array
     */
    public function getInfo($exclude = array())
    {
        $info = array();
        $exclude = array_merge($this->ignore_fields, $this->hidden, $exclude);
        $reflector = new \ReflectionClass($this);
        $properties = $reflector->getProperties();
        foreach ($properties as $p) {
            $key = $p->getName();
            if ($this->$key !== NULL && !in_array($key, $exclude)) {
                if ($this->is_JSON($this->$key)) {
                    $info[$key] = json_decode($this->$key);
                } else {
                    $info[$key] = $this->$key;
                }
                if(is_infinite((float) $info[$key])){
                    $info[$key] = (string)$info[$key];
                }
            }
        }
        return $info;
    }

    /**
     * Si occupa di settare i dati della nostra entity
     * @param array $attributes
     * @param bool|true $isGuard se impostato a false i campi protetti saranno comunque settati
     */
    public function setInfo(array $attributes, $isGuard = true)
    {
        $reflector = new \ReflectionClass($this);
        $properties = $reflector->getProperties();
        if ($isGuard)
            $attributes = $this->fill($attributes);
        foreach ($properties as $prop) {
            $key = $prop->getName();
            if (array_key_exists($key, $attributes)) {
                $method = 'set' . ucfirst($key);
                if (method_exists($this, $method)) {
                    $this->$method($attributes[$key]);
                } else {
                    $this->$key = $attributes[$key];
                }
            }
        }

    }

    /**
     * filtra gli attributa con quelli consentiti
     * @param array $attributes
     * @return array
     */
    private function fill(array $attributes)
    {
        if (count($this->fillable) > 0) {
            return array_intersect_key($attributes, array_flip($this->fillable));
        } else if (count($this->guarded) > 0) {
            return array_diff_key($attributes, array_flip($this->guarded));
        }
        return $attributes;
    }

    public function addFieldsIgnore($field)
    {
        if (is_array($field)) {
            $this->ignore_fields = array_merge($this->ignore_fields, $field);
        } else {
            $this->ignore_fields[] = $field;
        }
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->country;
        }
    }

    public function __set($property, $value)
    {
        $method = 'set' . ucfirst($property);
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->$property = $value;
        }
    }

    function is_JSON($value)
    {
        if (!empty($value) && is_string($value)) {
            json_decode($value);
            return (json_last_error() === JSON_ERROR_NONE);
        } else {
            return false;
        }
    }
}