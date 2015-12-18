<?php

class CompanyInfo
{
    static public $filesFolder = 'application.runtime.company.pdf';
    static public $debugFolder = 'application.runtime.company.debug';

    const CACHE_PREFIX = "CompanyInfo_";
    const DOMAIN = 'https://egrul.nalog.ru';

    /** @var PDFParser */
    static public $debug = false;
    static private $_number;
    static private $_pdfParser;
    static private $_parser;
    static private $_rawInfo;
    static private $_group = '';
    static private $_groupSub = 0;
    static public $useCache = true;
    static private $_groups
        = array(
            'Фамилия,'                                                                      => 'personal',
            'Сведения о гражданстве'                                                        => 'citizenship_info',
            'Сведения о регистрирующем органе по месту жительства'                          => 'register_department_info',
            'Сведения о видах экономической деятельности по Общероссийскому классификатору' => 'okved',
            'Сведения о дополнительных видах деятельности'                                  => 'advanced_okved',
            'Сведения о записях, внесенных в ЕГРИП'                                         => 'egrip',
            'Пенсионного фонда Российской Федерации'                                        => 'pension',
            'Сведения об учете в налоговом органе'                                          => 'nalog_info',
            'Сведения о регистрации индивидуального предпринимателя'                        => 'register_info',
            'Сведения об уставном капитале'                                                 => 'kapital',
            'Сведения о лице, имеющем право без доверенности действовать от имени'          => 'trusted_personal',
            'Сведения об учредителях'                                                       => 'founders',
            'Сведения о держателе реестра акционеров акционерного общества'                 => 'reester_holder',
            'Сведения о лицензиях'                                                          => 'license_info',
            'Сведения о записях, внесенных в Единый государственный реестр юридических лиц' => 'records',
            'Сведения о статусе записи'                                                     => 'record_status',
            'Наименование'                                                                  => 'names',
            'Адрес (место нахождения)'                                                      => 'address',
            'Сведения о регистрации'                                                        => 'register_info',
            'Сведения о регистрирующем органе по месту нахождения юридического лица'        => 'register_department_info',
            'Сведения о состоянии юридического лица'                                        => 'status',
            'Сведения о прекращении'                                                        => 'termination',
        );

    static private $_names
        = array(
            'Должность'                                                            => 'appointment',
            'Вид лицензируемой деятельности, на который выдана'                    => 'license_type',
            'Номер лицензии'                                                       => 'license_number',
            'Размер доли (в процентах)'                                            => 'cost_percent',
            'Дата лицензии'                                                        => 'license_date',
            'Дата начала действия лицензии'                                        => 'license_start',
            'Полное наименование'                                                  => 'full_name',
            'Субъект Российской Федерации'                                         => 'subdivision',
            'ИНН'                                                                  => 'inn',
            'КПП'                                                                  => 'kpp',
            'Способ образования'                                                   => 'method_forming',
            'Состояние'                                                            => 'status',
            'Улица (проспект,'                                                     => 'address',
            'Дом (владение'                                                        => 'house',
            'Корпус (строение и т.п.)'                                             => 'building',
            'район'                                                                => 'district',
            'Сокращенное наименование'                                             => 'short_name',
            'Фамилия'                                                              => 'secondname',
            'Почтовый индекс'                                                      => 'postal',
            'Имя'                                                                  => 'name',
            'Отчество'                                                             => 'patronymic',
            'Пол'                                                                  => 'sex',
            'ГРН и дата внесения'                                                  => 'grn_and_date',
            'ГРН и дата записи, в которую внесены исправления'                     => 'grn_and_date_fix',
            'Номинальная стоимость доли (в рублях)'                                => 'cost_rub',
            'Гражданство'                                                          => 'citizenship',
            'Дата присвоения ОГРНИП'                                               => 'ogrn_date',
            'ОГРНИП'                                                               => 'ogrn',
            'Дата регистрации'                                                     => 'register_date',
            'Наименование органа, зарегистрировавшего индивидуального'             => 'register_department',
            'Наименование регистрирующего органа'                                  => 'register_department_name',
            'Адрес регистрирующего органа'                                         => 'register_department_address',
            'Идентификационный номер налогоплательщика'                            => 'inn',
            'Размер (в рублях)'                                                    => 'size_rub',
            'Дата постановки на учет'                                              => 'record_date',
            'Наименование налогового органа'                                       => 'nalog_department',
            'Регистрационный номер'                                                => 'register_number',
            'Наименование территориального органа Пенсионного фонда'               => 'pension_name',
            'Код и наименование вида деятельности'                                 => 'code_activity',
            'Наименование документа'                                               => 'doc_name',
            'Номер документа'                                                      => 'doc_number',
            'Дата документа'                                                       => 'doc_date',
            'Вид'                                                                  => 'type',
            'Страна происхождения'                                                 => 'born_country',
            'Адрес (место нахождения) встране происхождения'                       => 'born_country_address',
            'Серия, номер и дата выдачи свидетельства'                             => 'serial_number_date',
            'Причина внесения записи в ЕГРИП'                                      => 'reason_for_recording',
            'Город (волость и т.п.)'                                               => 'town',
            'Населенный пункт'                                                     => 'village',
            'Причина внесения записи в ЕГРЮЛ'                                      => 'reason_for_recording',
            'Дата присвоения ОГРН'                                                 => 'ogrn_date',
            'ОГРН'                                                                 => 'ogrn',
            'Статус записи'                                                        => 'record_status',
            'Способ прекращения'                                                   => 'method',
            'Дата прекращения'                                                     => 'termination_date',
            'Наименование органа, внесшего запись о прекращении юридического лица' => 'termination_department_name'

        );

