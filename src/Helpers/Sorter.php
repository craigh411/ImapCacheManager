<?php
namespace Humps\ImapCacheManager\Helpers;

use Exception;
use Humps\MailManager\Components\Contracts\Message;

class Sorter
{
    /**
     * Sorts the given array of objects by the given sortBy.
     * @param array $array
     * @param string $sortBy The class variable to sort by e.g. 'subject' would make a call to getSubject().
     * The return value of the called method should be a string.
     * @param bool $reverse
     * @param bool $ignoreCase
     * @return bool
     */
    public static function sort(array &$array, $sortBy, $reverse = false, $ignoreCase = true)
    {
        $sortBy = 'get' . ucfirst(strtolower($sortBy));

        return usort($array, function ($a, $b) use ($sortBy, $reverse, $ignoreCase) {
            if ($reverse) {
                if($ignoreCase){
                    return strcasecmp($b->$sortBy(), $a->$sortBy());
                }

                return strcmp($b->$sortBy(), $a->$sortBy());
            }

            if($ignoreCase){
                return strcasecmp($a->$sortBy(), $b->$sortBy());
            }
            return strcmp($a->$sortBy(), $b->$sortBy());
        });
    }

    /**
     * Sorts the
     * @param bool $reverse
     */
    public static function sortMessagesByDate(array &$messages, $reverse = true)
    {
        if(isset($messages[0]) && $messages[0] instanceof Message){
            return usort($messages, function ($a, $b) use ($reverse) {
                if($reverse){
                    return $b->getDate()->gte($a->getDate());
                }
                return $a->getDate()->gte($b->getDate());
            });
        }else if(!count($messages)){
            return true;
        }

        return false;
    }
}