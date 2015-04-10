<?php

namespace Energine\shop\components;
use Energine\share\components\Grid, Energine\share\gears\FieldDescription, Energine\share\gears\Field;
use Energine\share\gears\QAL;

class PromotionEditor extends Grid {

	/**
	 * @var PromotionGoodsEditor $oEditor
	 */
	private $oEditor;

	public function __construct($name,  array $params = null) {
		parent::__construct($name, $params);
	}

	protected function prepare() {

		parent::prepare();

		if (in_array($this->getState(), array('add', 'edit'))) {

			$fd = new FieldDescription('goods');
			$fd->setType(FieldDescription::FIELD_TYPE_TAB);
			$fd->setProperty('title', $this->translate('TAB_PROMOTION_GOODS'));
			$this->getDataDescription()->addFieldDescription($fd);

			$field = new Field('goods');
			$state = $this->getState();
			$tab_url = (($state != 'add') ? $this->getData()->getFieldByName($this->getPK())->getRowData(0) : '') . '/goods/';

			$field->setData($tab_url, true);
			$this->getData()->addField($field);
		}
	}

	protected function add() {
		parent::add();
		$this->getData()->getFieldByName('promotion_is_active')->setData(1, true);
	}

	protected function goodsEditor() {
		$sp = $this->getStateParams(true);
		$params = array('config' => 'core/modules/shop/config/PromotionGoodsEditor.component.xml');

		if (isset($sp['promotion_id'])) {
			$this->request->shiftPath(2);
			$params['promotionID'] = $sp['promotion_id'];

		} else {
			$this->request->shiftPath(1);
		}
		$this->oEditor = $this->document->componentManager->createComponent('oEditor', 'Energine\shop\components\PromotionGoodsEditor', $params);
		$this->oEditor->run();
	}

	public function build() {
		if ($this->getState() == 'goodsEditor') {
			$result = $this->oEditor->build();
		} else {
			$result = parent::build();
		}

		return $result;
	}

	protected function saveData() {
		$promotionID = parent::saveData();
		$this->dbh->modify(
			'UPDATE shop_goods2promotions
			SET session_id = NULL, promotion_id=%s
			WHERE (promotion_id IS NULL and session_id = %s) or (promotion_id = %1$s)',
			$promotionID, session_id()
		);
		return $promotionID;
	}
}