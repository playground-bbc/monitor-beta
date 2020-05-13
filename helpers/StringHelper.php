<?php
namespace app\helpers;

use yii;


use Stringy\Stringy as S;
use function Stringy\create as s;


use Stringizer\Stringizer;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * StringHelper wrapper for string function.
 *
 */
class StringHelper
{

	
    /**
     * [convert a product string in to array ,delete / and () if exclude words less to 3]
     * @param  [string] $product   [product to convert]
     * @return [array] $data [ej: ['ThinQ','Aurora','Black']]
     */
    public static function structure_product_to_search($product)
    {
        $black_list_words = ['con','por','Casa','oficina','Blue','Ice','Fit','Frontal'];
        // if acents
        $product = self::normalizeChars($product);
        // eliminamos / y -
        $s = trim(preg_replace('/[\W]+/', ' ', $product));
        // convertimos en array 
        $product_exploded = explode(' ',$s);

        $data = [];
        // recorremos el array
        foreach($product_exploded as $product){
            $stringy = S::create($product);
            // contamos si el product a buscar es mayor a 3 palabras o si es un valor numeric
            if(count($stringy) >= 3){
                if(!is_numeric($product)){
                    // si no esta en el array para evitar repetidos
                    if(!in_array($product,$data)){
                        // if not black_list_words
                        if(!in_array($product,$black_list_words)){
                            $data[] = $product;
                        }

                    }
                }
            }
            
        }
        return $data;

    }
    /**
     * https://github.com/danielstjules/Stringy#containsanyarray-needles--boolean-casesensitive--true-
     */
    public static function containsAny($sentence,$data){
        return s($sentence)->containsAny($data,false);
    }
    /**
     * https://github.com/danielstjules/Stringy#containsallarray-needles--boolean-casesensitive--true-
     */
    public static function containsAll($sentence,$data){
        return s($sentence)->containsAll($data,false);
    }
    /**
     * https://github.com/jasonlam604/Stringizer#containscount
     */
    public static function containsCount($sentence,$word){
        $s = new Stringizer($sentence);
        return $s->containsCount($word); // true, case insensitive
    }
    /**
     * https://github.com/jasonlam604/Stringizer#dasherize
     */
    public static function dasherize($sentence){
        $s = new Stringizer($sentence);
        return $s->dasherize();
    }

    /**
     * https://github.com/jasonlam604/Stringizer#lowercase
     */
    public static function lowercase($sentence){
        $s = new Stringizer($sentence);
        $s->lowercase();
        return $s->getString();
    }
    /**
     * https://github.com/jasonlam604/Stringizer#replace
     */
    public static function replace($sentence,$word,$replace){
        $s = new Stringizer($sentence);
        $s->replace($word, $replace);
        return $s->getString();
    }
    /**
     * https://github.com/jasonlam604/Stringizer#removeascii
     */
    public static function removeNonAscii($sentence){
        $s = new Stringizer($sentence);
        $s->removeNonAscii(); 
        return $s->getString();
    }
    /**
     * https://github.com/jasonlam604/Stringizer#isascii
     */
    public static function isAscii($sentence){
        $s = new Stringizer($sentence);
        return $s->isAscii();
    }
    /**
     * https://github.com/jasonlam604/Stringizer#replaceaccents
     */
    public static function replaceAccents($sentence){
        $s = new Stringizer($sentence);
        $s->replaceAccents(); 
        return $s->getString();
    }

    /**
     * https://github.com/jasonlam604/Stringizer#contains
     */
    public static function containsIncaseSensitive($sentence,$word){
        $s = new Stringizer($sentence);
        return $s->containsIncaseSensitive($word); // true, case insensitive
    }

    /**
     * https://github.com/jasonlam604/Stringizer#containscount
     */
    public static function containsCountIncaseSensitive($sentence,$word){
        $s = new Stringizer($sentence);
        return $s->containsCountIncaseSensitive($word); // true, case insensitive
    }

