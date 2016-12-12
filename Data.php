<?php

namespace carono\company;


class Data
{
    const STATUS_ACTIVE = 'Действующая организация';
    const STATUS_TERMINATED = 'Деятельность прекращена';

    public $inn;
    public $ogrn;
    public $kpp;
    public $okpo;
    public $full_name;
    public $short_name;
    public $activity = '';
    public $activities = [];
    public $address;
    public $city;
    public $status = self::STATUS_ACTIVE;
    public $director = '';
    public $rawData = [];
    public $isOrganization = false;
    public $found;
    public $register_date;

    private static function get($array, $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    private static function lower($str)
    {
        return mb_strtolower($str, 'utf-8');
    }

    private static function ucfirst($str, $lower = true)
    {
        $enc = 'utf-8';
        $str = $lower ? self::lower($str) : $str;
        return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc) . mb_substr($str, 1, mb_strlen($str, $enc), $enc);
    }

    public function __construct($rawData = [])
    {
        $nalog_info = self::get($rawData, "nalog_info", []);
        $register_info = self::get($rawData, "register_info", []);
        $address = self::get($rawData, "address", []);
        $personal = self::get($rawData, "personal", []);
        $names = self::get($rawData, "names", []);
        $kapital = self::get($rawData, "kapital", []);
        $trusted_personal = self::get($rawData, "trusted_personal", []);
        $okved = self::get($rawData, "okved", []);
        $termination = self::get($rawData, "termination", []);
        $status = self::get($rawData, "status", []);

        $this->rawData = $rawData;
        $this->inn = self::get($nalog_info, 'inn');
        $this->ogrn = self::get($register_info, 'ogrn');
        if ($address) {
            if (!$this->city = self::get($address, 'town')) {
                $this->city = self::get($address, 'subdivision');
            }
            $this->city = self::ucfirst(trim(str_replace('ГОРОД', "", $this->city)), true);
            unset($address["grn_and_date"]);
            $this->address = join(', ', $address);
        }
        if ($personal) {
            $secondname = self::ucfirst(self::get($personal, 'secondname'), true);
            $name = self::ucfirst(self::get($personal, 'name'), true);
            $patronymic = self::ucfirst(self::get($personal, 'patronymic'), true);
            $nameL = mb_substr($name, 0, 1, "UTF-8");
            $patronymicL = mb_substr($patronymic, 0, 1, "UTF-8");
            $this->full_name = join(' ', array_filter([$secondname, $name, $patronymic]));
            $this->short_name = join(' ', array_filter([$secondname, $nameL, $patronymicL]));
            $this->director = $this->full_name;
        } elseif ($names) {
            $this->full_name = self::get($names, 'full_name');
            $this->short_name = self::get($names, 'short_name');
        }
        $this->found = self::get($kapital, 'size_rub');
        if ($trusted_personal) {
            $secondname = self::ucfirst(self::get($trusted_personal, 'secondname'), true);
            $name = self::ucfirst(self::get($trusted_personal, 'name'), true);
            $patronymic = self::ucfirst(self::get($trusted_personal, 'patronymic'), true);
            $this->director = join(' ', [$secondname, $name, $patronymic]);
        }
        $this->isOrganization = $trusted_personal || $names;
        $this->register_date = strtotime(self::get($register_info, 'register_date'));
        $this->activity = self::lower(self::get($okved, 'code_activity'));
        if ($termination) {
            $this->status = self::STATUS_TERMINATED;
        } elseif ($status) {
            $this->status = self::ucfirst(self::get($status, 'status'), true);
        }
    }
}