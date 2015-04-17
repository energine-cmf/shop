<?php

namespace Energine\shop\gears;

use Energine\share\gears\DataDescription;
use Energine\share\gears\DBWorker;
use Energine\share\gears\FieldDescription;
use Energine\share\gears\Object;
use Energine\share\gears\SystemException;
use Energine\share\gears\Field;

class FeatureFieldAbstract extends Object {
    use DBWorker;

    const FEATURE_TYPE_STRING = 'STRING';
    const FEATURE_TYPE_INT = 'INT';
    const FEATURE_TYPE_BOOL = 'BOOL';
    const FEATURE_TYPE_OPTION = 'OPTION';
    const FEATURE_TYPE_MULTIOPTION = 'MULTIOPTION';
    const FEATURE_TYPE_VARIANT = 'VARIANT';
    const FEATURE_TYPE_UNDEFINED = 'UNKNOWN';

	const FEATURE_FILTER_TYPE_DEFAULT = 'DEFAULT';
	const FEATURE_FILTER_TYPE_RADIOGROUP = 'RADIOGROUP';
	const FEATURE_FILTER_TYPE_CHECKBOXGROUP = 'CHECKBOXGROUP';
	const FEATURE_FILTER_TYPE_SELECT = 'SELECT';
	const FEATURE_FILTER_TYPE_RANGE = 'RANGE';
	const FEATURE_FILTER_TYPE_CHECKBOX = 'CHECKBOX';
	const FEATURE_FILTER_TYPE_NONE = 'NONE';

    protected $feature_id;
    protected $value;
    protected $data;
    protected $options = array();

    public function __construct() {
        parent::__construct();
    }

    public function getType() {
        return isset($this->data['feature_type']) ? $this->data['feature_type'] : self::FEATURE_TYPE_UNDEFINED;
    }

    public function getFeatureId() {
        return $this->feature_id;
    }

