<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 문서를 삭제한다.
 *
 * @file /modules/manual/processes/documents.delete.php
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
if ($me->getAdmin()->checkPermission('manuals', ['document']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$content_id = Request::get('content_id', true);
$start_version = Request::get('start_version', true);

/**
 * @var \modules\manual\admin\Manual $mAdmin
 */
$mAdmin = $me->getAdmin();
$mAdmin->deleteDocument($content_id, $start_version);

$results->success = true;
