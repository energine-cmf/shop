<?php

namespace Energine\shop\components;


use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\QAL;
use Energine\share\gears\SiteManager;
use Energine\share\gears\Translit;

class ProducerEditor extends Grid {
    private $multishop = false;

    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('shop_producers');
        $cols = array_keys($this->dbh->getColumnsInfo($this->getTableName()));
        //inspect($cols);
        if (in_array('producer_site_multi', $cols)) {
            $this->multishop = true;
        }
    }

    protected function createDataDescription() {
        $r = parent::createDataDescription();
        if (($this->document->getRights() < ACCESS_FULL) && ($fd = $r->getFieldDescriptionByName('producer_site_multi'))) {
            $fd->setType(FieldDescription::FIELD_TYPE_HIDDEN);
        }
        return $r;
    }

    /**
     * Отбираем только те сайты которые являются магазинами
     *
     * @param string $fkTableName
     * @param string $fkKeyName
     * @return array
     */
    protected function getFKData($fkTableName, $fkKeyName) {
        $filter = $order = [];
        if ($this->multishop && ($fkKeyName == 'site_id')) {
            //оставляем только те сайты где есть магазины
            if ($sites = E()->getSiteManager()->getSitesByTag('shop')) {
                $filter = array_map(function ($site) {
                    return $site->id;
                }, $sites);
                $filter['share_sites.site_id'] = $filter;
                $order['share_sites_translation.site_name'] = QAL::ASC;
            }
        }

        if ($this->getState() !== self::DEFAULT_STATE_NAME) {
            $result = $this->dbh->getForeignKeyData($fkTableName, $fkKeyName, $this->document->getLang(), $filter, $order);
        }

        return $result;
    }

    protected function getRawData() {
        if ($this->multishop && $this->document->getRights() != ACCESS_FULL) {
            //отбираем тех производителей права на которые есть у текущего пользователя
            //то есть те, у которых есть в перечен привязанных сайтов, сайты,

            //все магазины
            $sites = E()->getSiteManager()->getSitesByTag('shop');
            //ищем в них те разделы
        }
        parent::getRawData();
    }

    protected function saveData() {
        //Для всех с не админскими правами принудительно выставляем в те сайты на которые у юзера есть права
        if ($this->multishop && $this->document->getRights() < ACCESS_FULL) {
            foreach ($sites = E()->getSiteManager()->getSitesByTag('shop') as $site) {

            }

        }

        if (empty($_POST[$this->getTableName()]['producer_segment'])) {
            $_POST[$this->getTableName()]['producer_segment'] = Translit::asURLSegment($_POST[$this->getTranslationTableName()][E()->getLanguage()->getDefault()]['producer_name']);
        }

        $r = parent::saveData();

        return $r;
    }
}