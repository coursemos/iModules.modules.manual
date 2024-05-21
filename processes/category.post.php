<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 분류를 저장한다.
 *
 * @file /modules/manual/processes/category.post.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 11.
 *
 * @var \modules\manual\Manual $me
 */
if (defined('__IM_PROCESS__') == false) {
    exit();
}

/**
 * 관리자권한이 존재하는지 확인한다.
 */
if ($me->getAdmin()->checkPermission('manuals', ['categories']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$category = null;
$manual_id = Request::get('manual_id');
$category_id = Request::get('category_id');
if ($category_id !== null) {
    $category = $me
        ->db()
        ->select()
        ->from($me->table('categories'))
        ->where('category_id', $category_id)
        ->where('manual_id', $manual_id)
        ->getOne();
    if ($category === null) {
        $results->success = false;
        $results->message = $me->getErrorText('NOT_FOUND_DATA');
        return;
    }
}

$errors = [];
$insert = [];
$insert['category_id'] = Input::get('category_id', $errors);
$insert['manual_id'] = $manual_id;
$insert['title'] = Input::get('title', $errors);
$insert['permission'] = Input::get('permission', $errors);
$insert['has_version'] = Input::get('has_version') ? 'TRUE' : 'FALSE';
if ($insert['has_version'] == 'TRUE') {
    $versions = array_values(array_filter(explode("\n", Input::get('versions') ?? '')));
    if (count($versions) == 0) {
        $errors['versions'] = $me->getErrorText('REQUIRED');
    } else {
        foreach ($versions as &$version) {
            $version = $me->getVersionToInt($version);
            if ($version == 0) {
                $errors['versions'] = $me->getErrorText('INVALID_VERSION');
                break;
            }
        }

        sort($versions);
        $versions = array_reverse($versions);

        $insert['versions'] = implode("\n", $versions);
    }
}

if (count($errors) == 0) {
    if ($category == null) {
        $insert['sort'] = $me
            ->db()
            ->select()
            ->from($me->table('manuals'))
            ->where('manual_id', $manual_id)
            ->count();

        $me->db()
            ->insert($me->table('categories'), $insert)
            ->execute();
    } else {
        $me->db()
            ->update($me->table('categories'), $insert)
            ->where('category_id', $category_id)
            ->where('manual_id', $manual_id)
            ->execute();
    }

    $results->success = true;
    $results->category_id = $category_id;
} else {
    $results->success = false;
    $results->errors = $errors;
}
