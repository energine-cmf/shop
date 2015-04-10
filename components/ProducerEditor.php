<?php

namespace Energine\shop\components;


use Energine\share\components\Grid;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\QAL;
use Energine\share\gears\SiteManager;
use Energine\share\gears\Translit;

class ProducerEditor extends Grid {
    public function __construct($name, array $params = NULL) {
        parent::__construct($name, $params);
        $this->setTableName('shop_producers');
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
        if ($fkKeyName == 'site_id') {
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
        if ($this->document->getRights() < ACCESS_FULL) {
            //отбираем тех производителей права на которые есть у текущего пользователя
            //то есть те, у которых есть в перечен привязанных сайтов
            $this->addFilterCondition([$this->getTableName().'.producer_id' => $this->dbh->getColumn('shop_producers2sites', 'producer_id', ['site_id' => $this->document->getUser()->getSites()])]);
        }
        parent::getRawData();
    }

    protected function saveData() {
        if (empty($_POST[$this->getTableName()]['producer_segment'])) {
            $_POST[$this->getTableName()]['producer_segment'] = Translit::asURLSegment($_POST[$this->getTranslationTableName()][E()->getLanguage()->getDefault()]['producer_name']);
        }

        //Для всех с не админскими правами принудительно выставляем в те сайты на которые у юзера есть права
        if (($this->document->getRights() < ACCESS_FULL)) {
            $_POST[$this->getTableName()]['producer_site_multi'] = $this->document->getUser()->getSites();
        }

        $r = parent::saveData();

        return $r;
    }
}