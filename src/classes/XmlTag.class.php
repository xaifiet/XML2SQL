<?php
/**
 * Xml tag class file
 *
 * PHP version 5
 *
 * @category  SQL
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   GIT: <git_id>
 * @link      http://www.xaifiet.com
 * @see       INI
 * @since     File available since Release 0.1
 */

/**
 * Xml tag class
 *
 * This class is used to traduce a Xml tag into a Object with name, file path, line
 * number, attributes, children and value
 *
 * @category  XML
 * @package   Xml2Sql
 * @author    Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
 * @copyright 2011 Xaifiet Corp
 * @license   Xaifiet Corp licence
 * @version   Release: @package_version@
 * @link      http://www.xaifiet.com
 * @see       XML
 * @since     Class available since Release 0.1
 */
class XmlTag
{

    /**
     * Tag name value
     *
     * @var string
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public $name;

    /**
     * Tag file path
     *
     * @var string
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public $file;

    /**
     * Tag line number in source file
     *
     * @var string
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public $lineNo;

    /**
     * Attributes of the tag
     *
     * @var StdClass
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public $attributes;

    /**
     * Value of the tag
     *
     * @var mixed
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public $value;

    /**
     * Children of the tag
     *
     * @var array
     *
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    protected $children;

    /**
     * Class constructor
     *
     * This function initialize the tag by null value if no DomElement given or with
     * all tags informations:
     * - Name
     * - File path
     * - Line number in the file
     * - value of the tag
     * - Attributes
     * - Children
     *
     * @param DomElement $tag Tag to transform
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function __construct(DomElement $tag = null)
    {
        $this->attributes = new StdClass();
        $this->children =   array();
        if (!is_null($tag)) {
            $this->name =   $tag->nodeName;
            $this->file =   $tag->ownerDocument->documentURI;
            $this->lineNo = $tag->getLineNo();
            $this->value =  $tag->nodeValue;
            foreach ($tag->attributes as $attribute) {
                $this->addAttribute($attribute->name, $attribute->value);
            }
            foreach ($tag->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $this->addChild($child);
                }
            }
        } else {
            $this->name =   null;
            $this->file =   null;
            $this->lineNo = null;
            $this->value =  null;
        }
    }

    /**
     * Add attribute function
     *
     * This function add an attribute to the tag
     *
     * @param string $name  Name of the attribute
     * @param string $value Value of the attribute
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addAttribute($name, $value)
    {
        $this->attributes->$name = $value;
    }

    /**
     * Add child function
     *
     * This function add a child to the tag
     *
     * @param DomElement $child Child element
     *
     * @return void
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function addChild($child)
    {
        $this->children[] = $child;
    }

    /**
     * Get children function
     *
     * This function return children of the tag
     *
     * @return array Children list
     *
     * @since 0.1
     * @author Xavier DUBREUIL <xavier.dubreuil@xaifiet.com>
     */
    public function getChildren()
    {
        return $this->children;
    }

}

?>
