<?php

namespace Inventory\Api;

use Common\Api\Authenticator;

class LineItemCreativeAssociationService {
	private $customTargetingService;

	public function __construct() {
		$this->customTargetingService = new CustomTargetingService();
	}

	public function create( $creativeId, $lineItemId ) {

		if ( empty($creativeId) ) {
			return [
				'creativeSet' => false
			];
		}

		$response = [
			'success' => true,
			'creativeSet' => true,
			'creativeId' => $creativeId
		];

		try {
			$user = Authenticator::getUser();

			if ( empty($lineItemId) ) {
				return $this->getIncorrectLineItemResult();
			} else {
				$lineItemCreativeAssociationService = $user->GetService( 'LineItemCreativeAssociationService', 'v201608' );

				$lineItemCreativeAssociation = new \LineItemCreativeAssociation();
				$lineItemCreativeAssociation->creativeId = $creativeId;
				$lineItemCreativeAssociation->lineItemId = $lineItemId;
				$lica = $lineItemCreativeAssociationService->createLineItemCreativeAssociations( [ $lineItemCreativeAssociation ] );

				if ( !isset($lica) ) {
					$response['success'] = false;
					$response['message'] = 'line item - creative association not created';
				}
			}
		} catch ( \Exception $e ) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	public function getIncorrectLineItemResult() {
		return [
			'success' => false,
			'message' => 'Line item creation failed - unable to associate creative',
			'creativeSet' => false
		];
	}
}