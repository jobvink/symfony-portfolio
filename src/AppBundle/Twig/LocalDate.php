<?php
/**
 * Created by PhpStorm.
 * User: jobvink
 * Date: 30-10-17
 * Time: 20:44
 */

namespace AppBundle\Twig;


use Carbon\Carbon;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\AclBundle\Entity\Car;
use Twig_Extension;

class LocalDate extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('localdate', array($this, 'convert')),
        );
    }

    public function convert($date, $locale = 'nl', $format = null)
    {

        $output = Carbon::instance($date);
        Carbon::setLocale($locale);
        $output->setTimezone('Europe/Amsterdam');


//        if (!is_null($format)) {
//        } else {
//            $output->format($format);
//            $output = $output->toFormattedDateString();
//        }
        setlocale(LC_TIME, 'nl_NL');
        return $output->formatLocalized('%B %Y');
    }
}