<?php

/**
 * Error outputter - posts check data to post table
 * @param type $funcNameErr
 */
function err_outputs($funcNameErr) {
    $dataPostTable = array(//change
        'post_title' => 'fail',
        'post_type' => 'testData',
        'post_content' => $funcNameErr . ' has returned empty'
    );
    wp_insert_post($dataPostTable);
}

/**
 * Pass outputter - posts check data to post table
 * @param type $funcNameErr
 */
function pass_outputs($funcNameErr) {
    $dataPostTable = array(//change
        'post_title' => 'pass',
        'post_type' => 'testData',
        'post_content' => $funcNameErr
    );
    wp_insert_post($dataPostTable);
}

/**
 * Gets certificate object
 * @global type $wpdb
 * @param type $user_ID
 * @return array
 */
function test_get_certificate_data($user_ID) {
    global $wpdb;
    $watuStr = "SELECT max(wp_watupro_user_certificates.ID), wp_watupro_user_certificates.user_id, 
        wp_watupro_user_certificates.certificate_id, wp_watupro_user_certificates.exam_id,
        wp_watupro_certificates.title 
        FROM wp_watupro_user_certificates JOIN wp_watupro_certificates 
        ON wp_watupro_user_certificates.certificate_id=wp_watupro_certificates.ID WHERE wp_watupro_user_certificates.user_id = $user_ID
        GROUP BY wp_watupro_user_certificates.certificate_id";

    $watuObjUnparsed = $wpdb->get_results($watuStr); //list of objects depending on amount of certificates
    if ((empty($watuObjUnparsed))) {
        err_outputs('test_get_certificate_data - empty watuObjUnparsed');
    } else {
        pass_outputs('test_get_certificate_data - empty watuObjUnparsed');
    }
    $watuObj = [];
    foreach ($watuObjUnparsed as $val) {
        array_push($watuObj, $val->title);
    }
    if ((empty($watuObj))) {
        err_outputs('test_get_certificate_data - empty watuObj');
    } else {
        pass_outputs('test_get_certificate_data - empty watuObj');
    }
    return $watuObj;
}

/**
 * Tests if certificate value has been added
 * @param type $postedID
 * @param type $watuObj
 * @return boolean
 */
function test_valExistence_check($postedID, $watuObj) {

    $errorVal = true;
    $valDoesExist = true;

    $valExistence = ['', '', '']; //might not work
    $valExistence[0] = get_post_meta($postedID, 'wpsl_certification_url1', true);
    $valExistence[1] = get_post_meta($postedID, 'wpsl_certification_url2', true);
    $valExistence[2] = get_post_meta($postedID, 'wpsl_certification_url3', true);

    if (!empty($valExistence[0]) || !empty($valExistence[1]) || !empty($valExistence[2])) {
        $valDoesExist = true;
        foreach ($watuObj as $val) {
            if (!in_array($val, $valExistence)) {
                if (empty($valExistence[0])) {
                    if (($valExistence[1] != $val) && ($valExistence[2] != $val)) {
                        $errorVal = update_post_meta($postedID, 'wpsl_certification_url1', $val);
                        if ($errorVal == false) {
                            err_outputs('test_valExistence_check');
                        } else {
                            pass_outputs('test_valExistence_check');
                        }
                    }
                } elseif (empty($valExistence[1])) {
                    if (($valExistence[0] != $val) && ($valExistence[2] != $val)) {
                        $errorVal = update_post_meta($postedID, 'wpsl_certification_url2', $val);
                        if ($errorVal == false) {
                            err_outputs('test_valExistence_check');
                        } else {
                            pass_outputs('test_valExistence_check');
                        }
                    }
                } elseif (empty($valExistence[2])) {
                    if (($valExistence[0] != $val) && ($valExistence[1] != $val)) {
                        $errorVal = update_post_meta($postedID, 'wpsl_certification_url3', $val);
                        if ($errorVal == false) {
                            err_outputs('test_valExistence_check');
                        } else {
                            pass_outputs('test_valExistence_check');
                        }
                    }
                }
            }
        }
    } else {
        $valDoesExist = false;
    }

    return $valDoesExist;
}

/**
 * inserts data to wo store locator tables
 * @global type $wpdb
 * @param type $idOfUser
 * @param type $examName
 * @param type $userEmail
 */
function test_update_all_data_to_wp_store_locator($idOfUser, $examName, $userEmail) {
    global $wpdb;
    $userFirstName = get_user_meta($idOfUser, 'billing_first_name', true);
    $userLastName = get_user_meta($idOfUser, 'billing_last_name', true);
    $userFullName = $userFirstName . ' ' . $userLastName;
    $userPhone = get_user_meta($idOfUser, 'billing_phone', true);
    $userAddress = get_user_meta($idOfUser, 'billing_address_1', true);
    $userCity = get_user_meta($idOfUser, 'billing_city', true);
    $userState = get_user_meta($idOfUser, 'billing_state', true);
    $userZip = get_user_meta($idOfUser, 'billing_postcode', true);
    $userCompany = get_user_meta($idOfUser, 'billing_company', true); //title

    $dataPostTable = array(//change
        'post_author' => $idOfUser,
        'post_title' => $userCompany,
        'post_type' => 'wpsl_stores'
    );

    $insert_checker = wp_insert_post($dataPostTable);
    if ($insert_checker == 0) {
        err_outputs('test_update_all_data_to_wp_store_locator');
    } else {
        pass_outputs('test_update_all_data_to_wp_store_locator');
    }

    $justCreatedPostId = $insert_checker;

    add_post_meta($justCreatedPostId, 'wpsl_address', $userAddress);
    add_post_meta($justCreatedPostId, 'wpsl_city', $userCity);
    add_post_meta($justCreatedPostId, 'wpsl_state', $userState);
    add_post_meta($justCreatedPostId, 'wpsl_zip', $userZip);
    add_post_meta($justCreatedPostId, 'wpsl_country', 'United States');
    add_post_meta($justCreatedPostId, 'wpsl_email', $userEmail);
    add_post_meta($justCreatedPostId, 'wpsl_name_url', $userFullName);
    add_post_meta($justCreatedPostId, 'wpsl_phone', $userPhone);
    add_post_meta($justCreatedPostId, 'wpsl_certification_url1', $examName); //default to cert1

    wp_publish_post($justCreatedPostId);

    $address = $userCity . " " . $userState . " " . $userZip;
    $latlng = wpsl_get_address_latlng($address);
    $coordinates = explode(',', $latlng);
    $lat = $coordinates[0];
    $lng = $coordinates [1];

    add_post_meta($justCreatedPostId, 'wpsl_lat', $lat);
    add_post_meta($justCreatedPostId, 'wpsl_lng', $lng);
    wp_publish_post($justCreatedPostId);
}

