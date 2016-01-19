<?php
/**
 * Item class file.
 *
 * @property integer $itemId
 * @property integer $parentId
 * @property string  $name
 * @property integer $position
 */
class Item extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'Item';
	}
	
	protected static $aChildCount = NULL;

	public function rules()
	{
		return [
			['name', 'length', 'min' => 1, 'max' => 50, 'allowEmpty' => FALSE, 'message' => 'Name should be between {min} and {max} characters.'],
		];
	}

	public function relations() {
		return [
			'children' => [self::HAS_MANY, 'Item', 'parentId'],
		];
	}

	public static function getChildren($nParentId = 0, $bArray = FALSE)
	{
		$oCriteria = new CDbCriteria();
		$oCriteria->addCondition('parentId = ' . $nParentId);
		$oCriteria->order = 'position ASC';

		$aItems = self::model()->findAll($oCriteria);

		if (!$bArray)
		{
			return $aItems;
		}

		$aData = [];

		foreach ($aItems as $oItem)
		{
			$aData[$oItem->itemId] = $oItem->name;
		}

		return $aData;
	}

	public function getAll($nParentId = 0)
	{
		$aData = [];

		$oItem = self::model()->findByPk($nParentId);

		if ($nParentId && !$oItem)
		{
			return [];
		}

		$aItems = self::getChildren($nParentId);

		foreach ($aItems as $oItem)
		{
			$aData[] = [
				'id'       => $oItem->itemId,
				'text'     => $oItem->name,
				'children' => self::getAll($oItem->itemId),
			];
		}

		return $aData;
	}

	public function getGenealogy()
	{
		$aGenealogy = [$this->name];
		$oItem      = $this;

		while ($oItem->parentId > 0)
		{
			$oItem        = self::model()->findByPk($oItem->parentId);
			$aGenealogy[] = $oItem->name;
		}

		return array_reverse($aGenealogy);
	}

	public static function treeChildren($nItemId)
	{
		$aData = [];
		$oItem = self::model()->findByPk($nItemId);

		if ($nItemId && !$oItem)
		{
			return [];
		}

		$aItems = self::getChildren($nItemId);

		foreach ($aItems as $oItem)
		{
			$aData[] = [
				'id'       => $oItem->itemId,
				'text'     => $oItem->name,
				'children' => (count($oItem->children) > 0) ? TRUE : FALSE,
			];
		}

		return $aData;
	}

	public static function treeCreate($nParentId, $nPositionId, $sName)
	{
		$oItem = new self;

		$oItem->name     = $sName;
		$oItem->parentId = $nParentId;
		$oItem->position = $nPositionId;

		if (!$oItem->save())
		{
			Yii::log('Cannot save new Item with attributes:' . serialize($oItem->attributes), CLogger::LEVEL_ERROR);
			return FALSE;
		}

		return $oItem->itemId;
	}

	public static function treeRename($nItemId, $sName)
	{
		$oItem = self::model()->findByPk($nItemId);

		if (!$nItemId || !strlen($sName) || !$oItem)
		{
			return FALSE;
		}

		$oItem->name = $sName;

		if(!$oItem->save())
		{
			Yii::log('Cannot save Item ID:' . $nItemId . ' with attributes:' . serialize($oItem->attributes), CLogger::LEVEL_ERROR);
			return FALSE;
		}

		return TRUE;
	}

	public static function treeMove($nItemId, $nParentId, $nOldParentId, $nPosition, $nOldPosition)
	{
		if (!$nItemId || $nParentId === NULL || $nOldParentId === NULL || $nPosition === NULL || $nOldPosition === NULL)
		{
			Yii::log('Invalid attribute(s).', CLogger::LEVEL_WARNING);
			return FALSE;
		}

		$oItem = self::model()->findByPk($nItemId);

		if (!$oItem)
		{
			Yii::log('Cannot find Item ID:' . $nItemId, CLogger::LEVEL_ERROR);
			return FALSE;
		}

		if ($nOldParentId != $nParentId)
		{
			$oParentItem = Item::model()->findByPk($nParentId);

			if ($nParentId > 0 && !$oParentItem)
			{
				Yii::log('Cannot find Item ID:' . $nParentId, CLogger::LEVEL_ERROR);
				return FALSE;
			}

			$aOldSiblings = Item::getChildren($nOldParentId);

			if ($nOldPosition <= count($aOldSiblings) - 1)
			{
				foreach ($aOldSiblings as $oSibling)
				{
					if ($oSibling->position > $nOldPosition)
					{
						$oSibling->position -= 1;
					}

					if(!$oSibling->save())
					{
						Yii::log('Cannot save Item ID:' . $oSibling->itemId . ' with attributes:' . serialize($oSibling->attributes), CLogger::LEVEL_ERROR);
						return FALSE;
					}
				}
			}

			$aNewSiblings = Item::getChildren($nParentId);

			if ($nPosition <= count($aNewSiblings) - 1)
			{
				foreach ($aNewSiblings as $oSibling)
				{
					if ($oSibling->position >= $nPosition)
					{
						$oSibling->position += 1;
					}

					if(!$oSibling->save())
					{
						Yii::log('Cannot save Item ID:' . $oSibling->itemId . ' with attributes:' . serialize($oSibling->attributes), CLogger::LEVEL_ERROR);
						return FALSE;
					}
				}
			}
		}
		else
		{
			$aSiblings = Item::getChildren($nParentId);

			foreach ($aSiblings as $oSibling)
			{
				if (($oSibling->position > $nOldPosition && $oSibling->position > $nPosition) || ($oSibling->position < $nOldPosition && $oSibling->position < $nPosition))
				{
					continue;
				}

				if ($nPosition > $nOldPosition && $oSibling->position > $nOldPosition && $oSibling->position <= $nPosition)
				{
					$oSibling->position -= 1;
				}

				if ($nPosition < $nOldPosition && $oSibling->position < $nOldPosition && $oSibling->position >= $nPosition)
				{
					$oSibling->position += 1;
				}

				if(!$oSibling->save())
				{
					Yii::log('Cannot save Item ID:' . $oSibling->itemId . ' with attributes:' . serialize($oSibling->attributes), CLogger::LEVEL_ERROR);
					return FALSE;
				}
			}
		}

		$oItem->parentId = $nParentId;
		$oItem->position = $nPosition;

		if(!$oItem->save())
		{
			Yii::log('Cannot save Item ID:' . $nItemId . ' attributes:' . serialize($oItem->attributes), CLogger::LEVEL_ERROR);
			return FALSE;
		}

		return TRUE;
	}

	public static function treeRemove($nItemId)
	{
		$oItem = Item::model()->findByPk($nItemId);

		if (!$oItem)
		{
			Yii::log('Cannot find Item ID:' . $nItemId, CLogger::LEVEL_ERROR);
			return FALSE;
		}

		if (count(Item::getChildren($nItemId)))
		{
			Yii::log('Cannot delete Item having children ID:' . $nItemId, CLogger::LEVEL_WARNING);
			return FALSE;
		}

		$aSiblings = Item::getChildren($oItem->parentId);

		if ($oItem->position < count($aSiblings) - 1)
		{
			foreach ($aSiblings as $oSibling)
			{
				if ($oSibling->position > $oItem->position)
				{
					$oSibling->position -= 1;
					$oSibling->save();
				}
			}
		}

		if (!$oItem->delete())
		{
			Yii::log('Cannot delete Item ID:' . $nItemId, CLogger::LEVEL_ERROR);
			return FALSE;
		}

		return TRUE;
	}
}
