<?php
namespace carono\company;

class EgrulException extends \Exception
{
    public static function raiseDownloadError()
    {
        throw new self('Download error');
    }

    public static function raiseNotFound()
    {
        throw new self('Not found');
    }
}