<?php

/*************************************************************************************
 * Views Output Alter for Job Postings
 *************************************************************************************/
/**
 * Implements TEMPLATE_preprocess_views_view_field().
 */
function altarum_jobs_preprocess_views_view_field(&$vars)
{
    $view = $vars['view'];
    $field = $vars['field'];

    /* job postings */
    if (
        isset($view) && ($view->storage->id() == 'list_of_job_postings_search_api') && ($view->current_display == 'page_1') &&
        $field->field == 'nid'
    ) {
        $value = $field->getValue($vars['row']);
        $job_card = altarum_jobs_job_card($value);

        // likes form
        $like_form = altarum_custom_forms_get_like_form_instance($value);

        // favorites form
        $fav_form = altarum_custom_forms_get_favorite_form_instance($value);

        $output = [
            'job_card' => $job_card,
            'fav_form' => $fav_form,
            'like_form' => $like_form,
            '#cache' => [
                'max-age' => 0,
            ],
        ];

        $vars['output'] = $output;
    }
}

/*
 * Returns Job Card by replacing current value (nid)
 */
function altarum_jobs_job_card($nid)
{
    $job_card = [];

    if (!empty($nid)) {
        $node = \Drupal\node\Entity\Node::load($nid);

        $type = $node->getType();
        $authoring_info = altarum_jobs_author_info($nid);

        switch($type) {
            case 'job_post':
            case 'self_directed_job_post':
                // availability
                $availability = [];
                $availability_terms = !empty($node->get('field_availability')) ? $node->field_availability : false;
                if (!empty($availability_terms)) {
                    foreach ($availability_terms as $value) {
                        $availability[] = $value->value;
                    }
                }

                // number of positions (field_number_of_positions)
                $number_of_positions = !empty($node->get('field_number_of_positions')->value) ? $node->field_number_of_positions->value : '1';

                // benefits tags
                $benefits = [];
                $benefits_terms = !empty($node->get('field_benefits_ref')) ? $node->get('field_benefits_ref') : false;
                if (!empty($benefits_terms)) {
                    foreach ($benefits_terms as $value) {
                        $benefits[$value->target_id] = _set_term_name($value->target_id);
                    }
                }

                // field_provide_support_for tags
                $field_provide_support_for = [];
                if (!empty($node->get('field_provide_support_for'))) {
                    foreach ($node->get('field_provide_support_for') as $value) {
                        $field_provide_support_for[] = $value->value;
                    }
                }

                // field_provide_support_for_needs tags
                $field_provide_support_for_needs = [];
                if (!empty($node->get('field_provide_support_for_needs'))) {
                    foreach ($node->get('field_provide_support_for_needs') as $value) {
                        $field_provide_support_for_needs[$value->target_id] = _set_term_name($value->target_id);
                    }
                }

                // field_other_special_needs tags
                $field_other_special_needs = !empty($node->get('field_other_special_needs')) ? $node->field_other_special_needs->value : false;

                // field_candidates_willing_to_work in environment tags
                $field_candidates_willing_to_work = [];
                if (!empty($node->get('field_candidates_willing_to_work'))) {
                    foreach ($node->get('field_candidates_willing_to_work') as $value) {
                        $field_candidates_willing_to_work[] = $value->value;
                    }
                }

                // field_gender_preferences tags
                $field_gender_preferences = [];
                if (!empty($node->get('field_gender_preferences'))) {
                    foreach ($node->get('field_gender_preferences') as $value) {
                        $field_gender_preferences = $value->value;
                    }
                }

                // field_language_preference tags
                $field_language_preference = [];
                $field_language_preference_terms = !empty($node->get('field_language_preference')) ? $node->get('field_language_preference') : false;
                $field_language_preference = [];
                if (!empty($field_language_preference_terms)) {
                    foreach ($field_language_preference_terms as $value) {
                        $field_language_preference[$value->target_id] = _set_term_name($value->target_id);
                    }
                }

                // field_other_language_s_
                $field_other_language_s_ = !empty($node->get('field_other_language_s_')) ? $node->field_other_language_s_->value : false;

                // field_minimum_qualifications tags
                $field_minimum_qualifications = [];
                $field_minimum_qualifications_terms = !empty($node->get('field_minimum_qualifications')) ? $node->get('field_minimum_qualifications') : false;
                $field_minimum_qualifications = [];
                if (!empty($field_minimum_qualifications_terms)) {
                    foreach ($field_minimum_qualifications_terms as $value) {
                        $field_minimum_qualifications[$value->target_id] = _set_term_name($value->target_id);
                    }
                }

                // field_min_qualification_add
                $field_min_qualification_add = !empty($node->get('field_min_qualification_add')) ? $node->field_min_qualification_add->value : false;

                $credentials = '';
                if($type == 'job_post') {
                    // credentials tags
                    $credentials_terms = !empty($node->get('field_credentials')) ? $node->get('field_credentials') : false;
                    if (!empty($credentials_terms)) {
                        foreach ($credentials_terms as $value) {
                            $credentials = _set_term_name($value->target_id);
                        }
                    }
                }

                $position_role = '';
                if($type == 'job_post') {
                    // position_role tags
                    $position_role_terms = !empty($node->get('field_position_role')) ? $node->get('field_position_role') : false;
                    if (!empty($position_role_terms)) {
                        foreach ($position_role_terms as $value) {
                            $position_role = _set_term_name($value->target_id);
                        }
                    }
                }

                // Employer information
                $employer = [];
                if($type == 'job_post') {
                    if (!empty($node->field_job_post_provider)) {
                        $pid = $node->field_job_post_provider->target_id;
                        if($pid) {
                            $business_information = \Drupal\node\Entity\Node::load($pid);
                            if(!empty($business_information)) {
                                $employer = [
                                    'nid' => $business_information->id(),
                                    'pid' => $pid,
                                    'path' => '/node/' . $business_information->id(),
                                    'provider_name' => $business_information->getTitle(),
                                    'about_the_provider' => $business_information->get('body')->value,
                                    'field_provider_email' => $business_information->get('field_provider_email')->value,
                                    'field_phone_number' => $business_information->get('field_phone_number')->value,
                                    'field_city' => $business_information->get('field_city')->value,
                                    'field_zip_code' => $business_information->get('field_zip_code')->value,
                                ];
                            }
                        }
                    }
                }
                if($type == 'self_directed_job_post') {
                    if (!empty($node->getOwner())) {
                        $eid = $node->getOwner()->id();
                        if($eid) {
                            $user = _get_user_data($eid);
                            // Get profile storage to use ProfileStorage methods.
                            $profile_storage = \Drupal::entityTypeManager()->getStorage('profile');
                            // Load business information (self-directed employer)
                            $business_information = $profile_storage->loadByUser($user, 'business_information');
                            if(!empty($business_information)) {
                                if(!empty($business_information->get('field_cds_provider_s_first_name')->value)) {
                                    $provider_name = $business_information->get('field_cds_provider_s_first_name')->value;
                                }
                                $employer = [
                                    'nid' => $business_information->id(),
                                    'pid' => $eid,
                                    'path' => '/node/' . $business_information->id(),
                                    'provider_name' => $provider_name,
                                    'about_the_provider' => $business_information->get('field_about_me')->value,
                                    'field_provider_email' => $business_information->get('field_cds_provider_s_email_addre')->value,
                                    'field_phone_number' => $business_information->get('field_cds_provider_s_phone_numbe')->value,
                                    'field_city' => $business_information->get('field_city')->value,
                                    'field_zip_code' => $business_information->get('field_zip_code')->value,
                                    'field_other_id' => $business_information->get('field_other_id')->value,
                                ];
                            }
                        }
                    }
                }

                // service period
                $start_date = $end_date = '';
                if (!empty($node->field_service_period)) {
                    if ($node->field_service_period->referencedEntities() && !empty($node->field_service_period->referencedEntities())) {
                        foreach ($node->field_service_period->referencedEntities() as $key => $paragraph) {
                            $start_date = !empty($paragraph->field_start_date->value) ? date('m/d/Y', strtotime($paragraph->field_start_date->value)) : false;
                            $end_date = !empty($paragraph->field_end_date->value) ? ' - ' . date('m/d/Y', strtotime($paragraph->field_end_date->value)) : false;
                        }
                    }
                }

                // service times
                $service_times = [];
                if (!empty($node->field_service_time)) {
                    if ($node->field_service_time->referencedEntities() && !empty($node->field_service_time->referencedEntities())) {
                        foreach ($node->field_service_time->referencedEntities() as $key => $paragraph) {
                            $service_times[] = [
                            'start_time' => !empty($paragraph->field_start_time->value) ? $paragraph->field_start_time->value : false,
                            'end_time' => !empty($paragraph->field_end_time->value) ? $paragraph->field_end_time->value : false,
                            'duration' => !empty($paragraph->field_duration->value) ? $paragraph->field_duration->value : false,
                            ];
                        }
                    }
                }

                // field_service_frequency
                $field_service_frequency = !empty($node->get('field_service_frequency')) ? $node->field_service_frequency->value : false;

                // field_select_shifts_needed
                $field_select_shifts_needed = [];
                if (!empty($node->get('field_select_shifts_needed'))) {
                    foreach ($node->get('field_select_shifts_needed') as $value) {
                        $field_select_shifts_needed[] = $value->value;
                    }
                }

                // field_service_weekdays tags
                $field_service_weekdays = [];
                if (!empty($node->get('field_service_weekdays'))) {
                    foreach ($node->get('field_service_weekdays') as $value) {
                        $field_service_weekdays[] = $value->value;
                    }
                }

                // field_service_month tags
                $field_service_month = [];
                if (!empty($node->get('field_service_month'))) {
                    foreach ($node->get('field_service_month') as $value) {
                        $field_service_month[] = $value->value;
                    }
                }

                // field_service_month_day tags
                $field_service_month_day = !empty($node->get('field_service_month_day')) ? $node->field_service_month_day->value : false;

                // field_type_of_care_needed tags
                $field_type_of_care_needed_terms = !empty($node->get('field_type_of_care_needed')) ? $node->get('field_type_of_care_needed') : false;
                $field_type_of_care_needed = [];
                if (!empty($field_type_of_care_needed_terms)) {
                    foreach ($field_type_of_care_needed_terms as $value) {
                        $field_type_of_care_needed[$value->target_id] = _set_term_name($value->target_id);
                    }
                }

                // field_job_requirements_add
                $field_job_requirements_add = !empty($node->get('field_job_requirements_add')) ? $node->field_job_requirements_add->value : false;

                // field_work_location_city
                $work_location = [];
                if(!empty($node->field_work_location_city->value)) {
                    $work_location['city'] = $node->field_work_location_city->value;
                }
                // field_work_location_zip_code
                if(!empty($node->field_work_location_zip_code->value)) {
                    $work_location['zip_code'] = $node->field_work_location_zip_code->value;
                }

                $params = '';
                $args = _get_args();
                if ($args[1] && ($args[1] == 'jobs' or $args[1] == 'node')) {
                    $url = \Drupal::request()->getRequestUri();
                    if(!empty($url)) {
                        $urlQuery = parse_url($url);
                        if(isset($urlQuery['query'])) {
                            $params = '?' . $urlQuery['query'];
                        }
                    }
                }

                $approved_services = !empty($node->get('field_approved_services')) ? $node->get('field_approved_services') : false;
                $approved_services_for_job = [];
                if(!empty($approved_services)) {
                    foreach($approved_services as $value) {
                        $approved_services_for_job[$value->target_id] = _set_term_name($value->target_id);
                    }
                }

                $required_endorsements = !empty($node->get('field_endorsements')) ? $node->get('field_endorsements') : false;
                $endorsements = [];
                if(!empty($required_endorsements)) {
                    foreach($required_endorsements as $value) {
                        $endorsements[$value->target_id] = _set_term_name($value->target_id);
                    }
                }


                $preferred_counties = !empty($node->get('field_preferred_counties')) ? $node->get('field_preferred_counties') : false;
                $counties = [];
                if(!empty($preferred_counties)) {
                    foreach($preferred_counties as $value) {
                        $counties[$value->target_id] = _set_term_name($value->target_id);
                    }
                }

                $job_card = [
                    'nid' => $nid,
                    'job_id' => $nid,
                    'type' => $type,
                    'title' => $node->getTitle(),
                    'status' => $node->status->value,
                    'path' => '/node/' . $nid . $params,
                    'params' => $params,
                    'field_availability' => $availability,
                    'field_number_of_positions' => $number_of_positions,
                    'benefits' => $benefits,
                    'field_provide_support_for' => $field_provide_support_for,
                    'field_provide_support_for_needs' => $field_provide_support_for_needs,
                    'field_other_special_needs' => $field_other_special_needs,
                    'field_candidates_willing_to_work' => $field_candidates_willing_to_work,
                    'field_gender_preferences' => $field_gender_preferences,
                    'field_language_preference' => $field_language_preference,
                    'field_other_language_s_' => $field_other_language_s_,
                    'pay_scale_from' => (!empty($node->field_pay_scale_from)) ? '$' . $node->field_pay_scale_from->value . ' per hour' : false,
                    'pay_scale_to' => (!empty($node->field_pay_scale_to)) ? '$' . $node->field_pay_scale_to->value . ' per hour' : false,
                    'employer' => $employer,
                    'credentials' => $credentials,
                    'field_minimum_qualifications' => $field_minimum_qualifications,
                    'field_min_qualification_add' => $field_min_qualification_add,
                    'field_type_of_care_needed' => $field_type_of_care_needed,
                    'field_job_requirements_add' => $field_job_requirements_add,
                    'position_role' => $position_role,
                    'field_service_period' => $start_date . $end_date,
                    'field_service_time' => $service_times,
                    'field_service_frequency' => $field_service_frequency,
                    'field_select_shifts_needed' => $field_select_shifts_needed,
                    'field_service_weekdays'  => $field_service_weekdays,
                    'field_service_month'     => $field_service_month,
                    'field_service_month_day' => $field_service_month_day,
                    'authoring_info'          => $authoring_info,
                    'work_location'           => $work_location,
                    'field_approved_services' => $approved_services_for_job,
                    'field_endorsements'      => $endorsements,
                    'field_counties'          => $counties,
                ];
                break;
        }
    }

    return $job_card;
}