    static private $_stopGroups
        = array(
            'Сведения о документах, представленных при внесении записи в ЕГРИП',
            'Сведения  сформированы  с  сайта  ФНС  России  с  использованием',
            'Сведения о свидетельстве, подтверждающем факт внесения записи в ЕГРИП',
            'Сведения о документах, представленных при внесении записи в ЕГРЮЛ',
            'Сведения о свидетельстве, подтверждающем факт внесения записи в ЕГРЮЛ',
        );

    public static function isInn($number)
    {
        $length = strlen((string)$number);
        return $length == 10 || $length == 12;
    }

    public static function isOgrn($number)
    {
        $length = strlen((string)$number);
        return $length == 13 || $length == 15;
    }

    public static function isOrganization($number)
    {
        $length = strlen((string)$number);
        return $length == 10 || $length == 13;
    }

    public static function setCache($number, $data)
    {
        if (self::$useCache) {
            return Yii::app()->cache->set(self::CACHE_PREFIX . $number, $data, 3600);
        }
        return false;
    }

    public static function getFromCache($number)
    {
        if (self::$useCache) {
            return Yii::app()->cache->get(self::CACHE_PREFIX . $number);
        }
        return false;
    }


    private static function getFileUrl($number, $isOrganization, $captchaToken, $recognizedCaptcha)
    {
        $parser = self::getParser();
        $parser->headers->X_Requested_With = "XMLHttpRequest";
        $parser->headers->Accept = "application/json, text/javascript, */*; q=0.01";
        $parser->headers->Content_Type = "application/x-www-form-urlencoded";
        $parser->headers->Accept_Encoding = "gzip,deflate";
        $parser->headers->Accept_Charset = "UTF-8,*;q=0.5";


        $parser->post["method"] = "post";
        $parser->post["kind"] = $isOrganization ? "ul" : "fl";
        $parser->post["srchUl"] = "ogrn";
        $parser->post["ogrninnul"] = $isOrganization ? $number : "";
        $parser->post["regionul"] = "";
        $parser->post["srchFl"] = "ogrn";
        $parser->post["ogrninnfl"] = !$isOrganization ? $number : "";
        $parser->post["namul"] = "";
        $parser->post["fam"] = "";
        $parser->post["nam"] = "";
        $parser->post["otch"] = "";
        $parser->post["region"] = "";
        $parser->post["captcha"] = $recognizedCaptcha;
        $parser->post["captchaToken"] = $captchaToken;

        $content = $parser->getContent(self::DOMAIN);
        $parser->post = [];
        return json_decode($content, 1);
    }

    private static function extractCaptcha()
    {
        $parser = self::getParser();
        if (!$content = $parser->getContent(self::DOMAIN)) {
            CompanyException::downloadError();
        }
        preg_match('/<img[^>]*?src=\"(\/static\/captcha.html\?a=(.*))\"/iU', $content, $arr);
        
        if (is_array($arr) && count($arr)) {
            $url = self::DOMAIN . $arr[1];
            $captchaToken = $arr[2];
            return ["url" => $url, "token" => $captchaToken];
        } else {
            throw new Exception("Not find", 1);
        }
    }

    private static function recognizeCaptcha($url)
    {
        return \Yii::app()->captcha->get($url, true);
    }

    public static function get($number, $parsePDF = true, $force = false)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        self::$_number = $number;
        $isIp = !self::isOrganization($number);

