<?php
/**
 * Created by PhpStorm.
 * User: jobvink
 * Date: 29-10-17
 * Time: 15:28
 */

namespace AppBundle\Twig;

use AppBundle\Entity\ModalItem;
use Twig_Extension;


class PortfolioTypeExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('portfoliotype', array($this, 'convert')),
        );
    }

    public function convert($item, $editpath)
    {
        /** @var ModalItem $item */
        $type = $item->getType();
        $name = $item->getName();
        $body = $item->getBody();

        switch ($type) {
            case 'image':
            case 'IMAGE_TYPE':
                $output = "<img class=\"img-responsive img-centered\" alt='$name' src='/uploads/portfolio/items/$item'>";
                break;
            case 'video':
            case 'VIDEO_TYPE':
                $output = "<div class=\"video-wrapper\"><iframe width=\"560\" height=\"315\" src=\"$item\" frameborder=\"0\" allowfullscreen></iframe></div>";
                break;
            case 'paragraph':
            case 'PARAGRAPH_TYPE':
                $output = "<p class=\"editable\" data-type=\"paragraph\" data-path=\"$editpath\">$item</p>";
                break;
            case 'link':
            case 'LINK_TYPE':
                $output = "<a href='$body'>$name</a>";
                break;
            case 'raw':
            case 'RAW_TYPE':
            default:
                $output = $item;
        }

        return $output;
    }
}