function altarum_jobs_author_info($nid)
{
    $authoring_info = [];
    $authored_by = $authored_on = $updated_by = $updated_on = '';
    if(!empty($nid)) {
        $node = \Drupal\node\Entity\Node::load($nid);

        $authored_by = _set_user_fname_lname($node->uid->target_id);
        $authored_on = date('m-d-Y g:i a', $node->created->value);
        $updated_by = $updated_on = '';

        if(!empty($node->field_job_update_information)) {
            foreach($node->field_job_update_information as $author_info) {
                $paragraph = Paragraph::load($author_info->target_id);
                if(!empty($paragraph->field_job_change_uid)) {
                    $updated_by = _set_user_fname_lname($paragraph->field_job_change_uid->target_id);
                    $updated_on = date('m-d-Y g:i a', strtotime('-4 hours', strtotime($paragraph->field_job_change_date->value)));
                }
            }
        }

        $cuid = \Drupal::currentUser()->id();
        $role = _get_role($cuid);
        $admins = ['Tier5','Tier6','Tier7','Tier8'];
        if(in_array($role, $admins) or ($role == 'Tier2' or $role == 'Tier21' or $role == 'Tier3')) {
            $authoring_info = [
                'authored_by' => $authored_by,
                'authored_on' => $authored_on,
                'changed_by'  => $updated_by,
                'changed_on'  => $updated_on
            ];

        }
    }

    return $authoring_info;

}

