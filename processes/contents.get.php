<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 목차 목록을 가져온다.
 *
 * @file /modules/manual/processes/contexts.get.php
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
$version = Request::getInt('version') ?? -1;
$is_root = Request::get('is_root') == 'TRUE';

/**
 * @var \modules\manual\admin\Manual $mAdmin
 */
$mAdmin = $me->getAdmin();
$records = $mAdmin->getContents($manual_id, $category_id, $is_root, $version);
if ($is_root == true) {
    $root =
        $me->getManual($manual_id)->getTitle() . ' (' . $me->getCategory($manual_id, $category_id)->getTitle() . ')';
    $records = [
        [
            'content_id' => '@',
            'title' => $root,
            'children' => $records,
        ],
    ];
}

$results->success = true;
$results->records = $records;
