<?php
/**
 * 이 파일은 아이모듈 매뉴얼모듈의 일부입니다. (https://www.imodules.io)
 *
 * 매뉴얼 권한목록을 가져온다.
 *
 * @file /modules/manual/processes/permissions.get.php
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
 * @var \modules\admin\Admin\Admin $mAdmin
 */
$mAdmin = $me
    ->getAdmin()
    ->getAdminModule()
    ->getAdmin();

$records = [];
$records[] = $mAdmin->getPermissionExpression($me->getText('permissions.true'), 'true', 0);
$records[] = $mAdmin->getPermissionExpression($me->getText('permissions.false'), 'false', 1);

$results->success = true;
$results->records = $records;