/*************************************************************************************
 * Views Output Alter for Resources and Colaborate Posts
 *************************************************************************************/
/**
 * Implements hook_preprocess_views_view().
 */
function altarum_custom_forms_preprocess_views_view_unformatted(&$variables)
{
    $view = $variables['view'];
    if ($view->storage->id() == 'list_of_resources_search_api') {
        $cuid = \Drupal::currentUser()->id();
        if ($cuid > 0) {
            $my_state = _get_user_state();
            $variables['my_state'] = $my_state;
        }
    }
}

/**
 * Implements TEMPLATE_preprocess_views_view_field().
 */
function altarum_custom_forms_preprocess_views_view_field(&$vars)
{
    $view = $vars['view'];
    $field = $vars['field'];
    /* resources */
    if (
        isset($view) && ($view->storage->id() == 'list_of_resources_search_api') && ($view->current_display == 'page_1') &&
        $field->field == 'nid'
    ) {
        $value = $field->getValue($vars['row']);
        $output = altarum_custom_forms_resource_card($value);
        $vars['output'] = $output;
    }
}

/*
 * Returns Resource Card by replacing current value (nid)
 */
function altarum_custom_forms_resource_card($nid)
{
    $resource_card = $output = [];

    if (!empty($nid)) {
        $node = \Drupal\node\Entity\Node::load($nid);
        $author = $node->getOwnerId();

        // resources attachments
        $attachment_files = !empty($node->get('field_attachments')->getValue()) ? $node->get('field_attachments')->getValue() : false;
        $attachments = [];
        if (!empty($attachment_files)) {
            foreach ($attachment_files as $attachment) {
                $file = \Drupal\file\Entity\File::load($attachment['target_id']);
                if ($file) {
                    $path = $file->getFileUri();
                    $attachments[] = [
                        'fid' => $attachment['target_id'],
                        'path' => file_url_transform_relative(
                            file_create_url($path)
                        ),
                        'file_name' => $file->getFilename(),
                    ];
                }
            }
        }

        // resource type tags
        $type_term = !empty($node->get('field_resource_type')) ? $node->get('field_resource_type')->target_id : false;
        $type = _set_term_name($type_term);

        // topics tags
        $topic_terms = !empty($node->get('field_topics')) ? $node->get('field_topics') : false;
        $topics = [];
        if (!empty($topic_terms)) {
            foreach ($topic_terms as $topic) {
                $topics[] = _set_term_name($topic->target_id);
            }
        }

        // Website Link
        $website_link = !empty($node->get('field_website_link')) ? $node->get('field_website_link')->uri : false;
        $website_title = !empty($node->get('field_website_link')) ? $node->get('field_website_link')->title : false;

        $resource_card = [
            'nid' => $nid,
            'title' => $node->getTitle(),
            'uid' => $author,
            'body' => $node->body->value,
            'resource_type' => $type,
            'resource_attachments' => $attachments,
            'resource_topics' => $topics,
            'website_link' => $website_link,
            'website_title' => $website_title,
        ];

        // likes form
        $like_form = altarum_custom_forms_get_like_form_instance($nid);

        // favorites form
        $fav_form = altarum_custom_forms_get_favorite_form_instance($nid);

        $output = [
            'resource_card' => $resource_card,
            'fav_form' => $fav_form,
            'like_form' => $like_form,
            '#cache' => [
                'max-age' => 0,
            ],
        ];
    }

    return $output;
}