    /**
     * https://github.com/jasonlam604/Stringizer#replace
     */
    public static function replaceIncaseSensitive($sentence,$word,$replace){
        $s = new Stringizer($sentence);
        $s->replaceIncaseSensitive($word, $replace); // Fizz bar Fizz bar Fizz bar
        return $s->getString();
    }
    /**
     * https://github.com/jasonlam604/Stringizer#collapsewhitespace
     */
    public static function collapseWhitespace($sentence){
        $s = new Stringizer($sentence);
        $s->collapseWhitespace(); // ȘŦŗÍñĝ ìzĕŕ
        return $s->getString();
    }
    /**
     * https://github.com/jasonlam604/Stringizer#striptags
     */
    public static function stripTags($sentence){
        $s = new Stringizer($sentence);
        return $s->stripTags();
    }

    /**
     * https://github.com/jasonlam604/Stringizer#isempty
     */
    public static function isEmpty($sentence){
        $s = new Stringizer($sentence);
        return $s->isEmpty(); // true;
    }
    /**
     * [replacingSpacesWithUnderscores convert sentence in camel to snake and replace # to _ and chance '_' to ' ']
     * @param  [type] $sentence [description]
     * @return [type]           [description]
     */
    public static function replacingSpacesWithUnderscores($sentence){
        $s = new Stringizer($sentence);
        $collapseDiagonalpace = self::replaceIncaseSensitive($s->camelToSnake(),'/','_');
        $collapseUnderscorespace = self::replaceIncaseSensitive($collapseDiagonalpace,'#','_');
        $collapseWhitespace = self::replaceIncaseSensitive($collapseUnderscorespace,' ','_');
        return $collapseWhitespace; // true;
    }
    /**
     * https://github.com/jasonlam604/Stringizer#substring
     */
    public static function substring($sentence,$start,$end){
        $s = new Stringizer($sentence);
        $s->subString($start,$end);
        return $s->getString();
    }
    /**
     * [ensureRightPoints add to the sentence "...."]
     * @param  [string] $sentence 
     * @return [string]
     */
    public static function ensureRightPoints($sentence){
        $s = new Stringizer($sentence);
        $s->ensureRight("....");
        return $s->getString();
    }
    /**
     * https://github.com/jasonlam604/Stringizer#isurl
     */
    public static function isUrl($url)
    {
        $s = new Stringizer($url);
        return $s->isUrl(); // true
    }
    /**
     * [getValidUrls return valid urls]
     * @param  [array/string] $urls
     * @return [array]
     */
    public static function getValidUrls($urls){
        
        $urls = (!is_array($urls)) ? explode(',', $urls) : $urls;

        $valid_urls = [];
        foreach ($urls as $url) {
            if (self::isUrl($url)) {
                array_push($valid_urls, $url);
            }
        }
        return $valid_urls;
    }

    
    public static function in_array_r($needle, $haystack, $strict = true) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replace language-specific characters by ASCII-equivalents.
     * @param string $s
     * @return string
     */
    public static function normalizeChars($s) {
        $replace = array(
            'ъ'=>'-', 'Ь'=>'-', 'Ъ'=>'-', 'ь'=>'-',
            'Ă'=>'A', 'Ą'=>'A', 'À'=>'A', 'Ã'=>'A', 'Á'=>'A', 'Æ'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ä'=>'Ae',
            'Þ'=>'B',
            'Ć'=>'C', 'ץ'=>'C', 'Ç'=>'C',
            'È'=>'E', 'Ę'=>'E', 'É'=>'E', 'Ë'=>'E', 'Ê'=>'E',
            'Ğ'=>'G',
            'İ'=>'I', 'Ï'=>'I', 'Î'=>'I', 'Í'=>'I', 'Ì'=>'I',
            'Ł'=>'L',
            'Ñ'=>'N', 'Ń'=>'N',
            'Ø'=>'O', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe',
            'Ş'=>'S', 'Ś'=>'S', 'Ș'=>'S', 'Š'=>'S',
            'Ț'=>'T',
            'Ù'=>'U', 'Û'=>'U', 'Ú'=>'U', 'Ü'=>'Ue',
            'Ý'=>'Y',
            'Ź'=>'Z', 'Ž'=>'Z', 'Ż'=>'Z',
            'â'=>'a', 'ǎ'=>'a', 'ą'=>'a', 'á'=>'a', 'ă'=>'a', 'ã'=>'a', 'Ǎ'=>'a', 'а'=>'a', 'А'=>'a', 'å'=>'a', 'à'=>'a', 'א'=>'a', 'Ǻ'=>'a', 'Ā'=>'a', 'ǻ'=>'a', 'ā'=>'a', 'ä'=>'ae', 'æ'=>'ae', 'Ǽ'=>'ae', 'ǽ'=>'ae',
            'б'=>'b', 'ב'=>'b', 'Б'=>'b', 'þ'=>'b',
            'ĉ'=>'c', 'Ĉ'=>'c', 'Ċ'=>'c', 'ć'=>'c', 'ç'=>'c', 'ц'=>'c', 'צ'=>'c', 'ċ'=>'c', 'Ц'=>'c', 'Č'=>'c', 'č'=>'c', 'Ч'=>'ch', 'ч'=>'ch',
            'ד'=>'d', 'ď'=>'d', 'Đ'=>'d', 'Ď'=>'d', 'đ'=>'d', 'д'=>'d', 'Д'=>'D', 'ð'=>'d',
            'є'=>'e', 'ע'=>'e', 'е'=>'e', 'Е'=>'e', 'Ə'=>'e', 'ę'=>'e', 'ĕ'=>'e', 'ē'=>'e', 'Ē'=>'e', 'Ė'=>'e', 'ė'=>'e', 'ě'=>'e', 'Ě'=>'e', 'Є'=>'e', 'Ĕ'=>'e', 'ê'=>'e', 'ə'=>'e', 'è'=>'e', 'ë'=>'e', 'é'=>'e',
            'ф'=>'f', 'ƒ'=>'f', 'Ф'=>'f',
            'ġ'=>'g', 'Ģ'=>'g', 'Ġ'=>'g', 'Ĝ'=>'g', 'Г'=>'g', 'г'=>'g', 'ĝ'=>'g', 'ğ'=>'g', 'ג'=>'g', 'Ґ'=>'g', 'ґ'=>'g', 'ģ'=>'g',
            'ח'=>'h', 'ħ'=>'h', 'Х'=>'h', 'Ħ'=>'h', 'Ĥ'=>'h', 'ĥ'=>'h', 'х'=>'h', 'ה'=>'h',
            'î'=>'i', 'ï'=>'i', 'í'=>'i', 'ì'=>'i', 'į'=>'i', 'ĭ'=>'i', 'ı'=>'i', 'Ĭ'=>'i', 'И'=>'i', 'ĩ'=>'i', 'ǐ'=>'i', 'Ĩ'=>'i', 'Ǐ'=>'i', 'и'=>'i', 'Į'=>'i', 'י'=>'i', 'Ї'=>'i', 'Ī'=>'i', 'І'=>'i', 'ї'=>'i', 'і'=>'i', 'ī'=>'i', 'ĳ'=>'ij', 'Ĳ'=>'ij',
            'й'=>'j', 'Й'=>'j', 'Ĵ'=>'j', 'ĵ'=>'j', 'я'=>'ja', 'Я'=>'ja', 'Э'=>'je', 'э'=>'je', 'ё'=>'jo', 'Ё'=>'jo', 'ю'=>'ju', 'Ю'=>'ju',
            'ĸ'=>'k', 'כ'=>'k', 'Ķ'=>'k', 'К'=>'k', 'к'=>'k', 'ķ'=>'k', 'ך'=>'k',
            'Ŀ'=>'l', 'ŀ'=>'l', 'Л'=>'l', 'ł'=>'l', 'ļ'=>'l', 'ĺ'=>'l', 'Ĺ'=>'l', 'Ļ'=>'l', 'л'=>'l', 'Ľ'=>'l', 'ľ'=>'l', 'ל'=>'l',
            'מ'=>'m', 'М'=>'m', 'ם'=>'m', 'м'=>'m',
            'ñ'=>'n', 'н'=>'n', 'Ņ'=>'n', 'ן'=>'n', 'ŋ'=>'n', 'נ'=>'n', 'Н'=>'n', 'ń'=>'n', 'Ŋ'=>'n', 'ņ'=>'n', 'ŉ'=>'n', 'Ň'=>'n', 'ň'=>'n',
            'о'=>'o', 'О'=>'o', 'ő'=>'o', 'õ'=>'o', 'ô'=>'o', 'Ő'=>'o', 'ŏ'=>'o', 'Ŏ'=>'o', 'Ō'=>'o', 'ō'=>'o', 'ø'=>'o', 'ǿ'=>'o', 'ǒ'=>'o', 'ò'=>'o', 'Ǿ'=>'o', 'Ǒ'=>'o', 'ơ'=>'o', 'ó'=>'o', 'Ơ'=>'o', 'œ'=>'oe', 'Œ'=>'oe', 'ö'=>'oe',
            'פ'=>'p', 'ף'=>'p', 'п'=>'p', 'П'=>'p',
            'ק'=>'q',
            'ŕ'=>'r', 'ř'=>'r', 'Ř'=>'r', 'ŗ'=>'r', 'Ŗ'=>'r', 'ר'=>'r', 'Ŕ'=>'r', 'Р'=>'r', 'р'=>'r',
            'ș'=>'s', 'с'=>'s', 'Ŝ'=>'s', 'š'=>'s', 'ś'=>'s', 'ס'=>'s', 'ş'=>'s', 'С'=>'s', 'ŝ'=>'s', 'Щ'=>'sch', 'щ'=>'sch', 'ш'=>'sh', 'Ш'=>'sh', 'ß'=>'ss',
            'т'=>'t', 'ט'=>'t', 'ŧ'=>'t', 'ת'=>'t', 'ť'=>'t', 'ţ'=>'t', 'Ţ'=>'t', 'Т'=>'t', 'ț'=>'t', 'Ŧ'=>'t', 'Ť'=>'t', '™'=>'tm',
            'ū'=>'u', 'у'=>'u', 'Ũ'=>'u', 'ũ'=>'u', 'Ư'=>'u', 'ư'=>'u', 'Ū'=>'u', 'Ǔ'=>'u', 'ų'=>'u', 'Ų'=>'u', 'ŭ'=>'u', 'Ŭ'=>'u', 'Ů'=>'u', 'ů'=>'u', 'ű'=>'u', 'Ű'=>'u', 'Ǖ'=>'u', 'ǔ'=>'u', 'Ǜ'=>'u', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'У'=>'u', 'ǚ'=>'u', 'ǜ'=>'u', 'Ǚ'=>'u', 'Ǘ'=>'u', 'ǖ'=>'u', 'ǘ'=>'u', 'ü'=>'ue',
            'в'=>'v', 'ו'=>'v', 'В'=>'v',
            'ש'=>'w', 'ŵ'=>'w', 'Ŵ'=>'w',
            'ы'=>'y', 'ŷ'=>'y', 'ý'=>'y', 'ÿ'=>'y', 'Ÿ'=>'y', 'Ŷ'=>'y',
            'Ы'=>'y', 'ž'=>'z', 'З'=>'z', 'з'=>'z', 'ź'=>'z', 'ז'=>'z', 'ż'=>'z', 'ſ'=>'z', 'Ж'=>'zh', 'ж'=>'zh'
        );
        return strtr($s, $replace);
    }