    public function setFeatureId($feature_id) {
        $this->feature_id = $feature_id;
        return $this;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function getValue() {
        return $this->value;
    }

    public function getName() {
        return (isset($this->data['feature_name'])) ? $this->data['feature_name'] : '';
    }

    public function getTitle() {
        return (isset($this->data['feature_title'])) ? $this->data['feature_title'] : '';
    }

    public function getDescription() {
        return (isset($this->data['feature_description'])) ? $this->data['feature_description'] : '';
    }

    public function getSysName() {
        return (isset($this->data['feature_sysname'])) ? $this->data['feature_sysname'] : '';
    }

	public function getGroupId() {
		return (isset($this->data['group_id'])) ? $this->data['group_id'] : '';
	}

	public function getGroupName() {
		return (isset($this->data['group_name'])) ? $this->data['group_name'] : '';
	}

	public function getUnit() {
        return (isset($this->data['feature_unit'])) ? $this->data['feature_unit'] : '';
    }

	public function getFilterType() {
		$type = (isset($this->data['feature_filter_type'])) ? $this->data['feature_filter_type'] : '';
		switch ($type) {
			case self::FEATURE_FILTER_TYPE_CHECKBOXGROUP:
			case self::FEATURE_FILTER_TYPE_RADIOGROUP:
			case self::FEATURE_FILTER_TYPE_SELECT:
			case self::FEATURE_FILTER_TYPE_RANGE:
				return $type;
			break;
			default:
				switch ($this -> getType()) {
					case self::FEATURE_TYPE_BOOL:
						return self::FEATURE_FILTER_TYPE_CHECKBOX;
					break;
					case self::FEATURE_TYPE_INT:
						return self::FEATURE_FILTER_TYPE_RANGE;
					break;
					case self::FEATURE_TYPE_OPTION:
						return self::FEATURE_FILTER_TYPE_RADIOGROUP;
					break;
					case self::FEATURE_TYPE_MULTIOPTION:
					case self::FEATURE_TYPE_VARIANT:
						return self::FEATURE_FILTER_TYPE_CHECKBOXGROUP;
					break;
					default:
						return self::FEATURE_FILTER_TYPE_NONE;
				}
		}
	}

    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    public function getData() {
        return $this->data;
    }

	public function isActive() {
		return (isset($this->data['feature_is_active'])) ? (bool)$this->data['feature_is_active'] : false;
	}

	public function isFilter() {
		return (isset($this->data['feature_is_filter'])) ? (bool)$this->data['feature_is_filter'] : false;
	}

	public function loadFeatureData() {
        if ($this->feature_id) {
            $res = $this->data = $this->dbh->select(
                'select f.feature_id,
				f.group_id,
				gt.group_name,
				f.feature_type,
				f.feature_filter_type,
				f.feature_is_active,
				f.feature_is_filter,
				ft.feature_name,
				ft.feature_description,
				ft.feature_unit,
				f.feature_sysname,
				ft.feature_title
				from shop_features f
				left join shop_feature_groups g on f.group_id = g.group_id
				left join shop_feature_groups_translation gt on gt.group_id = g.group_id and gt.lang_id = %s
				left join shop_features_translation ft
				on ft.feature_id = f.feature_id and ft.lang_id = %s
				where f.feature_id = %s LIMIT 1',
                E()->getDocument()->getLang(),
				E()->getDocument()->getLang(),
                $this->feature_id
            );
            if ($res) {
                $this->setData($res[0]);
            }
        }
        return $this;
    }

    public function loadFeatureOptions() {
        $options = array();

        $res = $this->dbh->select(
            'select o.option_id, ot.option_value, o.option_img, u.upl_mime_type
		from shop_feature_options o
		left join shop_feature_options_translation ot
		on o.option_id = ot.option_id and ot.lang_id = %s
		left join share_uploads u on o.option_img = u.upl_path
		where o.feature_id = %s
		order by o.option_order_num asc',
            E()->getDocument()->getLang(),
            $this->getFeatureId()
        );
        if ($res) {
            foreach ($res as $row) {
                $options[$row['option_id']] = array(
					'id' => $row['option_id'],
                    'value' => $row['option_value'],
                    'path' => $row['option_img'],
                    'mime_type' => $row['upl_mime_type']
                );
            }
        }

        $this->setOptions($options);
        return $this;
    }

    public function setOptions($options) {
        $this->options = $options;
        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    public function modifyFormFieldDescription(DataDescription &$dd, FieldDescription &$fd) {
        throw new SystemException('Not implemented');
    }

    public function modifyFormField(Field &$field) {
        // not implemented for most fields
    }

	public function getFilterFieldName() {
		$name = $this -> getSysName();
		$name = (!empty($name)) ? $name : 'feature_' . $this -> getFeatureId();
		return $name;
	}

    public function __toString() {
        return '';
    }

	public function getFilterFieldDescription($filter_data = false) {
		$fd = new FieldDescription($this -> getFilterFieldName());
		$fd -> setProperty('title', $this -> data['feature_title']);
		$fd -> setProperty('group_id', $this -> data['group_id']);
		$fd -> setProperty('group_title', $this -> data['group_name']);
		$fd -> setProperty('feature_id', $this -> getFeatureId());
		switch ($this -> getFilterType()) {
			// выпадающий список
			case self::FEATURE_FILTER_TYPE_SELECT:
				$fd -> setType(FieldDescription::FIELD_TYPE_SELECT);
				$fd -> setProperty('subtype', self::FEATURE_FILTER_TYPE_SELECT);
				$fd->setAvailableValues(array());
				$values = array();
				if ($this -> options) {
					foreach ($this -> options as $option_id => $option_data) {
						$values[] = array(
							'option_id' => $option_id,
							'option_value' => $option_data['value']
						);
					}
				}
				$fd->loadAvailableValues($values, 'option_id', 'option_value');
			break;
			// набор чекбоксов
			case self::FEATURE_FILTER_TYPE_CHECKBOXGROUP:
				$fd -> setType(FieldDescription::FIELD_TYPE_MULTI);
				$fd -> setProperty('subtype', self::FEATURE_FILTER_TYPE_CHECKBOXGROUP);
				$fd->setAvailableValues(array());
				$values = array();
				if ($this -> options) {
					foreach ($this -> options as $option_id => $option_data) {
						$values[] = array(
							'option_id' => $option_id,
							'option_value' => $option_data['value']
						);
					}
				}
				$fd->loadAvailableValues($values, 'option_id', 'option_value');
			break;
			// набор radio
			case self::FEATURE_FILTER_TYPE_RADIOGROUP:
				$fd -> setType(FieldDescription::FIELD_TYPE_MULTI);
				$fd -> setProperty('subtype', self::FEATURE_FILTER_TYPE_RADIOGROUP);
				$fd->setAvailableValues(array());
				$values = array();
				if ($this -> options) {
					foreach ($this -> options as $option_id => $option_data) {
						$values[] = array(
							'option_id' => $option_id,
							'option_value' => $option_data['value']
						);
					}
				}
				$fd->loadAvailableValues($values, 'option_id', 'option_value');
			break;
			// диапазон
			case self::FEATURE_FILTER_TYPE_RANGE:
				$fd -> setType(FieldDescription::FIELD_TYPE_CUSTOM);
				$fd -> setProperty('subtype', self::FEATURE_FILTER_TYPE_RANGE);

				if ($this -> options) {

					$min = (float) current($this -> options)['value'];
					$max = (float) current($this -> options)['value'];
					$step = 0.1;

					foreach ($this -> options as $option_id => $option_data) {
						if ((float) $option_data['value'] <= $min) {
							$min = (float) $option_data['value'];
						}
						if ((float) $option_data['value'] >= $max) {
							$max = (float) $option_data['value'];
						}
					}

					$begin = (isset($filter_data['begin'])) ? (float) $filter_data['begin'] : $min;
					$end = (isset($filter_data['end'])) ? (float) $filter_data['end'] : $max;

					$fd -> setProperty('range-min', $min);
					$fd -> setProperty('range-max', $max);
					$fd -> setProperty('range-step', $step);
					$fd -> setProperty('range-begin', $begin);
					$fd -> setProperty('range-end', $end);
				}
			break;
			// todo: остальные типы (checkbox, int, string?) - а нужны ли они в фильтрах ?
		}
		return $fd;
	}

	public function getFilterField($filter_data = false) {
		$name = $this -> getFilterFieldName();
		$f = new Field($name);
		switch ($this -> getFilterType()) {
			case self::FEATURE_FILTER_TYPE_SELECT:
				if (isset($filter_data['value'])) {
					$f->setRowData(0, $filter_data['value']);
				}
			break;
			case self::FEATURE_FILTER_TYPE_CHECKBOXGROUP:
			case self::FEATURE_FILTER_TYPE_RADIOGROUP:
				if (isset($filter_data['values'])) {
					$f->setRowData(0, $filter_data['values']);
				}
			break;
		}
		return $f;
	}
}