<?php

function getEducationUpdateData($postData)
{
    return [
        'education_highschool' => isset($postData['education_highschool']) ? $postData['education_highschool'] : null,
        'education_highschool_year' => isset($postData['education_highschool_year']) ? $postData['education_highschool_year'] : null,
        'education_college' => isset($postData['education_college']) ? $postData['education_college'] : null,
        'education_college_year' => isset($postData['education_college_year']) ? $postData['education_college_year'] : null,
        'education_college_skills' => isset($postData['education_college_skills']) ? $postData['education_college_skills'] : null,
        'education_mba' => isset($postData['education_mba']) ? $postData['education_mba'] : null,
        'education_mba_year' => isset($postData['education_mba_year']) ? $postData['education_mba_year'] : null,
        'education_mba_course' => isset($postData['education_mba_course']) ? $postData['education_mba_course'] : null,
        'education_others' => isset($postData['education_others']) ? $postData['education_others'] : null,
        'education_others_year' => isset($postData['education_others_year']) ? $postData['education_others_year'] : null,
    ];
}