/**
 * Gets most recent exam name for input into initial table
 * @global type $wpdb
 * @param type $user_ID
 * @return type
 */
function test_get_recent_exam_name($user_ID) {
    global $wpdb;
    $watuStr1 = "SELECT max(wp_watupro_user_certificates.ID), wp_watupro_user_certificates.user_id, 
        wp_watupro_user_certificates.certificate_id, wp_watupro_user_certificates.exam_id,
        wp_watupro_certificates.title 
        FROM wp_watupro_user_certificates JOIN wp_watupro_certificates 
        ON wp_watupro_user_certificates.certificate_id=wp_watupro_certificates.ID WHERE wp_watupro_user_certificates.user_id = $user_ID
        GROUP BY wp_watupro_user_certificates.certificate_id LIMIT 1";

    $watuObjUnparsed1 = $wpdb->get_row($watuStr1); //list of objects depending on amount of certificates
    if ((empty($watuObjUnparsed1))) {
        err_outputs('test_get_recent_exam_name - empty watuObjUnparsed');
    } else {
        pass_outputs('test_get_recent_exam_name - empty watuObjUnparsed');
    }
    return $watuObjUnparsed1->title;
}

/**
 * Gets most recent exam ID for input into initial table
 * @global type $wpdb
 * @param type $taking_id
 * @return type
 */
function test_get_recent_exam_ID($taking_id) {
    global $wpdb;
    $watuStr0 = "SELECT * FROM wp_watupro_taken_exams WHERE ID=$taking_id";

    $watuStr2 = $wpdb->get_row($watuStr0); //list of objects depending on amount of certificates
    if ((empty($watuStr2))) {
        err_outputs('test_get_recent_exam_ID');
    } else {
        pass_outputs('test_get_recent_exam_ID');
    }
    return $watuStr2;
}

/**
 * Gets specific postmeta data
 * @global type $wpdb
 * @param type $postmetaDatabaseName
 * @param type $userEmail
 * @return type
 */
function test_get_specific_postmeta_data($postmetaDatabaseName, $userEmail) {
    global $wpdb;
    $postmetaDatabaseNameMetaVal = $postmetaDatabaseName . ".meta_value";
    $postIdSearch = "SELECT * FROM $postmetaDatabaseName where $postmetaDatabaseNameMetaVal = '$userEmail' LIMIT 1";
    $postIdSearchObj = $wpdb->get_row($postIdSearch, OBJECT);
    if ((empty($postIdSearchObj))) {
        err_outputs('get_specific_postmeta_data - postIdSearchObjEmpty');
    } else {
        pass_outputs('get_specific_postmeta_data - postIdSearchObjEmpty');
    }
    $postedID = $postIdSearchObj->post_id;
    if ((empty($postedID))) {
        err_outputs('get_specific_postmeta_data - postedIDEmpty');
    } else {
        pass_outputs('get_specific_postmeta_data - postedIDEmpty');
    }
    return $postedID;
}

/**
 * main hook function when quiz is passed
 * @param type $taking_id
 */
function test_check_quiz_pass($taking_id) {

    $dataPostTableCheck = array(//change
        'post_title' => 'check',
        'post_type' => 'testData',
        'post_content' => 'test_check_quiz_pass function has been entered'
    );
    wp_insert_post($dataPostTableCheck);

    $quizDataBaseName = 'wp_watupro_taken_exams';
    $postmetaDatabaseName = 'wp_postmeta';
    $exam = test_get_recent_exam_ID($taking_id);
    $examID = $exam->exam_id;
    $user_ID = $exam->user_id;
    if ((empty($examID))) {
        err_outputs('get_exam_name');
    }
    if ((empty($user_ID))) {
        err_outputs('get_userID_name');
    }

    $userEmail = get_user_meta($user_ID, 'billing_email', true);
    if ((empty($userEmail))) {
        err_outputs('userEmail');
    }

    $watuObj = test_get_certificate_data($user_ID);
    if (!empty($watuObj)) {
        $postedID = test_get_specific_postmeta_data($postmetaDatabaseName, $userEmail);
        if (test_valExistence_check($postedID, $watuObj) == false) {
            $examName = test_get_recent_exam_name($user_ID);
            test_update_all_data_to_wp_store_locator($user_ID, $examName, $userEmail);
        }
    }
}