        if (!$force && $cache = self::getFromCache($number)) {
            return $cache;
        } elseif (!$force && ($file = self::getStorageFile(self::$_number)) && $parsePDF) {
            self::getPdfParser()->parseFile($file);
            self::formArray();
            $data = self::rawInfoAsObject();
            return $data;
        }

        $captcha = self::extractCaptcha();

        $fileUrl = $captcha["url"];
        $captchaToken = $captcha["token"];
        $recognizedCaptcha = self::recognizeCaptcha($fileUrl);

        $json = self::getFileUrl(self::$_number, !$isIp, $captchaToken, $recognizedCaptcha);
        if (array_key_exists("ERRORS", $json)) {
            throw new companyInfoException(print_r($json["ERRORS"], 1));
        } else {
            if ($parsePDF) {
                $file = self::DOMAIN . "/download/" . $json["rows"][0]["T"];
                self::file($file);
                $data = self::rawInfoAsObject();
                self::setCache(self::$_number, $data);
                return $data;
            } else {
                var_dump($json);
                return $json["rows"][0];
            }
        }
    }

    private static function saveFile($path)
    {
        $tmpFileName = tempnam(\Yii::app()->runtimePath, "pdf");
        $content = self::getParser()->getContent($path);
        if (self::getParser()->HTTP_CODE !== 200) {
            $document = \phpQuery::newDocumentHTML($content);
            $error = strip_tags($document->find('#error h1')->html());
            CompanyException::downloadError($error);
        }
        file_put_contents($tmpFileName, $content);
        return $tmpFileName;
    }

    public static function getStorageFile($number)
    {
        $file = Yii::getPathOfAlias(self::$filesFolder) . DIRECTORY_SEPARATOR . $number . '.pdf';
        if (file_exists($file)) {
            return $file;
        } else {
            return false;
        }
    }

    private static function storageFile($path)
    {
        if (!is_dir($folder = Yii::getPathOfAlias(self::$filesFolder))) {
            mkdir($folder, 0777, true);
        }
        $file = $folder . DIRECTORY_SEPARATOR . self::$_number . '.pdf';
        if (!file_exists($file) && file_exists($path)) {
            rename($path, $file);
        } elseif (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * @return \ext\companyInfo\CompanyData
     */
    public static function rawInfoAsObject()
    {
        $data = new \ext\companyInfo\CompanyData(self::$_rawInfo);
        return $data;
    }

    /**
     * @param $file
     *
     * @return array
     * @throws Exception
     */
    public static function file($file)
    {
        try {
            $path = self::saveFile($file);
            self::getPdfParser()->parseFile($path);
            self::formArray();
            self::storageFile($path);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        self::debug(self::$_rawInfo);
        return self::$_rawInfo;
    }

    private static function debug($content, $prefix = null, $append = false)
    {
        if (self::$debug) {
            $name = self::$_number . ($prefix ? "_" . $prefix : "") . ".txt";
            $folder = Yii::getPathOfAlias(self::$debugFolder);
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            if ($append) {
                file_put_contents($folder . DIRECTORY_SEPARATOR . $name, $content . "\n", FILE_APPEND);
            } else {
                file_put_contents($folder . DIRECTORY_SEPARATOR . $name, print_r($content, 1));
            }
        }
    }

    public static function fixLineArrayMap($elem)
    {
        if (is_int($elem)) {
            return chr((int)$elem);
        } else {
            return $elem;
        }
    }

    private static function getLinePattern($codes)
    {
        return join("", array_map('self::fixLineArrayMap', $codes));
    }

    private static function fixParserTextBugs(&$line)
    {
        // Баги в тексте

        $codes = [0, "\\f"];
        $line = str_replace(self::getLinePattern($codes), ") ", $line);

        $codes = self::formCodes("<");
        $line = str_replace(self::getLinePattern($codes), " В", $line);

        $codes = self::formCodes("IJ?DJ:S?GB?");
        $line = str_replace(self::getLinePattern($codes), "ПРЕКРАЩЕНИЕ", $line);

        $codes = self::formCodes("J?=BKLJ:PBY");
        $line = str_replace(self::getLinePattern($codes), "РЕГИСТРАЦИЯ", $line);

        $codes = self::formCodes("NE");
        $line = str_replace(self::getLinePattern($codes), " ФЛ", $line);

        $codes = self::formCodes("bg^b\\b^mZevgh]h");
        $line = str_replace(self::getLinePattern($codes), "индивидуального ", $line);

        $codes = self::formCodes("ij_^ijbqbfZl_ey");
        $line = str_replace(self::getLinePattern($codes), "предпринимателя ", $line);

        $codes = self::formCodes("xjb^bq_kdh]h");
        $line = str_replace(self::getLinePattern($codes), "юридического ", $line);

        $codes = self::formCodes("ebpz");
        $line = str_replace(self::getLinePattern($codes), "лица", $line);

        $codes = self::formCodes("\\");
        $line = str_replace(self::getLinePattern($codes), "в", $line);

        $codes = self::formCodes("kljZq_");
        $line = str_replace(self::getLinePattern($codes), "стране", $line);

        $codes = self::formCodes("=:A:");
        $line = str_replace(self::getLinePattern($codes), "ГАЗА", $line);

        $codes = self::formCodes(">EY");
        $line = str_replace(self::getLinePattern($codes), "ДЛЯ", $line);
    }

    private static function formCodes($string, $withBegin = true)
    {
        if ($withBegin) {
            $result = [0, 3];
        } else {
            $result = [];
        }
        for ($i = 0; $i < strlen($string); $i++) {
            $result[] = 2;
            $result[] = $string[$i];
        }
        return $result;
    }

    private static function formArray()
    {
        $res = array();
        foreach (self::$_pdfParser->pdf->getPages() as $page) {
            $content = trim($page->getText());

            $arr = explode("\n", $content);
            $cline = '';
            $group = "";
            $isSubElement = false;
            $SubElement = 0;
            foreach ($arr as $line) {
                self::fixParserTextBugs($line);
                self::debug($line, "raw", FILE_APPEND);
                $arrCline = explode(chr(9), $cline);
                if ((!is_numeric($arrCline[0]) && $cline) || ($isSubElement = is_numeric(trim($cline)))) {
                    if ($isSubElement) {
                        $SubElement = (int)trim($cline);
                    } else {
                        $group .= ' ' . trim($cline);
                        $SubElement = 0;
                    }
                    $cline = "";
                }

                if (!$cline || is_numeric($arrCline[0])) {
                    $cline .= ' ' . ltrim($line);
                    $arrCline = explode(chr(9), $cline);
                }

                if (count($arrCline) == 4) {
                    self::checkGroup($group, $SubElement);
                    self::push($res, self::$_group, self::$_groupSub, $arrCline[1], $arrCline[2]);
                    $cline = '';
                    $group = '';
                }
            }
        }
        array_shift($res);
        return self::$_rawInfo = $res;
    }

    private static function prepareValue($text)
    {
        $text = trim($text);
        $text = html_entity_decode($text);
        return $text;
    }

    private static function push(&$arr, $group, $index, $name, $value)
    {
        $group = self::getGroupName($group);
        $name = self::getName($name);
        $value = self::prepareValue($value);

        if (array_key_exists($group, $arr) && $index) {
            $temp = $arr[$group];
            if (!array_key_exists(0, $temp)) {
                unset($arr[$group]);
                $arr[$group][0] = $temp;
            }
            $arr[$group][$index - 1][$name] = $value;
        } else {
            $arr[$group][$name] = $value;
        }
    }

    private static function checkGroup($group, $SubElement)
    {
        $group = trim($group);
        $stop = false;
        if ($group && !$stop = self::isStopGroup($group)) {
            self::$_group = $group;
            self::$_groupSub = 0;
        }
        if ($SubElement && !$stop) {
            self::$_groupSub = $SubElement;
        }
        return self::$_group;
    }

    private static function findInArrayRule($arr, $line)
    {
        foreach ($arr as $rule => $name) {
            if (is_int($rule)) {
                $rule = $name;
                $name = true;
            }
            if (mb_stripos($line, $rule, 0, "UTF-8") !== false) {
                return $name;
            }
        }
        return false;
    }

    private static function isStopGroup($line)
    {
        return (bool)self::findInArrayRule(self::$_stopGroups, $line);
    }

    private static function getName($line)
    {
        if ($result = self::findInArrayRule(self::$_names, $line)) {
            return $result;
        } else {
            return $line;
        }
    }

    private static function getGroupName($line)
    {
        if ($result = self::findInArrayRule(self::$_groups, $line)) {
            return $result;
        } else {
            return $line;
        }
    }

    public static function getPdfParser()
    {
        if (!self::$_pdfParser) {
            self::$_pdfParser = new \PDFParser();
        }
        return self::$_pdfParser;
    }

    public static function getParser()
    {
        if (!self::$_parser) {
            self::$_parser = new CParser();
//            self::$_parser->proxyHost = '127.0.0.1';
//            self::$_parser->proxyPort = '8888';
        }
        return self::$_parser;
    }
}