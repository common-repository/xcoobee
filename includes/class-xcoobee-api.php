<?php
/**
 * The XcooBee_Api class.
 *
 * This file uses the same naming convention for variables and functions as the SDK.
 *
 * @package XcooBee
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use XcooBee\XcooBee as Sdk_XcooBee;
use XcooBee\Exception\XcooBeeException as Sdk_XcooBeeException;
use XcooBee\Http\Response as Sdk_Response;
use XcooBee\Core\Api\Api as Sdk_Api;
use XcooBee\Core\Validation as Sdk_Validation;

/**
 * Extends SDK API calls.
 *
 * @since 1.0.0
 * @see XcooBee\Core\Api\Api
 */
class XcooBee_API extends Sdk_Api {
	/**
	 * Returns the public_id of the current user.
	 *
	 * @since 1.4.0
	 *
	 * @param array $config Optional. Configuration array (default: []).
	 * @return string|null Public Id or null if not found.
	 */
	public function getUserPublicId( $config = [] ) {
		$query = 'query {
			user {
				public_id
			}
		}';

		$response = $this->_request( $query, [], $config );
		if ( $response->code !== 200 ) {
			return null;
		}

		return $response->result->user->public_id;
	}

	/**
	 * Extends XcooBee\Core\Api\Consents::getCampaignInfo().
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $campaignId Optional. Campaign Id (default: null).
	 * @param array       $config     Optional. Configuration array (default: []).
	 * @return XcooBee\Http\Response
	 */
	public function getCampaignInfo( $campaignId = null, $config = [] ) {
		$query = 'query getCampaignInfo($campaign_cursor: String) {
			campaign(campaign_cursor: $campaign_cursor) {
				campaign_cursor
				campaign_reference
				owner_cursor
				campaign_name
				date_c
				date_u
				date_e
				date_activated
				webhook
				webkey
				endpoint
				status
				campaign_title {
					locale
					text
				}
				campaign_description {
					locale
					text
				}
				campaign_type
				is_data_campaign
				allow_notes
				restrict_additional_users
				xcoobee_hosted_data
				is_inbound
				is_outbound
				campaign_params {
					privacy_policy_url
					terms_of_service_url
					custom_css
					display_position
					remove_after_n_sec
					do_not_show_outside_eu
					detect_country
					check_by_default_types
					send_data_requests_by_email
					campaign_email
					hide_on_complete
					response_wait_time
					theme
					hide_brand_tag
				}
				targets {
					recipient
					locale
					contract_ref
					name
				}
				xcoobee_targets {
					xcoobee_id
					contract_ref
				}
				email_targets {
					email
					locale
					contract_ref
				}
				countries
				requests {
					data {
						request_cursor
						request_description {
							locale
							text
						}
						request_name
						request_data_types
						required_data_types
						consent_types
						request_data_form
						date_offset
						offer_reference
						offer_points
						renewal_points
						date_e
						users {
							data {
								user_cursor
								date_c
							}
						}
					}
				}
			}
		}';

		return $this->_request( $query, [ 'campaign_cursor' => $campaignId ], $config );
	}

	/**
	 * Looks up for a campaign.
	 *
	 * @since 1.0.0
	 *
	 * @param string $campaignName Campaign name.
	 * @param array  $config       Optional. Configuration array (default: []).
	 * @return XcooBee\Http\Response
	 */
	public function findCampaign( $campaignName, $config = [] ) {
		$query = 'query findCampaign($userId: String!, $campaign_name: String) {
			campaigns(user_cursor: $userId, search: $campaign_name) {
				data {
					campaign_name
					campaign_cursor
					campaign_type
				}
			}
		}';

		return $this->_request( $query, [ 'userId' => $this->_getUserId( $config ), 'campaign_name' => $campaignName ], $config );
	}

	/**
	 * Retrieves company logo.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Optional. Configuration array (default: []).
	 * @return XcooBee\Http\Response
	 */
	public function getCompanyLogo( $config = [] ) {
		$query = 'query {
			user {
				settings {
					campaign {
						logo
					}
				}
			}
		}';

		return $this->_request( $query, [], $config );
	}

	/**
	 * Sets or updates campaign webhook.
	 *
	 * @param string $campaignId Campaign cursor.
	 * @param string $webhook    Camapign webhook.
	 * @param array $config Optional. Configuration array (default: []).
	 *
	 * @return XcooBee\Http\Response
	 * @throws XcooBee\Exception\XcooBeeException
	 */
	public function updateCampaignWebhook( $campaignId = null, $webhook, $config = [] )
	{
		$campaignId = $this->_getCampaignId( $campaignId, $config );

		$mutation = 'mutation modifyConsentCampaign($config: ConsentCampaignUpdateConfig) {
				modify_consent_campaign(config: $config) {
					campaign_title {
						text
					},
					webhook
				}
			}';

		return $this->_request( $mutation, [ 'config' => [ 'campaign_cursor' => $campaignId, 'webhook' => $webhook ] ], $config );
	}

	/**
	 * Retrieves campaign_cursor by campaign_reference.
	 *
	 * @param string $campaignReference Campaign reference.
	 * @param array  $config            Optional. Configuration array (default: []).
	 * @return string|null campaign_cursor or null if campaign not found.
	 */
	public function getCampaignCursor( $campaignRef = null, $config = [] ) {
		$query = 'query getCampaignCursor($campaignRef: String) {
			campaign(campaign_ref: $campaignRef) {
				campaign_cursor
			}
		}';

		$campaign = $this->_request( $query, ['campaignRef' => $campaignRef], $config );

		if ( 200 === $campaign->code ) {
			return $campaign->result->campaign->campaign_cursor;
		}

		return null;
	}

	/**
	 * Returns upload policies.
	 *
	 * @param [type] $files  Array of file paths.
	 * @param [type] $config Optional. Configuration array (default: []).
	 *
	 * @return array Array of policies.
	 */
	public function getUploadPolicy( $files, $config = [] ) {
		$endpoint = 'outbox';
		
		$response = new Sdk_Response();

		try {
			$user = $this->_xcoobee->users->getUser( $config );
			$endpointId = $this->_getOutboxEndpoint( $user->userId, $endpoint, $config );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		// Get upload policies.
		$query = 'query uploadPolicy {';
		foreach ( $files as $key => $file ) {
			$fileName = basename( $file );

			$query .= "policy$key: upload_policy(filePath: \"$fileName\",
				intent: $endpoint,
				identifier: \"$endpointId\") {
					signature
					policy
					date
					upload_url
					key
					credential
					identifier
				}";
		}
		$query .= '}';

		try {
			$policies = $this->_request( $query, [], $config );

		} catch (Exception $e ) {
			return $e->getMessage();
		}

		return $policies;

		if ( $policies->errors ) {
			$response->code = 400;
			$response->errors = $policies->errors;

			return $response;
		}

		// Return policies.
		$response->code = 200;
		$response->result->policies = [];

		foreach ( $files as $key => $file ) {
			$policy = 'policy' . $key;
			$policy = $policies->result->$policy;
			array_push( $response->result->policies, $policy );
		}

		return $response;
	}

	/**
	 * Retrieves bee_cursor by bee_name.
	 *
	 * @since 1.0.0
	 * @internal
	 *
	 * @param string $beeName bee_name.
	 * @param array  $config  Optional. Configuration array (default: []).
	 * @return string|null bee_cursor or null if bee not found.
	 */
	protected function _getBeeCursor( $beeName, $config = [] ) {
		$bees = $this->_xcoobee->bees->listBees( $beeName, $config );

		if ( 200 === $bees->code ) {
			return $bees->result->bees->data[0]->cursor;
		}

		return null;
	}

	/**
	 * Retrieves campaign settings.
	 *
	 * @since 1.5.0
	 *
	 * @param array  $config  Optional. Configuration array (default: []).
	 *
	 * @return XcooBee\Http\Response
	 * @throws XcooBee\Exception\XcooBeeException
	 */
	public function getCampaignSettings( $config = [] ) {
		$query = 'query {
			user {
				settings {
					campaign {
						accept_non_campaign_data
						default_callback_url
						default_wait_time
						logo
						company_email
						receive_consent_updates_via_email
						max_sar_answer_time
					}
				}
			}
		}';

		return $this->_request( $query, [], $config );
	}

	protected function _getOutboxEndpoint($userId, $intent, $config = []) {
		$query = 'query getEndpoint($userId: String!) {
			outbox_endpoints(user_cursor: $userId) {
				data {
					cursor
					name
					date_c
				}
			}
		}';
		
		$response = $this->_request( $query, ['userId' => (string)$userId], $config );

		$endpoint = array_filter($response->result->outbox_endpoints->data,
			function($value) use ($intent) {
				return (($value->name == $intent) || ($value->name == "flex"));
			});

		if($endpoint != null ){
			return $endpoint[0]->cursor;
		}

		return null;
	}
}