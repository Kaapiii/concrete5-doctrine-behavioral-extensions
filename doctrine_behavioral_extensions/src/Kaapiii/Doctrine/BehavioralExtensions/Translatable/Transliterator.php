<?php

namespace Kaapiii\Doctrine\BehavioralExtensions\Translatable;

/**
 * Transliterator
 *
 * @author markus.liechti
 */
class Transliterator {
    
    /**
     * Replace all German and French specail characters
     * 
     * @param string $slug
     * @param string $separatorUsed
     * @param string $objectBeingSlugged
     * 
     * @return string
     */
    public function replaceSecialSigns($slug, $separatorUsed, $objectBeingSlugged){
        $specialCharactersPairs = array(
                // German special characters
                'ä'=>'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'oe', 'Ü' => 'Üe', 'ß' => 'ss',
                // French special characters
                'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Æ'=>'A', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'æ'=>'a', 'Ç'=>'C', 'ç'=>'c',
                'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'Î'=>'I', 'Ï'=>'I', 'î'=>'i', 'ï'=>'i', 'Ô'=>'O',
                'Œ'=> 'O', 'œ'=>'o', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'Ÿ'=>'Y', 'ÿ'=>'y',
                // Ohter special characters
                'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'Ã'=>'A', 'Å'=>'A', 'Ì'=>'I', 'Í'=>'I', 'Ñ'=>'N', 
                'Ò'=>'O', 'Ó'=>'O', 'Õ'=>'O', 'Ø'=>'O', 'Ý'=>'Y', 'Þ'=>'B', 'à'=>'a', 'á'=>'a', 'ã'=>'a', 'å'=>'a',
                'ì'=>'i', 'í'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ø'=>'o', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            );
        return \Gedmo\Sluggable\Util\Urlizer::urlize(strtr($slug, $specialCharactersPairs));
    }
}
