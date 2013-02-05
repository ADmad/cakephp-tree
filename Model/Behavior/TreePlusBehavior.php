<?php
/**
 * Behavior to calculate and save depth of a node in MPTT
 *
 * PHP versions 5
 *
 * Copyright (c) 2008, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) 2008, Andy Dawson
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

App::uses('ModelBehavior', 'Model');

/**
 * TreePlusBehavior class
 *
 * @uses ModelBehavior
 */
class TreePlusBehavior extends ModelBehavior {

/**
 * Defaults
 *
 * @var array
 */
	public $defaults = array(
		'field' => '_depth'
	);

/**
 * Setup method
 *
 * @param Model $Model Model instance
 * @param array $settings Behavior settings
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = $settings + $this->defaults;
	}

/**
 * afterSave method
 *
 * @param Model $Model Model instance
 * @return void
 */
	public function afterSave(Model $Model, $created) {
		if ($Model->hasField($this->settings[$Model->alias]['field'])) {
			$this->resetDepths($Model);
		}
	}

/**
 * Set depth of nodes. Try to use single query if possibly
 * as its typically ~20 times faster than using a loop
 *
 * @param Model $Model Model instance
 * @param mixed $id Record's primary key value
 * @return boolean
 */
	public function resetDepths(Model $Model, $id = null) {
		if (!$Model->Behaviors->attached('Tree')) {
			return false;
		}

		if (!$id) {
			$db = $Model->getDataSource();
			$table = $db->config['prefix'] . $Model->table;
			$field = $this->settings[$Model->alias]['field'];
			$pK = $Model->primaryKey;
			$treeSettings = $Model->Behaviors->Tree->settings[$Model->alias];
			$left = $treeSettings['left'];
			$right = $treeSettings['right'];
			$scope = $db->conditions($treeSettings['scope'], true, false, $Model);
			// @todo Replace this custom query with cakey way
			try {
				$Model->query("
					UPDATE $table AS `{$Model->alias}` SET `$field` = (
						SELECT wrapper.parents FROM (
							SELECT
								this.$pK as row,
								COUNT(parent.$pK) as parents
							FROM
								$table AS this
							LEFT JOIN $table AS parent ON (
								parent.$left < this.$left AND
								parent.$right > this.$right AND
								" . str_replace('`' . $Model->alias . '`', 'parent', $scope) . ")
							WHERE " . str_replace('`' . $Model->alias . '`', 'this', $scope) . "
							GROUP BY
								this.$pK
						) AS wrapper WHERE wrapper.row = {$Model->alias}.$pK
					) WHERE $scope
				");
			} catch (PDOException $e) {
				$max = ini_get('max_execution_time');
				if ($max) {
					set_time_limit(max($Model->find('count') / 10, 30));
				}
				$Model->updateAll(array($field => 0));
				$Model->displayField = $pK;
				$nodes = $Model->find('list', array('conditions' => $treeSettings['scope']));
				foreach ($nodes as $id => $r) {
					$this->resetDepths($Model, $id);
				}
			}
			return true;
		}

		$Model->id = $id;
		$path = $Model->getPath($id, array($Model->primaryKey));
		$Model->saveField($this->settings[$Model->alias]['field'], count($path) - 1, array('callbacks' => 'before'));
		return true;
	}

}