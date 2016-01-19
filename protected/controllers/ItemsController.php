<?php
class ItemsController extends CController
{
	public function actionIndex()
	{
		$this->render('index', []);
	}

	public function actionRoot()
	{
		$this->jsonOutput([['id' => '0', 'text' => 'Items', 'children' => (bool) count(Item::model()->findAllByAttributes(['parentId' => 0]))]]);
	}

	public function actionChildren()
	{
		$nItemId = Yii::app()->request->getParam('id', 0);
		$this->jsonOutput(Item::treeChildren($nItemId));
	}

	public function actionCreate()
	{
		$nParentId   = Yii::app()->request->getParam('parent', 0);
		$nPositionId = Yii::app()->request->getParam('position', NULL);
		$sName       = Yii::app()->request->getParam('name', 'New node');

		$nItemId = Item::treeCreate($nParentId, $nPositionId, $sName);

		if ($nItemId)
		{
			$this->jsonOutput(['id' => $nItemId]);
		}

		$this->jsonOutput(['message' => 'Cannot add item.'], 'error');
	}

	public function actionRename()
	{
		$nId   = Yii::app()->request->getParam('id', NULL);
		$sName = Yii::app()->request->getParam('name', NULL);

		if (Item::treeRename($nId, $sName))
		{
			$this->jsonOutput(['status' => 'ok']);
		}

		$this->jsonOutput(['message' => 'Cannot rename item.'], 'error');
	}

	public function actionMove()
	{
		$nId          = Yii::app()->request->getParam('id', NULL);
		$nParentId    = Yii::app()->request->getParam('parent', NULL);
		$nOldParentId = Yii::app()->request->getParam('oldParent', NULL);
		$nPosition    = Yii::app()->request->getParam('position', NULL);
		$nOldPosition = Yii::app()->request->getParam('oldPosition', NULL);

		if (Item::treeMove($nId, $nParentId, $nOldParentId, $nPosition, $nOldPosition))
		{
			$this->jsonOutput(['status' => 'ok']);
		}

		$this->jsonOutput(['message' => 'Cannot move item.'], 'error');
	}

	public function actionRemove()
	{
		$nId = Yii::app()->request->getParam('id', NULL);

		if (Item::treeRemove($nId))
		{
			$this->jsonOutput(['status' => 'ok']);
		}

		$this->jsonOutput(['message' => 'Cannot remove item.'], 'error');
	}
	
	protected function jsonOutput($aData, $sStatus = 'ok')
	{
		if ($sStatus == 'error')
		{
			header('HTTP/1.1 400 Bad Request');
		}
		else
		{
			header('Content-type: application/json');
		}

		echo CJSON::encode($aData);

		Yii::app()->end();
	}
}
