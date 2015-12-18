<?php
/**
 * User: Карно
 * Date: 18.03.2015
 * Time: 18:26
 */

namespace ext\companyInfo;


class CompanyData
{
    const STATUS_ACTIVE = 'Действующая организация';
    const STATUS_TERMINATED = 'Деятельность прекращена';

    public $inn;
    public $ogrn;
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

    public function __construct($rawData = [])
    {
        $nalog_info = \CArray::get($rawData, "nalog_info", []);
        $register_info = \CArray::get($rawData, "register_info", []);
        $address = \CArray::get($rawData, "address", []);
        $personal = \CArray::get($rawData, "personal", []);
        $names = \CArray::get($rawData, "names", []);
        $kapital = \CArray::get($rawData, "kapital", []);
        $trusted_personal = \CArray::get($rawData, "trusted_personal", []);
        $okved = \CArray::get($rawData, "okved", []);
        $termination = \CArray::get($rawData, "termination", []);
        $status = \CArray::get($rawData, "status", []);

        $this->rawData = $rawData;
        $this->inn = \CArray::get($nalog_info, 'inn');
        $this->ogrn = \CArray::get($register_info, 'ogrn');
        if ($address) {
            if (!$this->city = \CArray::get($address, 'town')) {
                $this->city = \CArray::get($address, 'subdivision');
            }
            $this->city = \StringHelper::ucfirst(trim(str_replace('ГОРОД', "", $this->city)), true);
            unset($address["grn_and_date"]);
            $this->address = join(', ', $address);
        }
        if ($personal) {
            $secondname = \StringHelper::ucfirst(\CArray::get($personal, 'secondname'), true);
            $name = \StringHelper::ucfirst(\CArray::get($personal, 'name'), true);
            $patronymic = \StringHelper::ucfirst(\CArray::get($personal, 'patronymic'), true);
            $nameL = mb_substr($name, 0, 1, "UTF-8");
            $patronymicL = mb_substr($patronymic, 0, 1, "UTF-8");
            $this->full_name = join(' ', array_filter([$secondname, $name, $patronymic]));
            $this->short_name = join(' ', array_filter([$secondname, $nameL, $patronymicL]));
            $this->director = $this->full_name;
        } elseif ($names) {
            $this->full_name = \CArray::get($names, 'full_name');
            $this->short_name = \CArray::get($names, 'short_name');
        }
        $this->found = \CArray::get($kapital, 'size_rub');
        if ($trusted_personal) {
            $secondname = \StringHelper::ucfirst(\CArray::get($trusted_personal, 'secondname'), true);
            $name = \StringHelper::ucfirst(\CArray::get($trusted_personal, 'name'), true);
            $patronymic = \StringHelper::ucfirst(\CArray::get($trusted_personal, 'patronymic'), true);
            $this->director = join(' ', [$secondname, $name, $patronymic]);
        }
        $this->isOrganization = $trusted_personal || $names;
        $this->register_date = strtotime(\CArray::get($register_info, 'register_date'));
        $this->activity = \StringHelper::lower(\CArray::get($okved, 'code_activity'));
        if ($termination) {
            $this->status = self::STATUS_TERMINATED;
        } elseif ($status) {
            $this->status = \StringHelper::ucfirst(\CArray::get($status, 'status'), true);
        }
    }
}