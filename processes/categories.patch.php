<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼분류 정렬을 저장한다.
 *
 * @file /modules/manual/processes/categories.patch.php
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
if ($me->getAdmin()->checkPermission('manuals', ['categories']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$records = Input::get('records') ?? [];
foreach ($records as $record) {
    iModules::db()
        ->update($me->table('categories'), (array) $record->updated)
        ->where('manual_id', $record->origin->manual_id)
        ->where('category_id', $record->origin->category_id)
        ->execute();
}

$results->success = true;
