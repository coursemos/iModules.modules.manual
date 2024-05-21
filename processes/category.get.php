<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 분류 정보를 가져온다.
 *
 * @file /modules/manual/processes/category.get.php
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
if ($me->getAdmin()->checkPermission('manuals') == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$manual_id = Request::get('manual_id', true);
$category_id = Request::get('category_id', true);
$data = $me
    ->db()
    ->select()
    ->from($me->table('categories'))
    ->where('manual_id', $manual_id)
    ->where('category_id', $category_id)
    ->getOne();
if ($data === null) {
    $results->success = false;
    $results->message = $me->getErrorText('NOT_FOUND_DATA');
    return;
}

$data->has_version = $data->has_version == 'TRUE';
if ($data->has_version == true) {
    $versions = array_values(array_filter(explode("\n", $data->versions ?? '')));
    foreach ($versions as &$version) {
        $version = $me->getIntToVersion($version);
    }

    $data->versions = implode("\n", $versions);
}

$results->success = true;
$results->data = $data;
