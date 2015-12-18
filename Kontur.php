<?php

namespace carono\company;


use Stringy\Stringy as S;

class Kontur extends absKontur
{
	/**
	 * @param array $data
	 *
	 * @return array
	 */
	private static function filteringData(array $data)
	{
		$newKeys = [];
		foreach (array_keys($data) as $key) {
			$key = trim($key);
			if (in_array(S::create($key)->toLowerCase(), array_keys(self::$names))) {
				$newKeys[] = self::$names[S::create($key)->toLowerCase()->__toString()];
			} else {
				$newKeys[] = $key;
			}
		}
		foreach ($data as $key => &$value) {
			if (is_array($value)) {
			} else {
				$value = trim($value);
			}
		}
		return array_combine($newKeys, $data);
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return array
	 */
	private static function extractMainData($phpQuery)
	{
		$phpQuery = $phpQuery->find('dl')->eq(0);
		$keys = [];
		$values = [];
		foreach ($phpQuery->find('dt') as $dt) {
			$keys[] = pq($dt)->html();
		}
		foreach ($phpQuery->find('dd') as $dd) {
			$values[] = pq($dd)->html();
		}
		return self::filteringData(array_combine($keys, $values));
	}

	public static function parse($inn, $force = 0)
	{
		$data = [];
		$content = self::getContent($inn, $force);
		$result = \phpQuery::newDocument($content);
		$table = $result->find('.unevenIndent');
		$data["full_name"] = self::extractFullName($result);
		$data["status"] = self::extractStatus($table);
		$data = array_merge($data, self::extractMainData($table));
		$data["date_create"] = self::extractDateCreate($table);
		$data["address"] = self::extractAddress($table);
		$data["founders"] = self::extractFounders($table);
		$data["found"] = self::extractFound($table);
		$data["director"] = self::extractDirector($table);
		$data["activities"] = self::extractActivities($data["ogrn"], $force);
		$data["city"] = self::extractCity($data["address"]);
		return self::filteringData($data);
	}

	/**
	 * @param $address
	 *
	 * @return string
	 */
	public static function extractCity($address)
	{
		//http://xmlcon.ru/index.php?catid=3:instrcat&id=32:2012-06-23-15-57-08&Itemid=31&option=com_content&view=article
		$arr = explode(', ', $address);
		$result = '';
		foreach ($arr as $element) {
			if (stripos($element, "г ") === 0) {
				$result = $element;
			} elseif (stripos($element, "пгт ") === 0) {
				$result = $element;
			} elseif (stripos($element, "п ") === 0) {
				$result = $element;
			} elseif (stripos($element, "рп ") === 0) {
				$result = $element;
			} elseif (stripos($element, "с ") === 0) {
				$result = $element;
			}
			if ($result) {
				$arr1 = explode(" ", $result);
				array_shift($arr1);
				$result = join(" ", $arr1);
				break;
			}
		}
		return $result;
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return string
	 */
	private static function extractStatus($phpQuery)
	{
		return $phpQuery->find('div:first')->html();
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return string
	 */
	private static function extractFullName($phpQuery)
	{
		$h1 = $phpQuery->find('h1');
		if ($html = $h1->find('span')->eq(0)->html()) {
			return $html;
		} else {
			return $h1->html();
		}
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return string
	 */
	private static function extractAddress($phpQuery)
	{
		$phpQuery = $phpQuery->find('div.fullMargin')->eq(2);
		$addressData = [];
		foreach ($phpQuery->children() as $node) {
			$node = pq($node);
			if ($node->eq(0)->is('p')) {
				if ($node->find('a')->html()) {
					$addressData[] = trim(strip_tags($node->find('a span')->eq(0)->html()));
				} else {
					$addressData[] = trim(strip_tags($node->eq(0)->html()));
				}
			} elseif ($node->eq(0)->is('a')) {
				$addressData[] = trim(strip_tags($node->find('span')->eq(0)->html()));
			}
		}
		array_pop($addressData);
		return join(', ', array_filter($addressData));
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return string
	 */
	private static function extractFounders($phpQuery)
	{
		$phpQuery = $phpQuery->find('.foundersMargin');
		$result = [];

		foreach ($phpQuery as $founder) {
			$founder = pq($founder);
			$founderItem = [];
			if ($percent = $founder->find('.foundersPercentFieldGt480')->html()) {
				preg_match('/\d+/', $percent, $match);
				if (isset($match[0])) {
					$percent = $match[0];
				}
			} else {
				$percent = '';
			}

			preg_match('/\d+/', $founder->find('.ind2em')->eq(2)->html(), $match);
			$inn = isset($match[0]) ? $match[0] : "";

			if (!($name = trim(strip_tags($founder->find('a')->eq(0)->find('span')->html()), " \t\n\r\0\x0B"))) {
				$name = trim(strip_tags($founder->find('a')->eq(0)->html()));
			}
			$founderItem["founder"] = $name;
			$founderItem["percent"] = $percent;
			if (!($money = $founder->find('.ind2em')->eq(1)->html())) {
				$money = $founder->find('.ind2em')->eq(0)->html();
			}
			$founderItem["money"] = $money;
			$founderItem["inn"] = $inn;
			$result[] = $founderItem;
		}
		return $result;
	}

	public static function extractActivities($ogrn, $force)
	{
		$content = self::getActivitiesContent($ogrn, $force);
		$result = \phpQuery::newDocument($content);
		$keys = [];
		$values = [];
		foreach ($result->find('.textRight') as $num) {
			$keys[] = strip_tags(pq($num)->html());
		}
		foreach ($result->find('.activity-text') as $text) {
			$values[] = strip_tags(pq($text)->html());
		}
		return array_combine($keys, $values);
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return string
	 */
	private static function extractDirector($phpQuery)
	{
		foreach ($phpQuery->find('.fullMargin') as $div) {
			$div = pq($div);
			$flag = strpos(trim($div->find('p')->html()), "Директор") !== false;
			if ($div->find('span.ind1em')->html() || $div->find('a.connectionsLink')->html() || $flag) {
				if ($span = $div->find('a span')->eq(0)) {
					return strip_tags($span->html());
				}
			}
		}
		return "";
//        KonturException::directorNotFound();
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return string
	 */
	private static function extractFound($phpQuery)
	{
		foreach ($phpQuery->find('div.noMargin') as $node) {
			$node = pq($node);
			if (mb_stripos($node->html(), "Уставный капитал", null, 'utf-8')) {
				$arr = explode(":", current(array_filter(explode("\r\n", $node->html()))));
				return trim($arr[1]);
			}
		}
		return '';
	}

	/**
	 * @param \phpQueryObject $phpQuery
	 *
	 * @return string
	 */
	private static function extractDateCreate($phpQuery)
	{
		setlocale(LC_ALL, 'ru_RU.UTF-8');
		$phpQuery = $phpQuery->find('div.fullMargin')->eq(1);
		$create_date = strip_tags($phpQuery->html());
		$arr = explode(':', $create_date);
		foreach ($arr as $path) {
			if ($timestamp = strftime($path)) {
				return date('Y-m-d H:i:s', $timestamp);
			}
		}
		return "";
	}
}