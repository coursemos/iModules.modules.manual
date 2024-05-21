<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 목차를 저장한다.
 *
 * @file /modules/manual/processes/content.post.php
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
if ($me->getAdmin()->checkPermission('manuals', ['contents']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$content = null;
$manual_id = Request::get('manual_id');
$category_id = Request::get('category_id');
$content_id = Request::get('content_id');
if ($content_id !== null) {
    $content = $me
        ->db()
        ->select()
        ->from($me->table('contents'))
        ->where('content_id', $content_id)
        ->where('category_id', $category_id)
        ->where('manual_id', $manual_id)
        ->getOne();
    if ($content === null) {
        $results->success = false;
        $results->message = $me->getErrorText('NOT_FOUND_DATA');
        return;
    }
}

$parent_id = Input::get('parent_id') == '@' ? null : Input::get('parent_id');

$errors = [];
$insert = [];
$insert['manual_id'] = $manual_id;
$insert['category_id'] = $category_id;
$insert['parent_id'] = $parent_id;
$insert['title'] = Input::get('title', $errors);
$insert['permission'] = Input::get('permission', $errors);

if (count($errors) == 0) {
    if ($content == null) {
        $insert['content_id'] = UUID::v4();
        $insert['sort'] = $me
            ->db()
            ->select()
            ->from($me->table('contents'))
            ->where('manual_id', $manual_id)
            ->where('category_id', $category_id)
            ->where('parent_id', $parent_id)
            ->count();

        $me->db()
            ->insert($me->table('contents'), $insert)
            ->execute();
    } else {
        if ($content->parent_id != $parent_id) {
            $insert['sort'] = $me
                ->db()
                ->select()
                ->from($me->table('contents'))
                ->where('manual_id', $manual_id)
                ->where('category_id', $category_id)
                ->where('parent_id', $parent_id)
                ->count();
        }

        $me->db()
            ->update($me->table('contents'), $insert)
            ->where('content_id', $content_id)
            ->execute();
    }

    $results->success = true;
    $results->category_id = $category_id;
} else {
    $results->success = false;
    $results->errors = $errors;
}
