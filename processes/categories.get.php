<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼분류 목록을 가져온다.
 *
 * @file /modules/manual/processes/categories.get.php
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
$records = $me
    ->db()
    ->select(['category_id', 'manual_id', 'title', 'has_version', 'permission', 'sort'])
    ->from($me->table('categories'))
    ->where('manual_id', $manual_id)
    ->orderBy('sort', 'asc')
    ->get();
foreach ($records as $sort => &$record) {
    $record->has_version = $record->has_version == 'TRUE';
    if ($sort != $record->sort) {
        $me->db()
            ->update($me->table('categories'), ['sort' => $sort])
            ->where('category_id', $record->category_id)
            ->where('manual_id', $manual_id)
            ->execute();
        $record->sort = $sort;
    }
}

$results->success = true;
$results->records = $records;
