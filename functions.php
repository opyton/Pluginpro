<?php

include 'functions_test.php';

/**
 * Gets certificate object
 * @global type $wpdb
 * @param type $user_ID
 * @return array
 */
function get_certificate_data($user_ID) {
    global $wpdb;
    $watuStr = "SELECT max(wp_watupro_user_certificates.ID), wp_watupro_user_certificates.user_id, 
        wp_watupro_user_certificates.certificate_id, wp_watupro_user_certificates.exam_id,
        wp_watupro_certificates.title 
        FROM wp_watupro_user_certificates JOIN wp_watupro_certificates 
        ON wp_watupro_user_certificates.certificate_id=wp_watupro_certificates.ID WHERE wp_watupro_user_certificates.user_id = $user_ID
        GROUP BY wp_watupro_user_certificates.certificate_id";

    $watuObjUnparsed = $wpdb->get_results($watuStr); //list of objects depending on amount of certificates
    $watuObj = [];
    foreach ($watuObjUnparsed as $val) {
        array_push($watuObj, $val->title);
    }
    return $watuObj;
}

/**
 * Tests if certificate value has been added
 * @param type $postedID
 * @param type $watuObj
 * @return boolean
 */
function valExistence_check($postedID, $watuObj) {

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
                    }
                } elseif (empty($valExistence[1])) {
                    if (($valExistence[0] != $val) && ($valExistence[2] != $val)) {
                        $errorVal = update_post_meta($postedID, 'wpsl_certification_url2', $val);
                    }
                } elseif (empty($valExistence[2])) {
                    if (($valExistence[0] != $val) && ($valExistence[1] != $val)) {
                        $errorVal = update_post_meta($postedID, 'wpsl_certification_url3', $val);
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
function update_all_data_to_wp_store_locator($idOfUser, $examName, $userEmail) {
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
function get_recent_exam_name($user_ID) {
    global $wpdb;
    $watuStr1 = "SELECT max(wp_watupro_user_certificates.ID), wp_watupro_user_certificates.user_id, 
        wp_watupro_user_certificates.certificate_id, wp_watupro_user_certificates.exam_id,
        wp_watupro_certificates.title 
        FROM wp_watupro_user_certificates JOIN wp_watupro_certificates 
        ON wp_watupro_user_certificates.certificate_id=wp_watupro_certificates.ID WHERE wp_watupro_user_certificates.user_id = $user_ID
        GROUP BY wp_watupro_user_certificates.certificate_id LIMIT 1";

    $watuObjUnparsed1 = $wpdb->get_row($watuStr1); //list of objects depending on amount of certificates
    return $watuObjUnparsed1->title;
}

/**
 * Gets most recent exam ID for input into initial table
 * @global type $wpdb
 * @param type $taking_id
 * @return type
 */
function get_recent_exam_ID($taking_id) {
    global $wpdb;
    $watuStr0 = "SELECT * FROM wp_watupro_taken_exams WHERE ID=$taking_id";

    $watuStr2 = $wpdb->get_row($watuStr0); //list of objects depending on amount of certificates
    return $watuStr2;
}

/**
 * Gets specific postmeta data
 * @global type $wpdb
 * @param type $postmetaDatabaseName
 * @param type $userEmail
 * @return type
 */
function get_specific_postmeta_data($postmetaDatabaseName, $userEmail) {
    global $wpdb;
    $postmetaDatabaseNameMetaVal = $postmetaDatabaseName . ".meta_value";
    $postIdSearch = "SELECT * FROM $postmetaDatabaseName where $postmetaDatabaseNameMetaVal = '$userEmail' LIMIT 1";
    $postIdSearchObj = $wpdb->get_row($postIdSearch, OBJECT);
    $postedID = $postIdSearchObj->post_id;
    return $postedID;
}

/**
 * main hook function when quiz is passed
 * @param type $taking_id
 */
function check_quiz_pass($taking_id) {

    $quizDataBaseName = 'wp_watupro_taken_exams';
    $postmetaDatabaseName = 'wp_postmeta';
    $exam = get_recent_exam_ID($taking_id);
    $examID = $exam->exam_id;
    $user_ID = $exam->user_id;
    $userEmail = get_user_meta($user_ID, 'billing_email', true);
    $watuObj = get_certificate_data($user_ID);
    if (!empty($watuObj)) {
        $postedID = get_specific_postmeta_data($postmetaDatabaseName, $userEmail);
        if (valExistence_check($postedID, $watuObj) == false) {
            $examName = get_recent_exam_name($user_ID);
            update_all_data_to_wp_store_locator($user_ID, $examName, $userEmail);
        }
    }
}

//add_action('watupro_completed_exam', 'test_check_quiz_pass', 10, 1);
add_action('watupro_completed_exam', 'check_quiz_pass', 10, 1);
