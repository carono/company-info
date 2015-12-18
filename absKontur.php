<?php

namespace carono\company;

use carono\murl\MUrl as CParser;

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
	private static $_parser = null;

	/**
	 * @param $inn
	 * @param $force
	 *
	 * @return mixed|string
	 * @throws KonturException
	 */
	public static function getContent($inn, $force)
	{
		if (!trim($inn)) {
			KonturException::wrongInn();
		}
//		if (!($content = \Yii::app()->cache->get("parse_$inn")) || $force) {
		$query = "/search?query=$inn";
		$content = self::parser()->getContent(self::$host . $query);
//			\Yii::app()->cache->set("parse_$inn", $content, 3000);
//		}
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
		$slug = "parse_ogrn_$ogrn";
//		if (!($content = \Yii::app()->cache->get($slug)) || $force) {
			$query = "/activities?query=$ogrn";
			$content = self::parser()->getContent(self::$host . $query);
//			\Yii::app()->cache->set($slug, $content, 3000);
//		}
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
//		\Yii::app()->cache->set(self::CACHE_COOKIE, $cookie);
	}

	/**
	 * @return CParser
	 */
	public static function parser()
	{
		if (!self::$_parser) {
			self::$_parser = new CParser();
			if ($proxy = self::$proxy) {
				$arr = explode(":", $proxy);
				self::$_parser->proxyHost = $arr[0];
				self::$_parser->proxyPort = $arr[1];
			}
			self::$_parser->followRedirect = 1;
			self::clearCookie();
			if ($cookie = self::getTokenCookie()) {
				self::$_parser->headers->Cookie = $cookie;
			}
			return self::$_parser;
		} else {
			return self::$_parser;
		}
	}


	private static function clearCookie()
	{
		self::parser()->headers->Cookie = 'new-int-rel=1; expires=Sat, 28-Mar-2115 11:00:48 GMT; path=/';
	}

	public static function getTokenCookie()
	{
		return '';
		if ($cookie = \Yii::app()->cache->get(self::CACHE_COOKIE)) {
			return $cookie;
		} else {
			self::clearCookie();
			self::parser()->getContent(self::$loginUrl);
			$cookie = self::parser()->cookie;
			self::setTokenCookie($cookie);
			return $cookie;
		}
	}

	public static function isLogin()
	{
		throw new KonturException('Not implemented');
		if (!$cookie = self::getTokenCookie()) {
			return false;
		}
		self::parser()->headers->Cookie = $cookie;
		$content = self::parser()->getContent(self::$host);
		if (!($result = (bool)strpos($content, 'authLine-requisite'))) {
			\Yii::app()->cache->set(self::CACHE_COOKIE, "");
		}
		return $result;
	}

	public static function login($url)
	{
		throw new KonturException('Not implemented');
		self::clearCookie();
		self::parser()->getContent($url);
		self::setTokenCookie(self::parser()->cookie);
		return self::isLogin();
	}

	public static function logout()
	{
		throw new KonturException('Not implemented');
		self::setTokenCookie('');
	}
}