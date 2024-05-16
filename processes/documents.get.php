<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 문서 목록을 가져온다.
 *
 * @file /modules/manual/processes/documents.get.php
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

/**
 * @var \modules\member\Member $mMember
 */
$mMember = Modules::get('member');

$content_id = Request::get('content_id', true);
$records = $me
    ->db()
    ->select()
    ->from($me->table('documents'))
    ->where('content_id', $content_id)
    ->orderBy('start_version', 'desc')
    ->get();
foreach ($records as &$record) {
    $record->author = $mMember->getMember($record->member_id)->getJson();
}

$results->success = true;
$results->records = $records;
