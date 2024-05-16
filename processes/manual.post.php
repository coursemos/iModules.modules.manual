<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼을 저장한다.
 *
 * @file /modules/manual/processes/manual.post.php
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
if ($me->getAdmin()->checkPermission('manuals', ['manuals']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$manual = null;
$manual_id = Request::get('manual_id');
if ($manual_id !== null) {
    $manual = $me
        ->db()
        ->select()
        ->from($me->table('manuals'))
        ->where('manual_id', $manual_id)
        ->getOne();
    if ($manual === null) {
        $results->success = false;
        $results->message = $me->getErrorText('NOT_FOUND_DATA');
        return;
    }
}

$errors = [];
$insert = [];
$insert['manual_id'] = Input::get('manual_id', $errors);
$insert['title'] = Input::get('title', $errors);
$insert['template'] = Format::toJson(Input::get('template', $errors));
$insert['permission'] = Input::get('permission', $errors);

if (count($errors) == 0) {
    if ($manual == null) {
        $me->db()
            ->insert($me->table('manuals'), $insert)
            ->execute();
    } else {
        $me->db()
            ->update($me->table('manuals'), $insert)
            ->where('manual_id', $manual_id)
            ->execute();
    }

    $results->success = true;
    $results->manual_id = $manual_id;
} else {
    $results->success = false;
    $results->errors = $errors;
}
