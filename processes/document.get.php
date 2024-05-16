<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 문서 정보를 가져온다.
 *
 * @file /modules/manual/processes/document.get.php
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
$content_id = Request::get('content_id', true);
$start_version = Request::get('start_version', true);
$data = $me
    ->db()
    ->select()
    ->from($me->table('documents'))
    ->where('content_id', $content_id)
    ->where('start_version', $start_version)
    ->getOne();
if ($data === null) {
    $results->success = false;
    $results->message = $me->getErrorText('NOT_FOUND_DATA');
    return;
}

/**
 * @var \modules\wysiwyg\Wysiwyg $mWysiwyg
 */
$mWysiwyg = Modules::get('wysiwyg');

if ($data->start_version == -1) {
    unset($data->start_version);
    unset($data->end_version);
    $data->all_version = true;
} else {
    $data->start_version = $me->getIntToVersion($data->start_version);
    $data->end_version = $me->getIntToVersion($data->end_version);
}

$data->content = $mWysiwyg->getViewerContent($data->content)->getJson();

$results->success = true;
$results->data = $data;
