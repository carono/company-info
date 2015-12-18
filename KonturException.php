<?php

namespace carono\company;


class KonturException extends \Exception
{
	public static function moreAtOne()
	{
		throw new self("More at one results");
	}

	public static function notFound()
	{
		throw new self("Company not found");
	}

	public static function contentNofFound()
	{
		throw new self("Content not found");
	}

	public static function directorNotFound()
	{
		throw new self("Director not found");
	}

	public static function wrongInn()
	{
		throw new self("Inn is wrong");
	}
}