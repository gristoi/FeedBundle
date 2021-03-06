<?php
/*
 * This file is part of the Eko\FeedBundle Symfony bundle.
 *
 * (c) Vincent Composieux <vincent.composieux@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eko\FeedBundle\Formatter;

use Eko\FeedBundle\Feed\Feed;
use Eko\FeedBundle\Field\ItemField;
use Eko\FeedBundle\Item\Writer\ItemInterface;

/**
 * Formatter
 *
 * This class provides formatter methods
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class Formatter
{
    /**
     * @var Feed $feed A feed instance
     */
    protected $feed;

    /**
     * @var DOMDocument $dom XML DOMDocument
     */
    protected $dom;

    /**
     * @var array $fields Contain item Field instances for this formatter
     */
    protected $itemFields = array();

    /**
     * Construct a formatter with given feed
     *
     * @param Feed $feed A feed instance
     */
    public function __construct(Feed $feed)
    {
        $this->itemFields = array_merge($this->itemFields, $feed->getItemFields());

        $this->feed = $feed;
    }

    /**
     * Format items field
     *
     * @param ItemField     $field A item field instance
     * @param ItemInterface $item  An entity instance
     * 
     * @return string
     */
    protected function format(ItemField $field, ItemInterface $item)
    {
        $name = $field->getName();

        $method = $field->getMethod();
        $value = $item->{$method}();

        if ($field->get('cdata')) {
            $value = $this->dom->createCDATASection($value);

            $element = $this->dom->createElement($name);
            $element->appendChild($value);
        } else if ($field->get('attribute')) {
            if (!$field->get('attribute_name')) {
                throw new \InvalidArgumentException("'attribute' parameter required an 'attribute_name' parameter.");
            }

            $element = $this->dom->createElement($name);
            $element->setAttribute($field->get('attribute_name'), $item->getFeedItemLink());

        } else {
            if ($format = $field->get('date_format')) {
                if (!$value instanceof \DateTime) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" should be a DateTime instance.', $name));
                }

                $value = $value->format($format);
            }

            $element = $this->dom->createElement($name, $value);
        }

        return $element;
    }

    /**
     * This method render the given feed transforming the DOMDocument to XML
     *
     * @return string
     */
    public function render()
    {
        $this->dom->formatOutput = true;

        return $this->dom->saveXml();
    }
}
