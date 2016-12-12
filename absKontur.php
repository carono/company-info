<?php

namespace carono\company;

use GuzzleHttp\Client;

class absKontur
{
    const CACHE_COOKIE = "kontur_cookie";
    protected static $host = "https://focus.kontur.ru";
    protected static $loginUrl = "";
    protected static $names
        = [
            "инн"              => "inn",
            "кпп"              => "kpp",
            "огрн"             => "ogrn",
            "окпо"             => "okpo",
            "дата образования" => "create_date"
        ];
    public static $proxy;
    protected static $_parser = null;



    /**
     * @param $inn
     * @param $force
     *
     * @return mixed|string
     * @throws KonturException
     */
    protected static function getContent($inn, $force)
    {
        if (!trim($inn)) {
            KonturException::wrongInn();
        }
        $query = "/search?query=$inn";
        $response = static::parser()->request('GET', static::$host . $query);
        $content = $response->getBody()->getContents();
        $result = \phpQuery::newDocument($content);
        if ($result->find('div.main-summary')->html()) {
            return $content;
        } elseif (!$content) {
            KonturException::contentNofFound();
        } elseif ($result->find('div.filterDetails_summaryWrap')->html()) {
            KonturException::moreAtOne();
        } else {
            KonturException::notFound();
        }
    }

    /**
     * @param $ogrn
     * @param $force
     *
     * @return \phpQuery|\QueryTemplatesParse|\QueryTemplatesSource|\QueryTemplatesSourceQuery|string
     * @throws KonturException
     */
    public static function getActivitiesContent($ogrn, $force)
    {
        $query = "/activities?query=$ogrn";
        $response = static::parser()->request('GET', static::$host . $query);
        $content = $response->getBody()->getContents();
        $result = \phpQuery::newDocument($content);
        $div = $result->find('.activities-list');
        if (strip_tags($div->html())) {
            return $div->html();
        } else {
            KonturException::notFound();
        }
    }

    public static function setTokenCookie($cookie)
    {
        throw new KonturException('Not implemented');
    }

    /**
     * @return Client
     */
    public static function parser()
    {
        if (!static::$_parser) {
            return static::$_parser = new Client();
        } else {
            return static::$_parser;
        }
    }


    private static function clearCookie()
    {
        throw new KonturException('Not implemented');
    }

    public static function getTokenCookie()
    {
        throw new KonturException('Not implemented');
    }

    public static function isLogin()
    {
        throw new KonturException('Not implemented');
    }

    public static function login($url)
    {
        throw new KonturException('Not implemented');
    }

    public static function logout()
    {
        throw new KonturException('Not implemented');
    }
}