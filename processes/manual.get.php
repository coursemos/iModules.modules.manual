<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼 정보를 가져온다.
 *
 * @file /modules/manual/processes/manual.get.php
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
$data = $me
    ->db()
    ->select()
    ->from($me->table('manuals'))
    ->where('manual_id', $manual_id)
    ->getOne();
if ($data === null) {
    $results->success = false;
    $results->message = $me->getErrorText('NOT_FOUND_DATA');
    return;
}

$data->template = json_decode($data->template);

$results->success = true;
$results->data = $data;
