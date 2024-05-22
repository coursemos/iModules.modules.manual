<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 목차 정렬을 저장한다.
 *
 * @file /modules/manual/processes/contents.patch.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 5. 11.
 *
 * @var \modules\admin\Admin $me
 */
if (defined('__IM_PROCESS__') == false) {
    exit();
}

/**
 * 관리자권한이 존재하는지 확인한다.
 */
if ($me->getAdmin()->checkPermission('manuals', ['contents']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$records = Input::get('records') ?? [];
foreach ($records as $record) {
    $me->db()
        ->update($me->table('contents'), (array) $record->updated)
        ->where('content_id', $record->origin->content_id)
        ->execute();
}

$results->success = true;