    /**
     * [remove_emoji remove emoji form sentence]
     * @param  [string] $text [description]
     * @return [string]       [description]
     */
    public static function remove_emoji ($text){
        //$text = self::replaceAccents($text);
        return preg_replace('/[[:^print:]]/', '', $text);
    }
    /**
     * [getDomain get domain form url]
     * @param  [string] $url
     * @return [string] 
     */
    public static function getDomain($url){
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return FALSE;
    }


    public static function parses_url($url, $param){
        // Use parse_url() function to parse the URL  
        // and return an associative array which 
        // contains its various components 
        $url_components = parse_url($url); 
          
        // Use parse_str() function to parse the 
        // string passed via URL 
        parse_str($url_components['query'], $params); 
              
        // return result 
        return $params[$param]; 
    }


    public static function sortDataAnalysisTable($tds = [],$link)
    {
        if (!empty($tds)) {
            $headersTarget = ['name','total'];
            
            $statistics = [];
            $size = (sizeof($tds) >= 5) ? 5 : sizeof($tds);

            for ($i=1; $i < $size; $i++) { 
                for ($h=0; $h < sizeof($headersTarget); $h++) { 
                    $tmp[$headersTarget[$h]] = $tds[$i][$h];
                    $tmp['url'] = $link;
                }
                $statistics[] = $tmp;
            }
            
            return $statistics;
        }
    }

}