<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 분류를 삭제한다.
 *
 * @file /modules/manual/processes/categories.delete.php
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
if ($me->getAdmin()->checkPermission('manuals', ['categories']) == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$manual_id = Request::get('manual_id', true);
$category_id = Request::get('category_id', true);

/**
 * @var \modules\manual\admin\Manual $mAdmin
 */
$mAdmin = $me->getAdmin();
$mAdmin->deleteCategory($manual_id, $category_id);

$results->success = true;
