<?php
namespace Modular\Extensions\Controller;

use DataObject;

class GraphNode extends \Modular\ContentControllerExtension {

	/**
	 * Return all the actions performed on the extended model by the actor.
	 *
	 * @param string            $forAction           a relationship type/action Code e.g. 'CRT' or empty for all
	 * @param string|DataObject $actorModels         of the class related to the extended model,
	 *                                               e.g. 'Member' or 'SocialOrganisation'
	 * @return \ArrayList of all ManyManyRelationships which match passed criteria.
	 */
	public function Actions($forAction = '', $actorModels = 'Member') {
		$out = new \ArrayList();

		if ($actionModel = $this()->getModelInstance($forAction)) {
			if ($actionModel->ID) {
				$actionModelClass = $actionModel->ClassName;

				$actorModels = is_object($actorModels) ? get_class($actorModels) : $actorModels;

				if (!is_array($actorModels)) {
					$actorModels = [$actorModels];
				}

				/** @var string|SocialRelationship $relationshipClass */
				$relationshipClasses = SocialRelationship::relationship_implementors(
					$actorModels,
					$actionModelClass
				);

				foreach ($relationshipClasses as $relationshipClass) {
					/** @var DataList $actions */
					$actions = $relationshipClass::get()->filter([
						$relationshipClass::to_field_name() => $actionModel->ID,
					]);
					if ($forAction) {
						$actions = $actions->filter([
							'Action.Code' => $forAction,
						]);
					}
					$out->merge($actions);
				}
			}
		}

		return $out;
	}

	/**
	 * Return all the Actors who performed actions on the extended model, by default Members.
	 *
	 * @param string $forAction
	 * @param string $actorModel
	 * @return \ArrayList
	 */
	public function Actors($forAction = '', $actorModel = 'Member') {
		$actionModelClass = $this()->getModelClass();

		$actions = $this->Actions($forAction, $actorModel);

		/** @var string|SocialRelationship $relationshipClass */
		$relationshipClass = current(SocialRelationship::relationship_implementors(
			$actorModel,
			$actionModelClass
		));

		return $actions->filter([
			'ID' => $actions->column($relationshipClass::from_field_name()),
		]);
	}

	/**
	 * Return the historically first actor who did an action on the extended model. Can be used in templates
	 * e.g. FirstActor(CRT) will get you the creator of a model.
	 *
	 * @param string            $forAction  a relationship type/action Code e.g. 'CRT'
	 * @param string|DataObject $actorModel of the class related to the extended model, e.g. 'Member'
	 * @return \DataObject
	 */
	public function FirstActor($forAction, $actorModel = 'Member') {
		$actorModel = is_object($actorModel) ? $actorModel->ClassName : $actorModel;

		$action = $this->Actors($forAction, $actorModel)->sort('Created asc')->first();
		return ($action && $action->exists()) ? $action->getFrom() : null;
	}

	/**
	 * Return the historically last actor who did an action on the extended model.
	 *
	 * @param string            $forAction  a relationship type/action Code e.g. 'CRT'
	 * @param string|DataObject $actorModel of the class related to the extended model, e.g. 'Member'
	 * @return \DataObject
	 */
	public function LastActor($forAction, $actorModel = 'Member') {
		$actorModel = is_object($actorModel) ? $actorModel->ClassName : $actorModel;

		$action = $this->Actors($forAction, $actorModel)->sort('Created desc')->first();
		return ($action && $action->exists()) ? $action->getFrom() : null;
	}
}